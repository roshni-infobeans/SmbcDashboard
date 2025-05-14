
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$github_token = $_ENV['GITHUB_TOKEN'];
$repo_owner   = $_ENV['REPO_OWNER'];

?>

