<?php
include 'config.php';

// === Auth Header ===
$auth = base64_encode("$email:$apiToken");
$headers = [
    "Authorization: Basic $auth",
    "Content-Type: application/json",
    "Accept: application/json"
];

// === Fetch all Epics ===
$jql = "issuetype = Epic ORDER BY key ASC";
$epicUrl = "https://$jiraDomain/rest/api/3/search?jql=" . urlencode($jql) . "&maxResults=100&fields=summary,priority,project,customfield_10091,customfield_10092,customfield_10093,customfield_10096,created";
$epicResponse = makeRequest($epicUrl, $headers);
$epics = $epicResponse['issues'] ?? [];

// === Collect Unique Filter Options ===
$projectNames = [];
$hiringStatuses = [];
$groups = [];
$epicPriorities = [];

foreach ($epics as $epic) {
    $projectNames[] = trim($epic['fields']['customfield_10092']) ?? '';
    $hiringStatuses[] = $epic['fields']['customfield_10096']['value'] ?? '';
    $groups[] = $epic['fields']['customfield_10091']['value'] ?? '';
    $epicPriorities[] = $epic['fields']['customfield_10093']['value'] ?? '';
}
$projectNames = array_unique(array_filter($projectNames));
$hiringStatuses = array_unique(array_filter($hiringStatuses));
$groups = array_unique(array_filter($groups));
$epicPriorities = array_unique(array_filter($epicPriorities));

// === Capture Filters from GET ===
$filterProject = $_GET['project'] ?? '';
$filterStatus = $_GET['sfilterStatustatus'] ?? '';
$filterGroup = $_GET['group'] ?? '';
$filterPriority = $_GET['priority'] ?? '';

