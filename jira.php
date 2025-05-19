<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- <link href="assets/img/logo/logo.png" rel="icon"> -->
  <title>SMBC - Dashboard</title>
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="assets/css/ruang-admin.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style type="text/css">
      .btn-outline-primary:not(:disabled):not(.disabled).active, .btn-outline-primary:not(:disabled):not(.disabled):active, .show > .btn-outline-primary.dropdown-toggle {
  color: #fff;
  background-color: #a0c81e;
  border-color: #a0c81e;
}
.btn-outline-primary {
    color:#616161;
    border-color: #616161;
    
}
.btn-outline-primary:hover {
    color: #fff;
    background-color: #616161;
    border-color: #616161;
}
  </style>
</head>

<body id="page-top">
 <div id="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include 'topbar.php'; ?>
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Story Points Dashboard</h1>
            <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol> -->
        </div>


<?php
$config = require 'config.php';

$jiraDomain = $config['jiraDomain'];
$email = $config['email'];
$apiToken = $config['apiToken'];

$auth = base64_encode("$email:$apiToken");
$headers = [
    "Authorization: Basic $auth",
    "Accept: application/json"
];

function getBoards($jiraDomain, $headers) {
    $url = "$jiraDomain/rest/agile/1.0/board?maxResults=1000";
    $result = curlRequest($url, $headers);
    return $result['values'] ?? [];
}

function getSprints($jiraDomain, $boardId, $headers) {
    $url = "$jiraDomain/rest/agile/1.0/board/$boardId/sprint?state=active,future,closed";
    $result = curlRequest($url, $headers);
    return $result['values'] ?? [];
}

function curlRequest($url, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange, $startDate = '', $endDate = '') {
 
    $jql = "statusCategory = Done";

    if (!empty($sprintId)) {
        $jql .= " AND sprint = $sprintId";
    } else {
        $allSprints = getSprints($jiraDomain, $boardId, $headers);
        $sprintIds = array_map(fn($s) => $s['id'], $allSprints);
        if (!empty($sprintIds)) {
            $jql .= " AND sprint IN (" . implode(',', $sprintIds) . ")";
        }

        if ($timeRange === 'daily') {
            $today = date('Y-m-d');
            $jql .= " AND resolved >= \"$today\"";
        } elseif ($timeRange === 'weekly') {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $jql .= " AND resolved >= \"$sevenDaysAgo\"";
        }
    }

   if (!empty($startDate)) {
    $jql .= " AND resolved >= \"$startDate\"";
}
if (!empty($endDate)) {
    $jql .= " AND resolved <= \"$endDate\"";
}


    if (!empty($developer)) {
        $jql .= " AND assignee = \"$developer\"";
    }

    $url = "$jiraDomain/rest/api/3/search?jql=" . urlencode($jql) . "&fields=summary,status,assignee,$storyPointField,resolutiondate&maxResults=100";
    return curlRequest($url, $headers);
}

function calculateStoryPoints($issues, $storyPointField, &$developers) {
    $total = 0;
    $data = [];

    foreach ($issues['issues'] as $issue) {
        $summary = $issue['fields']['summary'] ?? 'Unknown';
        $points = $issue['fields'][$storyPointField] ?? 0;
        $assignee = $issue['fields']['assignee']['displayName'] ?? '';
        $accountId = $issue['fields']['assignee']['accountId'] ?? '';

        if ($accountId) $developers[$accountId] = $assignee;
        $total += $points;

        $data[] = ['summary' => $summary, 'points' => $points];
    }

    usort($data, function ($a, $b) {
        preg_match('/\d+/', strtolower($a['summary']), $aNum);
        preg_match('/\d+/', strtolower($b['summary']), $bNum);
        return ($aNum[0] ?? PHP_INT_MAX) <=> ($bNum[0] ?? PHP_INT_MAX);
    });

    return [$total, array_column($data, 'summary'), array_column($data, 'points')];
}

// Initial setup
$boards = getBoards($jiraDomain, $headers);
$defaultBoardId = null;
foreach ($boards as $board) {
    if (stripos($board['name'], 'TP') !== false) {
        $defaultBoardId = $board['id'];
        break;
    }
}

$boardId = $_GET['boardId'] ?? $defaultBoardId ?? ($boards[0]['id'] ?? 1);
$sprintId = $_GET['sprintId'] ?? '';
$developer = $_GET['developer'] ?? '';
$timeRange = $_GET['timeRange'] ?? 'weekly';
$storyPointField = $_GET['storyPointField'] ?? 'customfield_10016';
$startDate = $_GET['startDate'] ?? '2025-05-01';
$endDate = $_GET['endDate'] ?? '2025-05-31';



