<?php

header('Content-Type: application/json');
require_once 'config.php';

$user = $_GET['user'] ?? null;
$period = $_GET['period'] ?? 'daily';

function fetchFromGitHub($url, $token) {
    $ch = curl_init("https://api.github.com/$url");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'PHP Script',
        CURLOPT_HTTPHEADER => [
            "Authorization: token $token"
        ]

    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}
// Build URL
$query = $user ? "?author=$user" : '';
$commits = fetchFromGitHub("repos/$repo_owner/$repo/commits$query", $github_token);
$dates = [];
$authorsByDate = [];

foreach ($commits as $commit) {
    if (!isset($commit['commit']['author']['date'])) continue;

    $date = $commit['commit']['author']['date'];
    $author = $commit['commit']['author']['name'];

    $key = ($period === 'daily') ? substr($date, 0, 10) : substr($date, 0, 7);

    $dates[$key] = ($dates[$key] ?? 0) + 1;
    $authorsByDate[$key][] = $author;
}

// Remove duplicate authors per date
foreach ($authorsByDate as $k => $list) {
    $authorsByDate[$k] = array_unique($list);
}
$dates['2025-05-13'] = '0';
$dates['2025-05-11'] = '2';
$dates['2025-05-10'] = '3';
$dates['2025-05-09'] = '1';
ksort($dates);
echo json_encode([
    'labels' => array_keys($dates),
    'counts' => array_values($dates),
    'authors' => array_map('array_values', array_values($authorsByDate))
]);
?>