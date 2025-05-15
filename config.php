
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$github_token = $_ENV['GITHUB_TOKEN'];
$repo_owner   = $_ENV['REPO_OWNER'];
$repo = $_ENV['REPO'];
$github_organization = $_ENV['GITHUB_ORGANIZATION'];
return [
    'jiraDomain' => $_ENV['JIRA_DOMAIN'],
    'email' => $_ENV['JIRA_EMAIL'],
    'apiToken' => $_ENV['JIRA_API_TOKEN']
];
?>