$sprints = getSprints($jiraDomain, $boardId, $headers);
$sprintName = 'N/A';
foreach ($sprints as $s) {
    if ($s['id'] == $sprintId) {
        $sprintName = $s['name'];
        break;
    }
}

$allIssues = fetchIssues($jiraDomain, $boardId, $sprintId, '', $headers, $storyPointField, $timeRange, $startDate, $endDate);
$developers = [];
calculateStoryPoints($allIssues, $storyPointField, $developers);

$issues = $developer ? fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange, $startDate, $endDate) : $allIssues;
[$totalPoints, $issueLabels, $issuePoints] = calculateStoryPoints($issues, $storyPointField, $developers);
?>

<!-- HTML Form -->
<form method="get" style="margin-bottom: 20px;">
    <div class="card p-3 mb-3">
        <div class="form-row">
            <div class="form-group mr-3">
                <label>Project:</label>
                <select disabled class="form-control" name="boardId">
                    <?php foreach ($boards as $board): ?>
                        <option value="<?= $board['id'] ?>" <?= $board['id'] == $boardId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($board['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mr-3">
                <label>Sprint:</label>
                <select class="form-control" name="sprintId" onchange="this.form.submit()">
                    <option value="">-- All Sprints --</option>
                    <?php foreach ($sprints as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $sprintId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mr-3">
                <label>Developer:</label>
                <select class="form-control" name="developer" onchange="this.form.submit()">
                    <option value="">-- All Developers --</option>
                    <?php foreach ($developers as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $id == $developer ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mr-3">
                <label>Start Date:</label>
                <input type="date" class="form-control" name="startDate" value="<?= htmlspecialchars($startDate) ?>" onchange="this.form.submit()">
            </div>

            <div class="form-group mr-3">
                <label>End Date:</label>
                <input type="date" class="form-control" name="endDate" value="<?= htmlspecialchars($endDate) ?>" onchange="this.form.submit()">
            </div>

            <input type="hidden" name="storyPointField" value="<?= htmlspecialchars($storyPointField) ?>">

            <div class="form-group ml-3">
                <label>Time Range:</label><br>
                <input type="hidden" name="timeRange" id="timeRangeInput" value="<?= htmlspecialchars($timeRange) ?>">
                <div class="btn-group">
                    <button type="submit" class="btn btn-sm btn-outline-primary <?= $timeRange === 'daily' ? 'active' : '' ?>"
                            onclick="document.getElementById('timeRangeInput').value='daily'">Daily</button>
                    <button type="submit" class="btn btn-sm btn-outline-primary <?= $timeRange === 'weekly' ? 'active' : '' ?>"
                            onclick="document.getElementById('timeRangeInput').value='weekly'">Weekly</button>
                </div>
            </div>

            <div class="form-group ml-3 mt-4">
                <a href="export_excel.php?sprintId=<?= urlencode($sprintId) ?>&boardId=<?= urlencode($boardId) ?>&developer=<?= urlencode($developer) ?>&storyPointField=<?= urlencode($storyPointField) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>"
                   class="btn btn-outline-primary" target="_blank">Export to Excel</a>
            </div>
        </div>
    </div>
</form>


<p><strong>Total Completed Story Points: <?= $totalPoints ?></strong></p>

<!-- Chart -->
<div class="card p-4">
    <h5 class="card-title">Chart</h5>
    <canvas id="storyChart" width="800" height="400"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const issueLabels = <?= json_encode(array_map(fn($label) => mb_strlen($label) > 30 ? mb_substr($label, 0, 27) . '...' : $label, $issueLabels)) ?>;
    const chartTitle = '<?= $developer ? "Tasks for " . addslashes($developers[$developer] ?? "Developer") : "All Developers" ?> - <?= ucfirst($timeRange) ?>';

    const ctx = document.getElementById('storyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: issueLabels,
            datasets: [{
                label: 'Story Points',
                data: <?= json_encode($issuePoints) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: chartTitle
                },
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Points' }
                },
                x: {
                    title: { display: true, text: 'Tasks (Summaries)' },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0
                    }
                }
            }
        }
    });
</script>


</div>

            </div>
            </div>
        </div>

        </div>
        <!-- End Container Fluid -->

      </div>
      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>copyright &copy; <script> document.write(new Date().getFullYear()); </script>
              <b>InfoBeans</b>
            </span>
          </div>
        </div>
      </footer>
      <!-- Footer -->
    </div>
  </div>

 <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="assets/js/ruang-admin.min.js"></script>
  <script src="assets/vendor/chart.js/Chart.min.js"></script>
  <script src="assets/js/demo/chart-area-demo.js"></script>  

</body>
</html>