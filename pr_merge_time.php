<?php 
require_once 'config.php';
require_once 'helopers.php';
$teams = get_teams_by_organization($github_token,$github_organization);
?>
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
</head>

<body id="page-top">
  <div id="wrapper">
  <?php include 'sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
          <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
        </nav>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Time to Merge Pull Requests</h1>
        </div>

        <div class="row mb-3">
            <div class="col-lg-12">
            <div class="card p-3">
                <div class="d-flex flex-wrap align-items-center mb-3">

                <!-- Repo Dropdown -->
                <div class="form-group mb-2 mr-3">
                    <label for="repo" class="mr-2">Repo:</label>
                    <select class="form-control" id="repo" onchange="handleRepoChange()">
                    </select>
                </div>

                <!-- Developer Dropdown -->
                <div class="form-group mb-2 mr-3">
                    <label for="developer" class="mr-2">Developer:</label>
                    <select class="form-control" id="developer" onchange="handleDeveloperChange()">
                    </select>
                </div>

                <!-- Team Dropdown -->
                <div class="form-group mb-2 mr-3">
                    <label for="team" class="mr-2">Team:</label>
                    <select class="form-control" id="team" onchange="handleTeamChange()">
                    <option value="">All Teams</option>
                      <?php
                        foreach($teams as $team){
                      ?>
                        <option value="<?php echo $team['slug']?>"><?php echo $team['name']?></option>    
                      <?php  
                        }
                      ?>
                    </select>
                </div>

                <!-- Tab Buttons -->
                <div class="btn-group mb-2 mr-3" style="margin-top: 1.7em;height:calc(1.5em + .75rem + 7px);">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" onclick="filterByTab('daily')">Daily</button>
                        <button type="button" class="btn btn-secondary" onclick="filterByTab('weekly')">Weekly</button>
                    </div>
                </div>

                <!-- Sprint Dropdown -->
                <div class="form-group mb-2">
                    <label for="sprint" class="mr-2">Sprint:</label>
                    <select class="form-control" id="sprint" onchange="filterByTab('sprint')">
                    <option value="">Select Sprint</option>
                    <option value="2025-05-01@@@2025-05-21">Sprint 1</option>
                    <option value="2025-05-22@@@2025-06-14">Sprint 2</option>
                    </select>
                </div>

                </div>
            </div>
            </div>
        </div>
            <!-- Chart Card -->
            <div class="row">
                <div class="col-lg-12">
                <div class="card p-4">
                    <h5 class="card-title">Chart</h5>
                    <div>
                    <canvas id="prChart" width="100%" height="40"></canvas>
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- <script src="assets/vendor/chart.js/Chart.min.js"></script> -->
  <!-- <script src="assets/js/demo/chart-area-demo.js"></script>   -->
</body>
<script>
    let chart;
    const repoOwner = '<?php echo $repo_owner;?>';
    const organization = '<?php echo $github_organization;?>';

    function handleRepoChange() {
        const repo = document.getElementById('repo').value;
        fetchDevelopers(repoOwner, repo)
    }

    function handleDeveloperChange() {
        const developer = document.getElementById('developer').value;
        const teamSelect = document.getElementById('team');
        if (developer) {
            teamSelect.selectedIndex = 0;
        }
    }

    function handleTeamChange() {
        const team = document.getElementById('team').value;
        const developerSelect = document.getElementById('developer');
        if (team) {
            developerSelect.selectedIndex = 0;
        }
    }
    function filterByTab(tab) {
      const sprint = document.getElementById('sprint');
      if(tab != 'sprint'){
        sprint.selectedIndex = 0;
      }  
      const repo = document.getElementById('repo').value;
      const developer = document.getElementById('developer').value;
      const team = document.getElementById('team').value;
      let startDate = '', endDate = '';

      if (tab === 'sprint') {
        const sprintRange = document.getElementById('sprint').value;
        const dates = sprintRange.split('@@@');
        startDate = dates[0];
        endDate = dates[1];
      }

      fetch(`api_pr_merge_time.php?repo=${repo}&developer=${developer}&team=${team}&tab=${tab}&startDate=${startDate}&endDate=${endDate}`)
        .then(response => response.json())
        .then(data => {
          renderChart(data.labels, data.values, data.displays);
        });
    }

    function renderChart(labels, values, displays) {
      const ctx = document.getElementById('prChart').getContext('2d');
      if (chart) chart.destroy();

      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Time to Merge (Minutes)',
            data: values,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  return `Time: ${displays[context.dataIndex]}`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              suggestedMin: 0,
              suggestedMax: 60,
              ticks: {
                stepSize: 5
              },
              title: {
                display: true,
                text: 'Minutes'
              }
            }
          }
        }
      });
    }

    // Load default data on page load
    window.onload = () => {
      
      const apiUrl = 'https://api.github.com/users/'+repoOwner+'/repos'; 

      // Call the API to get the repo data
      fetch(apiUrl)
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              // Assuming data is an array
              populateRepoDropdown(data);
              filterByTab('daily'); 
              if (data.length > 0) {
                fetchDevelopers(repoOwner, data[0].name); // Fetch developers for first repo
              }
          })
          .catch(error => {
              console.error('There has been a problem with your fetch operation:', error);
          }); 
    };

    // Function to populate the dropdown
    function populateRepoDropdown(data) {
        const dropdown = document.getElementById('repo');
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.name;
            option.text = item.name;
            dropdown.appendChild(option);
        });
    }
    
    //
    function fetchDevelopers(owner, repo) {
      const apiUrl = `https://api.github.com/repos/${owner}/${repo}/contributors`;

      fetch(apiUrl)
        .then(response => {
          if (!response.ok) {
            throw new Error('Error fetching developers');
          }
          return response.json();
        })
        .then(data => {
          populateDeveloperDropdown(data);
        })
        .catch(error => {
          console.error('Error fetching developers:', error);
        });
    }

    function populateDeveloperDropdown(contributors) {
      const dropdown = document.getElementById('developer');
      dropdown.innerHTML = '<option value="">All Developers</option>';

      contributors.forEach(user => {
        const option = document.createElement('option');
        option.value = user.login;
        option.text = user.login;
        dropdown.appendChild(option);
      });
    }
  </script>
</html>