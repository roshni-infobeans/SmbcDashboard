<?php
header('Content-Type: application/json');
require_once 'config.php';

$team_slug = $_GET['team_slug'] ?? '';

if (!$team_slug) {
    echo json_encode([]);
    exit;
}

function fetchFromGitHub($url, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "Accept: application/vnd.github+json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$members = fetchFromGitHub("/orgs/$github_organization/teams/$team_slug/members", $github_token);

if (!$members || !is_array($members)) {
    echo json_encode([]);
    exit;
}

$result = array_map(function($member) {
    return [
        'login' => $member['login'],
        'id' => $member['id']
    ];
}, $members);

echo json_encode($result);
?>
