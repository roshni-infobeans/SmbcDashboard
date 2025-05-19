<?php 
require_once 'config.php';
require_once 'helopers.php';
$teams = get_teams_by_organization($github_token,$github_organization);
?>
<!DOCTYPE html>
<html lang="en">

<?php include 'header.php'; ?>

<body id="page-top">
  <div id="wrapper">
  <?php include 'sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        
        <?php include 'topbar.php'; ?>

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
                    <select class="form-control form-control-sm" id="repo" onchange="handleRepoChange()">
                    </select>
                </div>

                <!-- Developer Dropdown -->
                <div class="form-group mb-2 mr-3">
                    <label for="developer" class="mr-2">Developer:</label>
                    <select class="form-control form-control-sm" id="developer" onchange="handleDeveloperChange()">
                    </select>
                </div>

                <!-- Team Dropdown -->
                <div class="form-group mb-2 mr-3">
                    <label for="team" class="mr-2">Team:</label>
                    <select class="form-control form-control-sm" id="team" onchange="handleTeamChange()">
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
                <div class="btn-group mb-2 mr-3">
                    <div class="btn-group">
                        <button type="button" style="margin-top: 1.9em;" id="dailyTab" class="btn btn-primary btn-sm" onclick="filterByTab('daily')">Daily</button>
                        <button type="button" style="margin-top: 1.9em;" id="weeklyTab" class="btn btn-secondary btn-sm" onclick="filterByTab('weekly')">Weekly</button>
                        <input type="hidden" value="" id="tab" name="tab">
                      </div>
                </div>

                  <div class="form-group mb-2 mr-3" id="simple-date4">
                      <label for="sprint" class="mr-2">Range Select:</label>
                      <div class="input-daterange input-group">
                        <input type="text" placeholder="From" style="width: 100px !important;" id="startDate" class="input-sm form-control form-control-sm mr-3" name="start" />
                        <input type="text" placeholder="To" style="width: 100px !important;" id="endDate" class="input-sm form-control form-control-sm" name="end" />
                      </div>
                  </div>
                  <div class="form-group mb-2" >
                      <button type="button" id="rangeGo" style="margin-top: 1.9em;" class="btn btn-primary btn-sm mr-3">Go</button>
                  </div>   
                  <div class="form-group mb-2" >
                      <button type="button" id="exportBtn" style="margin-top: 1.9em;" class="btn btn-primary btn-sm">Export</button>
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
                    <canvas id="prChart" width="100%" height="20"></canvas>
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
   <!-- Bootstrap Datepicker -->
  <script src="assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
</body>
<script>
    let chart;
    const rangegobtn = document.getElementById('rangeGo');
    const start_date = document.getElementById('startDate');
    const end_date = document.getElementById('endDate');
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
      
      if(tab != 'range'){
        start_date.value = '';
        end_date.value = '';
      }  
      document.getElementById('tab').value = tab;
      const repo = document.getElementById('repo').value;
      const developer = document.getElementById('developer').value;
      const team = document.getElementById('team').value;
      let startDate = '', endDate = '';

      if (tab === 'range') {
        startDate = start_date.value;
        endDate = end_date.value;
      }

      // Highlight the active tab
      document.getElementById('dailyTab').classList.remove('btn-primary');
      document.getElementById('dailyTab').classList.add('btn-secondary');

      document.getElementById('weeklyTab').classList.remove('btn-primary');
      document.getElementById('weeklyTab').classList.add('btn-secondary');

      // Set clicked tab to primary
      if (tab === 'daily') {
        document.getElementById('dailyTab').classList.remove('btn-secondary');
        document.getElementById('dailyTab').classList.add('btn-primary');
      } else if (tab === 'weekly') {
        document.getElementById('weeklyTab').classList.remove('btn-secondary');
        document.getElementById('weeklyTab').classList.add('btn-primary');
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
      rangegobtn.disabled = true;
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
              rangegobtn.disabled = false;
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

    $(document).ready(function () {
      // Bootstrap Date Picker
      $('#simple-date4 .input-daterange').datepicker({        
        format: 'yyyy/mm/dd',        
        autoclose: true,     
        todayHighlight: true,   
        todayBtn: 'linked',
      });

      $('#startDate, #endDate').on('changeDate change', function () {
        $('#tab').val('range');
      });

      //Filter by range
      $(document).on('click','#rangeGo',function(){
        const repo = document.getElementById('repo').value;
        if(repo === undefined){
          alert('Repo required');
          return false;
        }
        if(start_date.value == '' && end_date.value == ''){
          alert('Select date range');
          return false;
        }else if(start_date.value === undefined){
          alert('Select start date');
          return false;
        }else if(end_date.value === undefined){
          alert('Select end date');
          return false;
        }
        filterByTab('range');
      });

      //Export xls,csv
      $('#exportBtn').on('click', function() {
        let tab = document.getElementById('tab').value;
        let repo = document.getElementById('repo').value;
        if(repo == '' || repo == undefined){
          alert('Repo required');
          return false; 
        }
        $.ajax({
            url: 'api_pr_merge_time.php',
            type: 'GET',
            data: {
              repo: repo,
              developer: document.getElementById('developer').value,
              team: document.getElementById('team').value,
              tab: tab,
              startDate:start_date.value,
              endDate: end_date.value,
              export: true,
            },
            xhrFields: {
                responseType: 'blob'  // Expect binary response (CSV/Excel)
            },
            success: function(blob, status, xhr) {
                if (xhr.status === 204) {
                  alert("No data found for the selected criteria.");
                  return;
                }
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = "";
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
                // var link = document.createElement('a');
                // link.href = window.URL.createObjectURL(blob);
                // link.download = "";  // Or .xlsx
                // link.click();
            },
            error: function(xhr, status, error) {
                console.error('Export failed:', error);
            }
        });
      });


    });

  </script>
</html>