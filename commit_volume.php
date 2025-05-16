<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>
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
            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <img class="img-profile rounded-circle" src="img/boy.png" style="max-width: 60px">
                    <span class="ml-2 d-none d-lg-inline text-white small">User Profile</span>
                </a>
                </li>
            </ul>
            </nav>
            <!-- Topbar -->

            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Commit Volume</h1>
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
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
                
                // Fetch Contributors
                $contribs = fetchFromGitHub($repo_owner, $repo, $github_token);
            ?>
            <div class="row mb-3">
                <div class="col-lg-12">
                <div class="card p-3">
                    <div class="d-flex flex-wrap align-items-center mb-3">
                    <!-- Developer Dropdown -->
                    <div class="form-group mb-2 mr-3">
                    <label for="developer" class="mr-2">Developer:</label>
                        
                        <select class="form-control" id="user-select">
                            <option value="">Select Developer</option>
                            <?php foreach ($contribs as $user): 
                                $username = $user['login'] ?? null;
                                if (!$username) continue;

                                $userUrl = "https://api.github.com/users/$username";

                                $ch = curl_init($userUrl);
                                curl_setopt_array($ch, [
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_SSL_VERIFYPEER => false,
                                    CURLOPT_USERAGENT => 'PHP App',
                                    CURLOPT_HTTPHEADER => [
                                        "Authorization: token $token"
                                    ]
                                ]);

                                $userResponse = curl_exec($ch);
                                curl_close($ch);
                                $userData = json_decode($userResponse, true);
                                
                                $results= [
                                    'login' => $username,
                                    'name' => $userData['name'] ?? $username
                                ];
                                ?>
                            <option value="<?= htmlspecialchars($results['login']) ?>"><?= htmlspecialchars($results['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        <div class="form-group mb-2 mr-3">
                        <!-- <label for="developer" class="mr-2">Developer:</label> -->
                        <label for="period-select">Select Period:</label>
                        <select class="form-control" id="period-select">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
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
                        <canvas id="commitChart" width="400" height="200" style="max-width: 600px; height: 200px;"></canvas>
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
    <script src="assets/js/demo/chart-area-demo.js"></script>  
    <script>
        const ctx1 = document.getElementById('commitChart').getContext('2d');
        let chart;

        // Event listeners
        document.getElementById('user-select').addEventListener('change', fetchFilteredData);
        document.getElementById('period-select').addEventListener('change', fetchFilteredData);

        function fetchFilteredData() {
        const user = document.getElementById('user-select').value;
        const period = document.getElementById('period-select').value;

        let url = `api_commit.php?period=${period}`;
        if (user) {
            url += `&user=${user}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                console.log(data);
                // updateChart(data.labels, data.counts, user ? `Commits by ${user}` : 'Team Commits');
                updateChart(data.labels, data.counts, user ? `Commits by ${user}` : 'Team Commits', data.authors);
            });
        }

        function updateChart(labels, counts, label, authors = []) {
            if (chart) chart.destroy();

            const dataPoints = labels.map((date, i) => ({
                x: date,      // Dates will go on the X-axis
                y: counts[i],  // Commit counts will go on the Y-axis
                authors: authors[i] || []
            }));

            chart = new Chart(ctx1, {
                type: 'line',
                data: {
                    datasets: [{
                        label: label,
                        data: dataPoints,
                        showLine: true,
                        borderColor: 'blue',
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    padding: {
                        top: 100,
                        bottom: 10,
                        left: 10,
                        right: 10
                    },
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
                                text: 'Date'
                            },
                            ticks: {
                                padding: 10 // extra spacing from border
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

        // Load initial team daily data
        fetchFilteredData();
    </script>
</body>

</html>