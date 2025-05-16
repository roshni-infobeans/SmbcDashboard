<?php
function get_developers_by_team($github_token,$github_organization,$team_slug){
    $api_url = "https://api.github.com/orgs/$github_organization/teams/$team_slug/members";

    // Set up cURL
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
    $members = json_decode($response, true);
    if(!empty($members)){
        $developers = array_column($members,'login');
        return $developers;
    }
    return [];
}

function get_teams_by_organization($github_token,$github_organization){
    $api_url = "https://api.github.com/orgs/$github_organization/teams";
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
  
    return json_decode($response, true);
  }