// === HTML Header ===
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Jira Epic Report</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f9fbfd; color: #333; }
    h1 { color: #0052cc; margin-bottom: 20px; }
    table { border-collapse: collapse; width: 100%; background: white; }
    th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: center; cursor: pointer; }
    th { background-color: #e1ecf7; color: #0747a6; position: relative; }
    th .arrow { font-size: 12px; margin-left: 4px; opacity: 0.4; }
    th.active-sort { background-color: #cde4ff !important; }
    th .arrow.active { opacity: 1; color: #000; font-weight: bold; }
    .no-tasks-row { background-color: #ffe6e6; }
    form.filters { margin-bottom: 20px; background: #eef3f7; padding: 15px; border-radius: 5px; }
    form.filters label { margin-right: 20px; font-weight: bold; }
    form.filters select, form.filters button { padding: 5px 10px; margin-left: 5px; }
    th .arrow {
    font-size: 14px;
    margin-left: 4px;
    opacity: 0.4;
    color: #666;
    display: inline-block;
}

th .arrow.active {
    opacity: 1;
    color: #000;
    font-weight: bold;
}
</style>
</head>
<body>
<h1>Jira Epics Hiring Report</h1>

<form class="filters" method="GET">
    <label>Project:
        <select name="project">
            <option value="">-- All --</option>
HTML;

foreach ($projectNames as $project) {
    $selected = ($filterProject === $project) ? 'selected' : '';
    echo "<option value=\"$project\" $selected>$project</option>";
}

echo <<<HTML
        </select>
    </label>
    <label>Hiring Status:
        <select name="status">
            <option value="">-- All --</option>
HTML;

foreach ($hiringStatuses as $status) {
    $selected = ($filterStatus === $status) ? 'selected' : '';
    echo "<option value=\"$status\" $selected>$status</option>";
}

echo <<<HTML
        </select>
    </label>
    <label>Group:
        <select name="group">
            <option value="">-- All --</option>
HTML;

foreach ($groups as $group) {
    $selected = ($filterGroup === $group) ? 'selected' : '';
    echo "<option value=\"$group\" $selected>$group</option>";
}

echo <<<HTML
        </select>
    </label>
    <label>Epic Priority:
        <select name="priority">
            <option value="">-- All --</option>
HTML;

foreach ($epicPriorities as $priority) {
    $selected = ($filterPriority === $priority) ? 'selected' : '';
    echo "<option value=\"$priority\" $selected>$priority</option>";
}

echo <<<HTML
        </select>
    </label>
    <button type="submit">🔍 Apply</button>
    <a href="?"><button type="button">🔄 Reset</button></a>
</form>

<form method="post" action="export_jira_epics.php" target="_blank">
  <button type="submit">📥 Export to Excel</button>
</form>

<table id="epicTable">
<tr>
    <th>Key  <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>Group  <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>EPIC Summary  <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>EPIC HiringStatus  <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>EPIC Priority <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>EPIC ProjectName <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>Open Since -CL <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>0. PROFILES (TO BE SHARED) <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>1. Profile Feedback Pending <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>2. C-INTERVIEW SCHEDULED <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>3. C-INTERVIEW FBK PENDING <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>4. SELECTED <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>10. On-boarded <span class="arrow">▲</span><span class="arrow">▼</span></th>
    <th>Epic Age <span class="arrow">▲</span><span class="arrow">▼</span></th>
</tr>
HTML;

// === Display Filtered Epics ===
foreach ($epics as $epic) {
    $epicKey = $epic['key'];
    $epicSummary = $epic['fields']['summary'] ?? '';
    $epicPriority = $epic['fields']['customfield_10093']['value'] ?? '';
    $epicProject = $epic['fields']['customfield_10092'] ?? '';
    $epicGroup = $epic['fields']['customfield_10091']['value'] ?? 'N/A';
    $epicHiringStatus = $epic['fields']['customfield_10096']['value'] ?? 'N/A';
    $epicCreated = $epic['fields']['created'] ?? 'N/A';

    // Filter conditions
    if (
        ($filterProject && $filterProject !== $epicProject) ||
        ($filterStatus && $filterStatus !== $epicHiringStatus) ||
        ($filterGroup && $filterGroup !== $epicGroup) ||
        ($filterPriority && $filterPriority !== $epicPriority)
    ) {
        continue;
    }

    $dt = DateTime::createFromFormat('Y-m-d\TH:i:s.uO', $epicCreated);
    $epicOpenSince = $dt->format('Y-m-d H:i:s');
    $CurrentDate = new DateTime();
    $epicAge = $CurrentDate->diff($dt);

    $childJql = "\"Epic Link\" = $epicKey";
    $fields = "status";
    $childUrl = "https://$jiraDomain/rest/api/3/search?jql=" . urlencode($childJql) . "&maxResults=200&fields=" . urlencode($fields);
    $childResponse = makeRequest($childUrl, $headers);
    $children = $childResponse['issues'] ?? [];

    $statusBuckets = [
        "0. PROFILES (TO BE SHARED)" => 0,
        "1. Profile Feedback Pending" => 0,
        "2. C-INTERVIEW SCHEDULED" => 0,
        "3. C-INTERVIEW FBK PENDING" => 0,
        "4. SELECTED" => 0,
        "10. On-boarded" => 0,
    ];

    foreach ($children as $child) {
        $status = $child['fields']['status']['name'] ?? 'Unknown';
        foreach ($statusBuckets as $bucket => $count) {
            if (stripos($status, explode('.', $bucket)[1]) !== false) {
                $statusBuckets[$bucket]++;
                break;
            }
        }
    }

    $allZero = array_sum($statusBuckets) === 0;
    $rowClass = $allZero ? " class='no-tasks-row'" : "";
    echo "<tr$rowClass>";
    echo "<td>$epicKey</td>";
    echo "<td>$epicGroup</td>";
    echo "<td>$epicSummary</td>";
    echo "<td>$epicHiringStatus</td>";
    echo "<td>$epicPriority</td>";
    echo "<td>$epicProject</td>";
    echo "<td>$epicOpenSince</td>";
    foreach ($statusBuckets as $count) {
        echo "<td>$count</td>";
    }
    echo "<td>".$epicAge->days." days</td>";
    echo "</tr>";
}

echo <<<HTML
</table>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("epicTable");
    const headers = table.querySelectorAll("th");
    let sortColumn = -1;
    let sortDirection = 1;

    headers.forEach((header, index) => {
        header.addEventListener("click", () => {
            if (!header.classList.contains("active-sort")) {
                sortDirection = 1;
            } else {
                sortDirection *= -1;
            }
            sortColumn = index;

            const rows = Array.from(table.querySelectorAll("tr:nth-child(n+2)"));
            const isNumeric = !isNaN(rows[0].cells[index].innerText.trim());

            rows.sort((a, b) => {
                let aText = a.cells[index].innerText.trim();
                let bText = b.cells[index].innerText.trim();
                if (isNumeric) {
                    aText = parseFloat(aText) || 0;
                    bText = parseFloat(bText) || 0;
                }
                return (aText > bText ? 1 : aText < bText ? -1 : 0) * sortDirection;
            });

            rows.forEach(row => table.appendChild(row));

            headers.forEach(h => {
                h.classList.remove("active-sort");
                h.querySelectorAll(".arrow").forEach(arrow => arrow.classList.remove("active"));
            });

            header.classList.add("active-sort");
            const arrows = header.querySelectorAll(".arrow");
            if (sortDirection === 1) {
                arrows[0].classList.add("active");
            } else {
                arrows[1].classList.add("active");
            }
        });
    });
});
</script>
</body>
</html>
HTML;

function makeRequest($url, $headers) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "<p style='color:red;'>cURL error: " . curl_error($ch) . "</p>";
    }
    curl_close($ch);
    return json_decode($response, true);
}
?>
