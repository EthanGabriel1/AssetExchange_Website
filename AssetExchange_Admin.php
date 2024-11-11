<?php
session_start();

$servername = "localhost"; // Update as necessary
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch asset count
$sql = "SELECT * FROM assets;";
$result = $conn->query($sql);
$totalAssets = $result->num_rows;

// Fetch user count
$sqlUsers = "SELECT * FROM users;";
$resultUsers = $conn->query($sqlUsers);
$totalUsers = $resultUsers->num_rows;

// Fetch data for pie chart (user roles)
$roleData = [];
$sqlRoles = "SELECT users.role_id, roles.role_name, COUNT(users.role_id) AS count 
             FROM users 
             INNER JOIN roles ON users.role_id = roles.role_id 
             GROUP BY users.role_id;";
$resultRoles = $conn->query($sqlRoles);

if ($resultRoles->num_rows > 0) {
    while ($row = $resultRoles->fetch_assoc()) {
        $roleData[] = $row;
    }
} else {
    $roleData = [];
}

// Fetch counts for bar chart
$totalFiles = $conn->query("SELECT COUNT(*) as count FROM files;")->fetch_assoc()['count'];
$totalProjects = $conn->query("SELECT COUNT(*) as count FROM projects;")->fetch_assoc()['count'];
$totalTasks = $conn->query("SELECT COUNT(*) as count FROM tasks;")->fetch_assoc()['count'];

// Fetch notification count
$sqlNotifications = "SELECT * FROM `push_notifications`;";
$resultNotifications = $conn->query($sqlNotifications);
$totalNotifications = $resultNotifications->num_rows;

