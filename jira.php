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
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($result, true);
    return $data['values'] ?? [];
}

function getSprints($jiraDomain, $boardId, $headers) {
    $url = "$jiraDomain/rest/agile/1.0/board/$boardId/sprint?state=active,future,closed";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($result, true);
    return $data['values'] ?? [];
}

function fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange) {
    $jql = "statusCategory = Done";

    if ($timeRange === 'sprint' && $sprintId) {
        $jql .= " AND sprint = $sprintId";
    } else {
        // Restrict issues to selected board's sprints
        $allSprints = getSprints($jiraDomain, $boardId, $headers);
        $sprintIds = array_map(fn($s) => $s['id'], $allSprints);
        if (!empty($sprintIds)) {
            $sprintIdsStr = implode(',', $sprintIds);
            $jql .= " AND sprint IN ($sprintIdsStr)";
        }

        if ($timeRange === 'daily') {
            $today = date('Y-m-d');
            $jql .= " AND resolved >= $today";
        } elseif ($timeRange === 'weekly') {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $jql .= " AND resolved >= $sevenDaysAgo";
        }
    }

    if (!empty($developer)) {
        $jql .= " AND assignee = \"$developer\"";
    }

    $url = "$jiraDomain/rest/api/3/search?jql=" . urlencode($jql) . "&fields=summary,status,assignee,$storyPointField,resolutiondate&maxResults=100";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function calculateStoryPoints($issues, $storyPointField, &$developers) {
    $total = 0;
    $labels = [];
    $points = [];
    foreach ($issues['issues'] as $issue) {
        $summary = $issue['fields']['summary'] ?? 'Unknown';
        $value = $issue['fields'][$storyPointField] ?? 0;
        $assignee = $issue['fields']['assignee']['displayName'] ?? '';
        $accountId = $issue['fields']['assignee']['accountId'] ?? '';
        if ($accountId) {
            $developers[$accountId] = $assignee;
        }
        $total += $value;
        $labels[] = $summary;
        $points[] = $value;
    }
    return [$total, $labels, $points];
}

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
$timeRange = 'weekly'; // force weekly as the only option
$storyPointField = $_GET['storyPointField'] ?? 'customfield_10016';

$sprints = getSprints($jiraDomain, $boardId, $headers);

$sprintName = 'N/A';
if ($sprintId) {
    foreach ($sprints as $sprint) {
        if ($sprint['id'] == $sprintId) {
            $sprintName = $sprint['name'];
            break;
        }
    }
}

// Step 1: Get all issues from the selected board (for listing developers)
$allIssues = fetchIssues($jiraDomain, $boardId, $sprintId, '', $headers, $storyPointField, $timeRange);
$developers = [];
calculateStoryPoints($allIssues, $storyPointField, $developers);

// Step 2: Filter issues by developer
$issues = $developer ? fetchIssues($jiraDomain, $boardId, $sprintId, $developer, $headers, $storyPointField, $timeRange) : $allIssues;
[$totalPoints, $issueLabels, $issuePoints] = calculateStoryPoints($issues, $storyPointField, $developers);
?>

<form method="get" style="margin-bottom: 20px;">
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card p-3">
                <div class="d-flex flex-wrap align-items-center mb-3">
                    <div class="form-group mb-2 mr-3">
                        <label for="repo" class="mr-2">Project:</label>
                        <select disabled  class="form-control" name="boardId" onchange="this.form.submit()">
                            <?php foreach ($boards as $board): ?>
                                <option value="<?= $board['id'] ?>" <?= $board['id'] == $boardId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($board['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    
                    <div class="form-group mb-2 mr-3" id="sprintSelect">
                        <label for="sprintId" class="mr-2">Sprint:</label>
                        <select class="form-control" name="sprintId" onchange="this.form.submit()">
                            <option value="">-- Select Sprint --</option>
                            <?php foreach ($sprints as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $s['id'] == $sprintId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
<div hidden class="form-group mb-2 mr-3">
    <label for="timeRange" class="mr-2">Time Range:</label>
    <select class="form-control" name="timeRange" onchange="this.form.submit()">
        <option value="weekly" selected>Weekly</option>
    </select>
</div>
                    <div class="form-group mb-2 mr-3">
                        <label for="developer">Developer:</label>
                        <select class="form-control" name="developer" onchange="this.form.submit()">
                            <option value="">-- All Developers --</option>
                            <?php foreach ($developers as $id => $name): ?>
                                <option value="<?= $id ?>" <?= $id == $developer ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden" name="storyPointField" value="<?= htmlspecialchars($storyPointField) ?>">

                </div>
            </div>
        </div>
    </div>
</form>

<p><strong>Total Completed Story Points: <?= $totalPoints ?></strong></p>
<div class="row">
                    <div class="col-lg-12">
                    <div class="card p-4">
                        <h5 class="card-title">Chart</h5>
<canvas id="storyChart" width="800" height="400"></canvas>
</div></div></div>
<script>
    const issueLabels = <?= json_encode(array_map(function($label) {
        return mb_strlen($label) > 30 ? mb_substr($label, 0, 27) . '...' : $label;
    }, $issueLabels)) ?>;

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