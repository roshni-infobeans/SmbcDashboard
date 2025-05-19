<?php
header('Content-Type: application/json');
require_once 'config.php';

function fetchFromGitHub($url, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName'); // GitHub API requires a user-agent
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "Accept: application/vnd.github+json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$teams = fetchFromGitHub("/orgs/$github_organization/teams", $github_token);

if (!$teams || !is_array($teams)) {
    echo json_encode([]);
    exit;
}

$result = array_map(function($team) {
    return [
        'id' => $team['id'],
        'slug' => $team['slug'],
        'name' => $team['name'],
    ];
}, $teams);

echo json_encode($result);
?>
