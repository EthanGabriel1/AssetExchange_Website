<?php
session_start();

// Database connection
$servername = "localhost"; // Update as necessary
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from 'files' table
$sql = "SELECT file_id, file_name, date_uploaded, file_ext FROM files;";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/AssetExchange_Admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet"> <!-- DataTables Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
    <title>Asset Exchange Admin Website</title>
    
    <style>

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
                <a class="nav-link active" href="AssetExchange_Admin.php" style="color: #313131; font-size: 20px; font-weight: 600;">Dashboard</a>
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
                <a class="nav-link" href="AssetExchange_Assets.php" style="color: #313131; font-size: 20px; font-weight: 600; border-bottom: 3px #F76B30 solid;">Assets</a>
            </li>
            <hr>
            <!-- Sign-out Link -->
            <li class="nav-item mt-auto">
                <a class="nav-link" href="logout.php" style="color: #313131; font-size: 20px; font-weight: 600;">Sign Out</a>
            </li>
        </ul>
    </div>
</nav>

            <!-- Sidebar (Offcanvas for smaller screens) -->
            <div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
                <div class="offcanvas-header">
                    <h1 class="offcanvas-title sidebar-title" id="offcanvasSidebarLabel">Welcome to AssetEx Admin System</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="asset_exchange_admin.php">Dashboard</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_PushNotif.php">Push Notification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetEx._UserControl.html">User Control</a>
                        </li>
                    </ul>
                </div>
            </div>

             <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="height: 100vh;">
                <h2 style="color: #313131; font-family: 'Inter'; font-size: 55.03px; font-weight: 700; border: none; outline: none;">Assets</h2>

                <!-- ASSETS TABLE -->
                <table id="filesTable" class="table table-striped table-bordered">
                    <thead class="table-dark" >
                        <tr>
                            <th>File Name</th>
                            <th>Date Uploaded</th>
                            <th>File Extension</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";

                                // Set the icon based on file extension
                                $file_ext = strtolower($row['file_ext']);
                                $icon_path = './assets/ic_' . $file_ext . '.png'; 

                                // File link with icon
                                $file_link = './filegator/repository/user/' . htmlspecialchars($row['file_id']) . '.' . htmlspecialchars($row['file_ext']);
                                echo "<td style='color: red;'><a href='$file_link' target='_blank'>
                                        <img src='$icon_path' alt='$file_ext icon' style='width: 20px; height: 20px; margin-right: 5px;'>" . htmlspecialchars($row['file_name']) . "</a></td>";
                                echo "<td>" . htmlspecialchars($row['date_uploaded']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['file_ext']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No files found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#filesTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true, // Enable sorting
                "order": [], // Default no sorting, set as needed
                "lengthChange": true,
                "pageLength": 10,
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2] } // Make all columns sortable
                ]
            });
        });
        
        
        
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