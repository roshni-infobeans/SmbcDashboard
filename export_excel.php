<?php
// export_excel.php

$config = require 'config.php';

$jiraDomain = $config['jiraDomain'];
$email = $config['email'];
$apiToken = $config['apiToken'];

$auth = base64_encode("$email:$apiToken");
$headers = [
    "Authorization: Basic $auth",
    "Accept: application/json"
];

function curlRequest($url, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function getSprints($jiraDomain, $boardId, $headers) {
    $url = "$jiraDomain/rest/agile/1.0/board/$boardId/sprint?state=active,future,closed";
    $result = curlRequest($url, $headers);
    return $result['values'] ?? [];
}

function fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange, $sprintField) {
    $jql = "statusCategory = Done";

    if (!empty($sprintId)) {
        $jql .= " AND sprint = $sprintId";
    } else {
        $allSprints = getSprints($jiraDomain, $boardId, $headers);
        $sprintIds = array_map(fn($s) => $s['id'], $allSprints);
        if (!empty($sprintIds)) {
            $jql .= " AND sprint IN (" . implode(',', $sprintIds) . ")";
        }

        if ($timeRange === 'daily') {
            $today = date('Y-m-d');
            $jql .= " AND resolved >= \"$today\"";
        } elseif ($timeRange === 'weekly') {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $jql .= " AND resolved >= \"$sevenDaysAgo\"";
        }
    }

    if (!empty($developer)) {
        $jql .= " AND assignee = \"$developer\"";
    }

    $fields = "summary,status,assignee,$storyPointField,resolutiondate,$sprintField";
    $url = "$jiraDomain/rest/api/3/search?jql=" . urlencode($jql) . "&fields=$fields&maxResults=100";
    return curlRequest($url, $headers);
}

$boardId = $_GET['boardId'] ?? '';
$sprintId = $_GET['sprintId'] ?? '';
$developer = $_GET['developer'] ?? '';
$storyPointField = $_GET['storyPointField'] ?? 'customfield_10016'; // Story Points field
$sprintField = 'customfield_10020'; // Sprint field - adjust if needed
$timeRange = $_GET['timeRange'] ?? 'weekly';

$issues = fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange, $sprintField);

if (!$issues || empty($issues['issues'])) {
    die('No data found to export');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="story_points_export.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Sprint Name', 'Task Name', 'Assignee', 'Story Points', 'Resolution Date']);

foreach ($issues['issues'] as $issue) {
    $summary = $issue['fields']['summary'] ?? '';
    $assignee = $issue['fields']['assignee']['displayName'] ?? 'Unassigned';
    $points = $issue['fields'][$storyPointField] ?? 0;
    $resolvedRaw = $issue['fields']['resolutiondate'] ?? '';

    // Extract sprint name from sprint custom field
    $sprintName = 'Unknown Sprint';
    if (!empty($issue['fields'][$sprintField])) {
        if (is_array($issue['fields'][$sprintField])) {
            if (isset($issue['fields'][$sprintField][0]['name'])) {
                $sprintName = $issue['fields'][$sprintField][0]['name'];
            } else {
                $sprintName = (string) $issue['fields'][$sprintField];
            }
        } else {
            $sprintName = (string) $issue['fields'][$sprintField];
        }
    }

    // Remove "TP " prefix if present
    $sprintName = str_replace('TP ', '', $sprintName);

    if ($resolvedRaw) {
        try {
            $date = new DateTime($resolvedRaw);
            $resolved = $date->format('Y-m-d');
        } catch (Exception $e) {
            $resolved = $resolvedRaw;
        }
    } else {
        $resolved = '';
    }

    fputcsv($output, [$sprintName, $summary, $assignee, $points, $resolved]);
}

fclose($output);
exit;
