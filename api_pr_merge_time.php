<?php
ob_start();
require_once 'config.php';
require_once 'helopers.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

// === INPUT PARAMETERS ===
$repo_name = $_GET['repo'] ?? '';
$developer = $_GET['developer'] ?? '';
$team_slug = $_GET['team'] ?? '';
$tab = $_GET['tab'] ?? 'daily';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$export = $_GET['export'] ?? false;
$all_prs = [];
$page = 1;
$per_page = 100;

$teamDevelopers = get_developers_by_team($github_token,$github_organization,$team_slug);
do{
    $api_url = "https://api.github.com/repos/$repo_owner/$repo_name/pulls?state=closed&per_page=$per_page&page=$page";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $github_token",
        "Accept: application/vnd.github+json",
        "User-Agent: php-curl"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $prs = json_decode($response, true);
    if (is_array($prs)) {
        $all_prs = array_merge($all_prs, $prs);
    }

    $morePages = count($prs) === $per_page;
    $page++;
}while($morePages);

$filtered_data = [];

// === DATE RANGE ===
$now = new DateTime();
switch ($tab) {
    case 'daily':
        $start = (clone $now)->setTime(0, 0);
        $end = (clone $now)->setTime(23, 59);
        break;
    case 'weekly':
        $start = (clone $now)->modify('monday this week')->setTime(0, 0);
        $end = (clone $now)->modify('sunday this week')->setTime(23, 59);
        break;
    case 'range':
        if ($startDate && $endDate) {
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $end->setTime(23, 59);
        } else {
            $start = (new DateTime())->modify('-3 months');
            $end = new DateTime();
        }
        break;
    default:
        $start = (new DateTime())->modify('-3 months');
        $end = new DateTime();
}

// === FILTER PRs ===
foreach ($all_prs as $pr) {
    if (!$pr['merged_at']) continue;

    // Filter by developer if selected
    if (!empty($developer) && $pr['user']['login'] !== $developer) {
        continue;
    }

    // Filter by team if selected
    if (!empty($team_slug) && !in_array(strtolower($pr['user']['login']),$teamDevelopers)) {
        continue;
    }

    $created = new DateTime($pr['created_at']);
    $merged = new DateTime($pr['merged_at']);

    // Filter by date range
    if ($merged < $start || $merged > $end) {
        continue;
    }

    // Calculate time difference in minutes
    $diff_seconds = abs($merged->getTimestamp() - $created->getTimestamp());
    $value_in_minutes = round($diff_seconds / 60, 2);

    // Human-readable display
    if ($diff_seconds < 60) {
        $display = $diff_seconds . ' sec';
    } elseif ($diff_seconds < 3600) {
        $display = $value_in_minutes . ' min';
    } elseif ($diff_seconds < 86400) {
        $display = round($diff_seconds / 3600, 2) . ' hr';
    } else {
        $display = round($diff_seconds / 86400, 2) . ' day(s)';
    }
    $merge_at = date('Y-m-d',strtotime($pr['merged_at']));
    $created_at = date('Y-m-d',strtotime($pr['created_at']));
    $filtered_data[] = [
        // 'title' => $pr['title'],
        'title' => $merge_at,
        'value' => $value_in_minutes,
        'display' => $display.", Title:".$pr['title']
    ];
    $export_data[] = [
        $pr['id'],$pr['title'],$pr['user']['login'],$created_at,$merge_at,$display
    ];
}

if($export == 'true' || $export == true){
    if (empty($export_data)) {
        http_response_code(204); // No Content
        exit;
    }
    $titleArr = ['PR ID', 'Title', 'Merged By','Create Date', 'Merge Date','Time to Merge'];
    array_unshift($export_data,$titleArr);
    
    // Create spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add data to sheet starting from A1
    foreach ($export_data as $rowIndex => $row) {
        foreach ($row as $colIndex => $cellValue) {
            $cell = $sheet->getCellByColumnAndRow($colIndex + 1, $rowIndex + 1);
            $cell->setValue($cellValue);

            // Make header bold
            if ($rowIndex === 0) {
                $cell->getStyle()->getFont()->setBold(true);
            }
        }
    }

    // Unique filename with microseconds
    $filename = 'merged_pr_data_' . date('Ymd_His') . '_' . substr((string)microtime(true), -3) . '.xlsx';
    // ob_clean();
    // flush();
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    // Write file to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}else{
    echo json_encode([
        'labels' => array_column($filtered_data, 'title'),
        'values' => array_column($filtered_data, 'value'),
        'displays' => array_column($filtered_data, 'display')
    ]);
}
?>