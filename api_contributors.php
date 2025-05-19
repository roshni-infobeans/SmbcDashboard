<?php
// include 'config.php';

// if (!isset($_GET['repo'])) {
//     echo json_encode([]);
//     exit;
// }

// $repo = $_GET['repo'];
// $url = "https://api.github.com/repos/$repo_owner/$repo/contributors";

// $ch = curl_init($url);
// curl_setopt_array($ch, [
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_SSL_VERIFYPEER => false,
//     CURLOPT_USERAGENT => 'PHP App',
//     CURLOPT_HTTPHEADER => [
//         "Authorization: token $github_token"
//     ]
// ]);

// $response = curl_exec($ch);
// curl_close($ch);
// $data = json_decode($response, true);

// $developers = [];
// if (is_array($data)) {
//     foreach ($data as $dev) {
//         $developers[] = [
//             'login' => $dev['login'],
//             'name' => $dev['login'] // fallback if full name not fetched
//         ];
//     }
// }

// echo json_encode($developers);



include 'config.php';

if (!isset($_GET['repo'])) {
    echo json_encode([]);
    exit;
}

$repo = $_GET['repo'];
$url = "https://api.github.com/repos/$repo_owner/$repo/contributors";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'PHP App',
    CURLOPT_HTTPHEADER => [
        "Authorization: token $github_token"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

$developers = [];
if (is_array($data)) {
    foreach ($data as $dev) {
        $developers[] = [
            'login' => $dev['login'],
            'name' => $dev['login'] // fallback if full name not fetched
        ];
    }
}

echo json_encode($developers);
