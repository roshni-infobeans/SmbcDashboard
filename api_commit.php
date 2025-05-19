<?php

header('Content-Type: application/json');
require_once 'config.php';

$repo = $_GET['repo'] ?? $repo; // Use repo from query param or default from config
$user = $_GET['user'] ?? null;
$period = $_GET['period'] ?? 'daily';
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

// Validate dates and fallback if invalid
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

if (!validateDate($from)) {
    $from = (new DateTime())->modify('-30 days')->format('Y-m-d');
}
if (!validateDate($to)) {
    $to = (new DateTime())->format('Y-m-d');
}

$fromDate = new DateTime($from);
$toDate = new DateTime($to);
$toDate->setTime(23,59,59); // Include entire end date

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

$page = 1;
$allCommits = [];
// GitHub API pagination limit per page is 100
do {
    $query = "repos/$repo_owner/$repo/commits?per_page=100&page=$page";
    if ($user) {
        $query .= "&author=$user";
    }
    $commitsPage = fetchFromGitHub($query, $github_token);
    if (!is_array($commitsPage) || count($commitsPage) === 0) break;

    // Filter commits in date range
    foreach ($commitsPage as $commit) {
        if (!isset($commit['commit']['author']['date'])) continue;
        $commitDate = new DateTime($commit['commit']['author']['date']);
        if ($commitDate >= $fromDate && $commitDate <= $toDate) {
            $allCommits[] = $commit;
        }
    }
    $page++;
} while (count($commitsPage) === 100); // Keep paging if full page

$dates = [];
$authorsByDate = [];

// Prepare date keys for x-axis depending on period and from-to range
if ($period === 'daily') {
    $cursor = clone $fromDate;
    while ($cursor <= $toDate) {
        $key = $cursor->format('Y-m-d');
        $dates[$key] = 0;
        $authorsByDate[$key] = [];
        $cursor->modify('+1 day');
    }
} elseif ($period === 'weekly') {
    // Find Monday on or before $fromDate
    $startWeek = clone $fromDate;
    $startWeek->modify('Monday this week');
    $cursor = clone $startWeek;

    while ($cursor <= $toDate) {
        $weekNum = $cursor->format('W');
        $weekYear = $cursor->format('o');
        $weekStart = clone $cursor;
        $weekEnd = clone $cursor;
        $weekEnd->modify('+6 days');
        $label = "Week $weekNum (" . $weekStart->format('M j') . "–" . $weekEnd->format('M j') . ")";
        $dates[$label] = 0;
        $authorsByDate[$label] = [];
        $cursor->modify('+1 week');
    }
}

// Tally commits
foreach ($allCommits as $commit) {
    $commitDateStr = $commit['commit']['author']['date'];
    $timestamp = strtotime($commitDateStr);
    $author = $commit['commit']['author']['name'];

    if ($period === 'daily') {
        $key = date('Y-m-d', $timestamp);
    } else {
        $year = date('o', $timestamp);
        $week = date('W', $timestamp);
        $start = new DateTime();
        $start->setISODate($year, $week);
        $end = clone $start;
        $end->modify('+6 days');
        $key = "Week $week (" . $start->format('M j') . "–" . $end->format('M j') . ")";
    }

    if (!array_key_exists($key, $dates)) continue;

    $dates[$key]++;
    $authorsByDate[$key][] = $author;
}

// Remove duplicate authors
foreach ($authorsByDate as $k => $list) {
    $authorsByDate[$k] = array_unique($list);
}

// Sort keys
if ($period === 'daily') {
    ksort($dates);
} else {
    uksort($dates, function($a, $b) {
        preg_match('/\((.*?)–/', $a, $matchesA);
        preg_match('/\((.*?)–/', $b, $matchesB);
        $dateA = DateTime::createFromFormat('M j', $matchesA[1]);
        $dateB = DateTime::createFromFormat('M j', $matchesB[1]);
        return $dateA <=> $dateB;
    });
}

echo json_encode([
    'labels' => array_keys($dates),
    'counts' => array_values($dates),
    'authors' => array_values($authorsByDate)
]);
