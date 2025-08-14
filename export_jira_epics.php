<?php
require 'vendor/autoload.php';
include 'config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

function safeField($value) {
    if (is_array($value)) {
        if (isset($value['value'])) return $value['value'];
        if (isset($value['name'])) return $value['name'];
        if (isset($value['displayName'])) return $value['displayName'];
        return implode(', ', array_map('safeField', $value));
    }
    return $value ?? '';
}

function makeRequest($url, $headers) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$auth = base64_encode("$email:$apiToken");
$headersReq = [
    "Authorization: Basic $auth",
    "Content-Type: application/json",
    "Accept: application/json"
];

$jql = "issuetype = Epic" . ($projectKey ? " AND project = $projectKey" : "") . " ORDER BY key ASC";
$epicUrl = "https://$jiraDomain/rest/api/3/search?jql=" . urlencode($jql) .
    "&maxResults=100&fields=summary,priority,project,customfield_10091,customfield_10092,customfield_10093,customfield_10096";
$epicResponse = makeRequest($epicUrl, $headersReq);
$epics = $epicResponse['issues'] ?? [];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Jira Epic Report");

$headers = [
    "Key", "Group", "EPIC Summary", "EPIC HiringStatus", "EPIC Priority", "EPIC ProjectName",
    "0. PROFILES (TO BE SHARED)",
    "1. Profile Feedback Pending",
    "2. C-INTERVIEW SCHEDULED",
    "3. C-INTERVIEW FBK PENDING",
    "4. SELECTED",
    "10. On-boarded"
];
$sheet->fromArray($headers, null, 'A1');

$highlightStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'FFE6E6']
    ]
];

$rowNum = 2;
foreach ($epics as $epic) {
    $epicKey = $epic['key'];
    $summary = safeField($epic['fields']['summary'] ?? '');
    $priority = safeField($epic['fields']['customfield_10093']['value'] ?? '');
    $project = safeField($epic['fields']['customfield_10092'] ?? '');
    $group = safeField($epic['fields']['customfield_10091']['value'] ?? '');
    $hiringStatus = safeField($epic['fields']['customfield_10096']['value'] ?? '');

    $childJql = "\"Epic Link\" = $epicKey";
    $childUrl = "https://$jiraDomain/rest/api/3/search?jql=" . urlencode($childJql) . "&maxResults=200&fields=status";
    $childResponse = makeRequest($childUrl, $headersReq);
    $children = $childResponse['issues'] ?? [];

    $statusCounts = [
        "0. PROFILES (TO BE SHARED)" => 0,
        "1. Profile Feedback Pending" => 0,
        "2. C-INTERVIEW SCHEDULED" => 0,
        "3. C-INTERVIEW FBK PENDING" => 0,
        "4. SELECTED" => 0,
        "10. On-boarded" => 0,
    ];

    foreach ($children as $child) {
        $statusName = strtoupper(trim($child['fields']['status']['name'] ?? ''));
        foreach ($statusCounts as $label => $count) {
            $statusKey = strtoupper(trim(preg_replace('/^\d+\.\s*/', '', $label)));
            if (strpos($statusName, $statusKey) !== false) {
                $statusCounts[$label]++;
                break;
            }
        }
    }

    // Write row values normally for text columns
    $sheet->setCellValueExplicit("A{$rowNum}", $epicKey, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue("B{$rowNum}", $group);
    $sheet->setCellValue("C{$rowNum}", $summary);
    $sheet->setCellValue("D{$rowNum}", $hiringStatus);
    $sheet->setCellValue("E{$rowNum}", $priority);
    $sheet->setCellValue("F{$rowNum}", $project);

    // Write counts as strings so "0" stays visible
    $col = 'G';
    foreach ($statusCounts as $count) {
        $sheet->setCellValueExplicit("{$col}{$rowNum}", (string)$count, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $col++;
    }

    // Highlight if all counts are zero
    if (array_sum($statusCounts) === 0) {
        $sheet->getStyle("A{$rowNum}:L{$rowNum}")->applyFromArray($highlightStyle);
    }

    $rowNum++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="jira_epic_report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