// Fetch user data for the table (updated query to fetch role_name)
$sqlUserData = "SELECT full_name, email, date_joined, roles.role_name FROM users INNER JOIN roles ON users.role_id = roles.role_id;";
$resultUserData = $conn->query($sqlUserData);
$userData = [];
if ($resultUserData->num_rows > 0) {
    while ($row = $resultUserData->fetch_assoc()) {
        $userData[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/AssetExchange_Admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
    <title>Asset Exchange Admin Website</title>

    <style>
        /* Hover effect for cards */
        .card:hover {
            transform: scale(1.02);
            transition: transform .5s;
        }

        /* Loading screen styling */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(36, 47, 63, 0.55); /* 10% opacity */
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #loading-animation {
            width: 300px;
            height: 300px;
        }
    </style>
</head>

<body>

    <!-- Loading screen with Lottie animation -->
    <div id="loading-screen">
        <div id="loading-animation"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="./AssetExchange_Admin.php">
                <img src="./assets/logo.png" alt="Logo" class="logo-img">
                <span class="txt_AssetEx">AssetEx.</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="d-flex align-items-center ms-auto">
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-5 pt-4">
        <div class="row">
            <!-- Sidebar (Visible on larger screens) -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-none position-fixed d-md-block bg-light sidebar" style="height: 140vh;">
                <div class="d-flex justify-content-center align-items-center mt-1">
                    <img class="d-flex justify-content-center icon_profile" src="./assets/admin_profile.png" alt="Profile" style= "width: 70px; height: 70px; margin-left: -10px;">
                </div>
                <div class="position-sticky pt-1">
                     <label class="d-flex justify-content-center" style="color: #8C8C8C; font-size: 15px; font-family: Inter; font-weight: 500; margin-left: -15px;">Name:</label>
                    <span class="txt_Username d-flex justify-content-center" style = "color: #313131; font-family: Inter; font-size: 25px; font-weight: 700;"><?php echo $_SESSION['full_name']; ?></span> <!-- Display full name -->
                    <span class="txt_Role d-flex justify-content-center mb-5" style = "color: #313131; font-family: Inter; font-size: 18px; font-weight: 700; margin-left: -18px;">(Admin)</span>
                    <h5 class="px-3" style="color: #8C8C8C; font-size: 18px; margin-top: -20px;">General</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="AssetExchange_Admin.php" style="color: #313131; font-size: 20px; font-weight: 600; border-bottom: 3px #F76B30 solid;">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_PushNotif.php" style="color: #313131; font-size: 20px; font-weight: 600;">Push Notification</a>
                        </li>
                        <hr>
                        <h5 class="px-3" style="color: #8C8C8C; font-size: 18px; margin-top: 10px;">Access</h5>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_UserControl.php" style="color: #313131; font-size: 20px; font-weight: 600;">User Control</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_Assets.php" style="color: #313131; font-size: 20px; font-weight: 600;">Assets</a>
                        </li>
                        <hr>
                        <!-- Sign-out Link -->
                        <li class="nav-item mt-auto">
                            <a class="nav-link" href="logout.php" style="color: #313131; font-size: 20px; font-weight: 600;">Sign Out</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <h2 style="color: #313131; font-family: 'Inter'; font-size: 55.03px; font-weight: 700; border: none; outline: none;">Dashboard</h2>

                <!-- Dashboard Cards -->
                <div class="row">
                    <div class="col-md-4 mb-4 mt-3">
                        <a href="./AssetExchange_Assets.php" class="text-decoration-none">
                            <div class="card hover-card" style="border-radius:20px; background-color: #A159F6; color: white;">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title" style="font-size: 30px">Total Assets</h5>
                                        <p class="card-text" style="font-size: 30px"><?php echo $totalAssets; ?></p>
                                    </div>
                                    <i class="fas fa-boxes fa-3x"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-4 mt-3">
                        <a href="./AssetExchange_UserControl.php" class="text-decoration-none">
                            <div class="card" style="border-radius:20px; background-color: #02A9EB; color: white;">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title" style="font-size: 30px">Total Users</h5>
                                        <p class="card-text" style="font-size: 30px"><?php echo $totalUsers; ?></p>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-4 mt-3">
                        <a href="./AssetExchange_PushNotif.php" class="text-decoration-none">
                            <div class="card" style="border-radius:20px; background-color: #F76B30; color: white;">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title" style="font-size: 30px">Pending Notifications</h5>
                                        <p class="card-text" style="font-size: 30px"><?php echo $totalNotifications; ?></p>
                                    </div>
                                    <i class="fas fa-bell fa-3x"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Pie Chart for User Roles and Bar Chart for Asset, File, Project, and Task Counts -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title" style="font-size: 30px; color: #181818;">Counts of Assets, Files, Projects, and Tasks</h5>
                                <canvas id="countChart" width="900" height="425" style="display: block; box-sizing: border-box; height: 225px; width: 450px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title" style="font-size: 30px; color: #181818;">User Roles Distribution</h5>
                                <canvas id="roleChart" width="900" height="900" style="display: block; box-sizing: border-box; height: 450px; width: 450px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Data Table -->
                <div class="table-responsive mt-4">
                    <table class="table table-striped table-bordered">
                        <thead class="table">
                            <tr>
                                <th onclick="sortTable(0)">Full Name <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(1)">Email <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(2)">Date Joined <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(3)">Role <i class="fas fa-sort"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userData as $user): ?>
                                <tr>
                                    <td><?php echo $user['full_name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['date_joined']; ?></td>
                                    <td><?php echo $user['role_name']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PHP data for roles and counts
        const roleLabels = <?php echo json_encode(array_column($roleData, 'role_name')); ?>;
        const roleCounts = <?php echo json_encode(array_column($roleData, 'count')); ?>;

        // Chart.js pie chart configuration
        const ctx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: roleLabels,
                datasets: [{
                    label: 'User Roles',
                    data: roleCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 242, 99, 1)',
                        'rgba(100, 229, 114, 1)',
                        'rgba(47, 126, 216, 1)',
                        'rgba(13, 35, 58, 1)',
                        'rgba(255, 150, 85, 1)'
                    ],
                    borderColor: [
                        'rgba(251, 251, 251, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'User Roles Distribution'
                    }
                }
            }
        });

        // Bar chart configuration for counts
        const counts = [<?php echo $totalAssets; ?>, <?php echo $totalFiles; ?>, <?php echo $totalProjects; ?>, <?php echo $totalTasks; ?>];
        const countLabels = ['Assets', 'Files', 'Projects', 'Tasks'];

        const ctx2 = document.getElementById('countChart').getContext('2d');
        const countChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: countLabels,
                datasets: [{
                    label: 'Counts',
                    data: counts,
                    backgroundColor: [
                        'rgba(36, 203, 229, 1)',
                        'rgba(80, 180, 50, 1)',
                        'rgba(237, 86, 27, 1)',
                        'rgba(221, 223, 0, 1)',
                    ],
                    borderColor: [
                        'rgba(36, 203, 229, 1)',
                        'rgba(80, 180, 50, 1)',
                        'rgba(237, 86, 27, 1)',
                        'rgba(221, 223, 0, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: 'Counts of Assets, Files, Projects, and Tasks'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        let sortDirection = [true, true, true, true]; // Array to track sort direction for each column

        function sortTable(columnIndex) {
            const table = document.querySelector('.table tbody');
            const rows = Array.from(table.rows);
            const sortedRows = rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();

                // Toggle sort direction
                if (sortDirection[columnIndex]) {
                    return aText.localeCompare(bText);
                } else {
                    return bText.localeCompare(aText);
                }
            });

            // Update sort direction for the column
            sortDirection[columnIndex] = !sortDirection[columnIndex];

            // Remove existing rows
            while (table.firstChild) {
                table.removeChild(table.firstChild);
            }
            // Append sorted rows
            sortedRows.forEach(row => table.appendChild(row));

            // Update sort icons
            updateSortIcons(columnIndex);
        }

        function updateSortIcons(sortedColumnIndex) {
            const headers = document.querySelectorAll('.table th i');
            headers.forEach((icon, index) => {
                icon.classList.remove('fa-sort-up', 'fa-sort-down');
                if (index === sortedColumnIndex) {
                    icon.classList.add(sortDirection[sortedColumnIndex] ? 'fa-sort-up' : 'fa-sort-down');
                } else {
                    icon.classList.add('fa-sort');
                }
            });
        }

        // Initialize the Lottie animation
        var animation = lottie.loadAnimation({
            container: document.getElementById('loading-animation'), // the DOM element to render the animation
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: './assets/loading_animation.json' // specify the correct path to your Lottie animation JSON file
        });

        // Hide the loading screen once the page has loaded
        window.addEventListener('load', function () {
    setTimeout(function () {
        document.getElementById('loading-screen').style.display = 'none';
    }, 2000); // Add a 2-second delay before hiding the loading screen
});
        
        
        
        
    </script>

</body>

</html>
