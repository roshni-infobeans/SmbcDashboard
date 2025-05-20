<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>
<body id="page-top">
<div id="wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include 'topbar.php'; ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Commit Volume</h1>
                </div>

                <?php
                    include 'config.php';

                    function fetchFromGitHub($repo_owner, $repo, $github_token){
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
                        return json_decode($response, true);
                    }

                    $contribs = fetchFromGitHub($repo_owner, $repo, $github_token);

                    function fetchRepo($repo_owner, $github_token) {
                        $apiUrl = "https://api.github.com/users/$repo_owner/repos";
                        $ch = curl_init($apiUrl);
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
                        return json_decode($response, true);
                    }       
                    $repo = fetchRepo($repo_owner, $github_token);                
                ?>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="card p-3">
                            <div class="d-flex flex-wrap align-items-center mb-3">

                                <div class="form-group mb-2 mr-3">
                                    <label for="repo-select" class="mr-2">Repository:</label>
                                    <select class="form-control" id="repo-select">
                                        <?php foreach ($repo as $r):
                                            $repo_name = $r['name'] ?? null;
                                            if (!$repo_name) continue;
                                        ?>
                                        <option value="<?= htmlspecialchars($repo_name) ?>">
                                            <?= htmlspecialchars($repo_name);?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                 <div class="form-group mb-2 mr-3">
                                    <label for="user-select" class="mr-2">Team:</label>
                                    <select class="form-control" id="team-select">
                                        <option value="">Select Team</option>
                                    </select>
                                </div>                                    
                                <div class="form-group mb-2 mr-3">
                                    <label for="user-select" class="mr-2">Developer:</label>
                                    <select class="form-control" id="user-select">
                                        <option value="">Select Developer</option>
                                    </select>
                                </div>
                                <!-- <div class="form-group mb-2 mr-3">
                                    <label for="period-select">Select Period:</label>
                                    <select class="form-control" id="period-select">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div> -->
                                <div class="form-group mb-2 mr-3">
                                    <label for="from-date">From:</label>
                                    <input type="date" class="form-control" id="from-date" />
                                </div>

                                <div class="form-group mb-2 mr-3">
                                    <label for="to-date">To:</label>
                                    <input type="date" class="form-control" id="to-date" />
                                </div>
                                <div class="btn-group mb-2 mr-3" style="margin-top: 1.7em;height:calc(1.5em + .75rem + 7px);">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary period-select" onclick="filterByTab('daily')">Daily</button>
                                    <button type="button" class="btn btn-secondary period-select" onclick="filterByTab('weekly')">Weekly</button>
                                </div>
                            </div>
                                <div class="form-group mb-2 mr-3" style="margin-top: 2rem;">
                                    <button class="btn btn-primary" onclick="exportToExcel()">Export to Excel</button>
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
                            <div style="width: 100%; overflow-x: auto;">
                                <canvas id="commitChart" style="min-width: 1000px; height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Container Fluid -->

        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>&copy; <script>document.write(new Date().getFullYear());</script> <b>InfoBeans</b></span>
                </div>
            </div>
        </footer>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/js/ruang-admin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const teamSelect = document.getElementById('team-select');
    const repoSelect = document.getElementById('repo-select');
    const userSelect = document.getElementById('user-select');
    // const periodSelect = document.getElementById('period-select');
    const fromDateInput = document.getElementById('from-date');
    const toDateInput = document.getElementById('to-date');
    const ctx1 = document.getElementById('commitChart').getContext('2d');
    let allDevelopers = [];
    let chart;

    let periodSelect = 'daily'; // Default value

    function filterByTab(period) {
        selectedPeriod = period;

        // Update button styles
        document.querySelectorAll('.period-select').forEach(button => {
            button.classList.remove('btn-primary');
            button.classList.add('btn-secondary');
        });

        // Highlight the selected button
        const selectedButton = [...document.querySelectorAll('.period-select')].find(btn => 
            btn.textContent.trim().toLowerCase() === period
        );
        if (selectedButton) {
            selectedButton.classList.remove('btn-secondary');
            selectedButton.classList.add('btn-primary');
        }
        periodSelect = selectedPeriod;

        fetchFilteredData();
    }

    // Set default dates: last 30 days
    function setDefaultDates() {
        const today = new Date();
        const priorDate = new Date().setDate(today.getDate() -18);
        fromDateInput.value = new Date(priorDate).toISOString().split('T')[0];
        toDateInput.value = today.toISOString().split('T')[0];
    }

    // Fetch developers for selected repo
    function fetchDevelopers(repo) {
        fetch(`api_contributors.php?repo=${encodeURIComponent(repo)}`)
            .then(res => res.json())
            .then(users => {
                userSelect.innerHTML = '<option value="">All Developers</option>';
                users.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.login;
                    opt.textContent = user.name;
                    userSelect.appendChild(opt);
                });
                fetchFilteredData();
            });
    }

    // Validate date inputs (from <= to)
    function validateDates() {
        const from = fromDateInput.value;
        const to = toDateInput.value;
        if (!from || !to) return false;
        return new Date(from) <= new Date(to);
    }

    // Fetch filtered commits data
    function fetchFilteredData() {
        if (!validateDates()) {
            alert("Invalid date range. 'From' date must be earlier than or equal to 'To' date.");
            return;
        }

        const repo = repoSelect.value;
        const user = userSelect.value;
        const period = periodSelect;
        const from = fromDateInput.value;
        const to = toDateInput.value;

        let url = `api_commit.php?repo=${encodeURIComponent(repo)}&period=${period}&from=${from}&to=${to}`;
        if (user) {
            url += `&user=${encodeURIComponent(user)}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                updateChart(data.labels, data.counts, user ? `Commits by ${user}` : 'Team Commits', data.authors);
            });
    }

    // Update chart with new data
    function updateChart(labels, counts, label, authors = []) {
        if (chart) chart.destroy();

        const dataPoints = labels.map((date, i) => ({
            x: date,
            y: counts[i],
            authors: authors[i] || []
        }));

        chart = new Chart(ctx1, {
            type: 'line',
            data: {
                datasets: [{
                    label: label,
                    data: dataPoints,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const point = context.raw;
                                const authorList = point.authors?.join(', ') || 'No authors';
                                return `${point.y} commits by: ${authorList}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        labels: labels,
                        title: {
                            display: true,
                            text: 'Date of Commit'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: false
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Commits'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Event Listeners
    repoSelect.addEventListener('change', () => {
        fetchDevelopers(repoSelect.value);
    });
    userSelect.addEventListener('change', fetchFilteredData);
    // periodSelect.addEventListener('change', fetchFilteredData);
    fromDateInput.addEventListener('change', fetchFilteredData);
    toDateInput.addEventListener('change', fetchFilteredData);

    function fetchTeams() {
        fetch('api_teams.php')
            .then(res => res.json())
            .then(teams => {
                teamSelect.innerHTML = '<option value="all">All Teams</option>';
                teams.forEach(team => {
                    const option = document.createElement('option');
                    option.value = team.slug;
                    option.textContent = team.name;
                    teamSelect.appendChild(option);
                });
            });
    }
    function fetchTeamMembers(teamSlug) {
        if (teamSlug === 'all') {
            updateDeveloperDropdown(allDevelopers);
            return;
        }

        fetch(`api_team_members.php?team_slug=${encodeURIComponent(teamSlug)}`)
            .then(res => res.json())
            .then(members => {
                const memberLogins = members.map(m => m.login.toLowerCase());
                console.log(memberLogins);
                // const filteredDevs = memberLogins.filter(dev => memberLogins.includes(dev.login.toLowerCase()));
                updateDeveloperDropdown(memberLogins);
            });
    }
    function updateDeveloperDropdown(developers) {
        console.log(developers);
        userSelect.innerHTML = '<option value="">Select Developer</option>';
        developers.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user;
                    opt.textContent = user;
                    userSelect.appendChild(opt);
                });
    }
    teamSelect.addEventListener('change', () => {
        const selectedTeam = teamSelect.value;
        fetchTeamMembers(selectedTeam);
    });
    // Init
    window.addEventListener('DOMContentLoaded', () => {
        setDefaultDates();
        if (repoSelect.options.length > 0) {
            fetchDevelopers(repoSelect.value);
        }
    });
    // Initial load
    fetchTeams();

    function exportToExcel() {
        const repo = repoSelect.value;
        const user = userSelect.value;
        const team = teamSelect.value;
        const period = periodSelect;
        const from = fromDateInput.value;
        const to = toDateInput.value;

        if (!validateDates()) {
            alert("Invalid date range.");
            return;
        }

        let url = `export_commit_excel.php?repo=${encodeURIComponent(repo)}&period=${period}&from=${from}&to=${to}`;
        if (user) url += `&user=${encodeURIComponent(user)}`;
        if (team) url += `&team=${encodeURIComponent(team)}`;

        window.open(url, '_blank');
    }

</script>
</body>
</html>
