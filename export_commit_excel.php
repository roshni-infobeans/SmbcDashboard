<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$repo = $_GET['repo'] ?? '';
$developer = $_GET['developer'] ?? '';
$start_date = $_GET['from'] ?? '';
$end_date = $_GET['to'] ?? '';
$period = $_GET['period'] ?? 'daily';

$commits = [];
$page = 1;
do {
    $url = "https://api.github.com/repos/$repo_owner/$repo/commits?per_page=100&page=$page&since=$start_date"."T00:00:00Z&until=$end_date"."T23:59:59Z";
    if ($developer) {
        $url .= "&author=$developer";
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'CommitVolumeDashboard',
        CURLOPT_HTTPHEADER => ["Authorization: token $github_token"]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (empty($data)) break;

    $commits = array_merge($commits, $data);
    $page++;
} while (count($data) == 100);

// Process data
$rows = [];
foreach ($commits as $commit) {
    $date = new DateTime($commit['commit']['author']['date']);
    $originalAuthor = $commit['commit']['author']['name'];
    $normalizedAuthor = strtolower($originalAuthor);

    if ($period === 'weekly') {
        $year = (int)$date->format("o");
        $week = (int)$date->format("W");

        $monday = new DateTime();
        $monday->setISODate($year, $week);
        $sunday = clone $monday;
        $sunday->modify('+6 days');

        $labelDate = $monday->format('M-j') . ' - ' . $sunday->format('M-j');
        $key = "$normalizedAuthor|$labelDate";
    } else {
        $labelDate = $date->format('Y-m-d');
        $key = "$normalizedAuthor|$labelDate";
    }

    if (!isset($rows[$key])) {
        $rows[$key] = [
            'repo' => $repo,
            'author' => $originalAuthor,
            'date' => $labelDate,
            'count' => 0
        ];
    }
    $rows[$key]['count']++;
}

$fileName = "Export_Commit_Volume-" . date("Y-m-d") . ".xls";

// Output Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$fileName");

echo "<table border='1'>";
echo "<tr><th>No</th><th>Repo</th><th>Dev Name</th><th>Date</th><th>Number of Commits</th></tr>";

$index = 1;
foreach ($rows as $row) {
    echo "<tr>";
    echo "<td>{$index}</td>";
    echo "<td>{$row['repo']}</td>";
    echo "<td>{$row['author']}</td>";
    echo "<td>{$row['date']}</td>";
    echo "<td>{$row['count']}</td>";
    echo "</tr>";
    $index++;
}
echo "</table>";
?>
