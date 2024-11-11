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





$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/AssetExchange_Admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Asset Exchange Admin Website</title>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="logo.png" alt="Logo" class="logo-img">
            <span class="txt_AssetEx">AssetEx.</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <input class="search-bar" type="search" placeholder="Try searching 'demographic'" aria-label="Search">
            <div class="d-flex align-items-center ms-auto">
                <img class="ic_profile" src="assets/profile-icon.png" alt="Profile">
                <span class="txt_Username"><?php echo $_SESSION['full_name']; ?></span> <!-- Display full name -->
                <img class="ic_settings" src="assets/settings-icon.png" alt="Settings">
            </div>
        </div>
    </div>
</nav>

    <div class="container-fluid mt-5 pt-4">
        <div class="row">
            <!-- Sidebar (Visible on larger screens) -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar" style= "height: 120vh; ">
                <div class="position-sticky pt-3">
                    <h1 class="sidebar-title">Welcome to AssetEx Admin System</h1>
                    <h5 class="px-3" style= "color: #8C8C8C; font-size: 18px; margin-top: 20px;">General</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="asset_exchange_admin.php" style= "color: #313131; font-size: 20px; font-weight: 600;">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetEx._PushNotif.html" style= "color: #313131; font-size: 20px; font-weight: 600;">Push Notification</a>
                        </li>
                        <hr>
                        <h5 class="px-3" style= "color: #8C8C8C; font-size: 18px; margin-top: 10px;">Access</h5>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetEx._UserControl.html" style= "color: #313131; font-size: 20px; font-weight: 600;">User Control</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" style= "color: #313131; font-size: 20px; font-weight: 600;">Reports</a>
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
                            <a class="nav-link" href="AssetEx._PushNotif.html">Push Notification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetEx._UserControl.html">User Control</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Reports</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <h2>TEMPLATE</h2>

                

            </main>
        </div>
    </div>

   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>