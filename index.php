<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PR Merge Time</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <h2>Time to Merge Pull Requests</h2>

  <label for="repo">Repo:</label>
  <select id="repo">
    <option value="mediquick" selected>SMBC Dashboard</option>
    <option value="repo2">Repo 2</option>
  </select>

  <label for="developer">Developer:</label>
  <select id="developer" onchange="handleDeveloperChange()">
    <option value="">Select Developer</option>
    <option value="arjun2588">Arjun</option>
    <option value="dev2">Dev 2</option>
  </select>

  <label for="team">Team:</label>
  <select id="team" onchange="handleTeamChange()">
  <option value="">Select Team</option>
    <option value="Developers-Team">Developers Team</option>
    <option value="team2">Team 2</option>
  </select>

  <div style="margin-top: 20px;">
    <button onclick="filterByTab('daily')">Daily</button>
    <button onclick="filterByTab('weekly')">Weekly</button>

    <label for="sprint">Sprint:</label>
    <select id="sprint" onchange="filterByTab('sprint')">
      <option value="">Select Sprint</option>
      <option value="2025-05-01@@@2025-05-21">Sprint 1</option>
      <option value="2025-05-22@@@2025-06-14">Sprint 2</option>
    </select>
  </div>

  <div style="width: 800px; margin-top: 30px;">
    <canvas id="prChart"></canvas>
  </div>

  <script>
    let chart;

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

      fetch(`pr_merge_time.php?repo=${repo}&developer=${developer}&team=${team}&tab=${tab}&startDate=${startDate}&endDate=${endDate}`)
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
              max:60,
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
      filterByTab('daily');
    };
  </script>
</body>
</html>

