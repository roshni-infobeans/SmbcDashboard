<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="assets/img/logo/logo.png" rel="icon">
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
        <!-- TopBar -->
        <nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
          <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-1 small" placeholder="What do you want to look for?"
                      aria-label="Search" aria-describedby="basic-addon2" style="border-color: #3f51b5;">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-danger badge-counter">3+</span>
              </a>
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  Alerts Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                  <div class="mr-3">
                    <div class="icon-circle bg-primary">
                      <i class="fas fa-file-alt text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500">December 12, 2019</div>
                    <span class="font-weight-bold">A new monthly report is ready to download!</span>
                  </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                  <div class="mr-3">
                    <div class="icon-circle bg-success">
                      <i class="fas fa-donate text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500">December 7, 2019</div>
                    $290.29 has been deposited into your account!
                  </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                  <div class="mr-3">
                    <div class="icon-circle bg-warning">
                      <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500">December 2, 2019</div>
                    Spending Alert: We've noticed unusually high spending for your account.
                  </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
              </div>
            </li>
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <span class="badge badge-warning badge-counter">2</span>
              </a>
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                  Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                  <div class="dropdown-list-image mr-3">
                    <img class="rounded-circle" src="img/man.png" style="max-width: 60px" alt="">
                    <div class="status-indicator bg-success"></div>
                  </div>
                  <div class="font-weight-bold">
                    <div class="text-truncate">Hi there! I am wondering if you can help me with a problem I've been
                      having.</div>
                    <div class="small text-gray-500">Udin Cilok · 58m</div>
                  </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                  <div class="dropdown-list-image mr-3">
                    <img class="rounded-circle" src="img/girl.png" style="max-width: 60px" alt="">
                    <div class="status-indicator bg-default"></div>
                  </div>
                  <div>
                    <div class="text-truncate">Am I a good boy? The reason I ask is because someone told me that people
                      say this to all dogs, even if they aren't good...</div>
                    <div class="small text-gray-500">Jaenab · 2w</div>
                  </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
              </div>
            </li>
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-tasks fa-fw"></i>
                <span class="badge badge-success badge-counter">3</span>
              </a>
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                  Task
                </h6>
                <a class="dropdown-item align-items-center" href="#">
                  <div class="mb-3">
                    <div class="small text-gray-500">Design Button
                      <div class="small float-right"><b>50%</b></div>
                    </div>
                    <div class="progress" style="height: 12px;">
                      <div class="progress-bar bg-success" role="progressbar" style="width: 50%" aria-valuenow="50"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                </a>
                <a class="dropdown-item align-items-center" href="#">
                  <div class="mb-3">
                    <div class="small text-gray-500">Make Beautiful Transitions
                      <div class="small float-right"><b>30%</b></div>
                    </div>
                    <div class="progress" style="height: 12px;">
                      <div class="progress-bar bg-warning" role="progressbar" style="width: 30%" aria-valuenow="30"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                </a>
                <a class="dropdown-item align-items-center" href="#">
                  <div class="mb-3">
                    <div class="small text-gray-500">Create Pie Chart
                      <div class="small float-right"><b>75%</b></div>
                    </div>
                    <div class="progress" style="height: 12px;">
                      <div class="progress-bar bg-danger" role="progressbar" style="width: 75%" aria-valuenow="75"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">View All Taks</a>
              </div>
            </li>
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle" src="img/boy.png" style="max-width: 60px">
                <span class="ml-2 d-none d-lg-inline text-white small">User Profile</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>
        </nav>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Story Points Dashboard</h1>
            <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
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
$timeRange = $_GET['timeRange'] ?? 'sprint';
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
                        <label for="repo" class="mr-2">Board:</label>
                        <select class="form-control" name="boardId" onchange="this.form.submit()">
                            <?php foreach ($boards as $board): ?>
                                <option value="<?= $board['id'] ?>" <?= $board['id'] == $boardId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($board['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-2 mr-3">
                        <label for="timeRange" class="mr-2">Time Range:</label>
                        <select class="form-control" name="timeRange" onchange="this.form.submit()">
                            <option value="sprint" <?= $timeRange == 'sprint' ? 'selected' : '' ?>>Sprint</option>
                            <option value="daily" <?= $timeRange == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $timeRange == 'weekly' ? 'selected' : '' ?>>Weekly</option>
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
