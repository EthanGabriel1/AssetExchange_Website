<?php 
session_start();

// Connection setup
$servername = "localhost";
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle activation and deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'activate') {
        $update_sql = "UPDATE user_control SET user_activated = 1 WHERE user_id = ?";
    } elseif ($action === 'deactivate') {
        $update_sql = "UPDATE user_control SET user_activated = 2 WHERE user_id = ?";
    }

    if (isset($update_sql)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}



// Set default number of results per page
$results_per_page = isset($_GET['results_per_page']) ? (int)$_GET['results_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;


// Get search query if available
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';



// Construct the SQL query for filtering by date
$date_condition = '';
if ($date_filter) {
    $date_condition = "AND date_joined >= '$date_filter'";
}


// Fetch total number of users, applying search query and date condition if present
$total_sql = "SELECT COUNT(u.user_id) AS total 
              FROM users u 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE (u.full_name LIKE '%$search_query%' OR u.email LIKE '%$search_query%') 
              $date_condition";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];



// Sort users based on the selected option
$order_by = isset($_GET['sort']) ? $_GET['sort'] : 'full_name';
$order_direction = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT u.user_id, u.full_name, u.email, r.role_name, uc.user_activated, u.date_joined 
        FROM users u 
        JOIN user_control uc ON u.user_id = uc.user_id
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.full_name LIKE '%$search_query%' OR u.email LIKE '%$search_query%' 
        $date_condition
        ORDER BY $order_by $order_direction
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);


// Fetch roles for the dropdown filter
$roles_sql = "SELECT * FROM roles;";
$roles_result = $conn->query($roles_sql);
$roles = [];
if ($roles_result->num_rows > 0) {
    while($role = $roles_result->fetch_assoc()) {
        $roles[] = $role['role_name'];
    }
}

// Handle role filter
$selected_role = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';
$role_condition = '';
if ($selected_role) {
    $role_condition = "AND r.role_name = '$selected_role'";
}


// Handle status filter
$selected_status = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$status_condition = '';
if ($selected_status === 'ACTIVATED') {
    $status_condition = "AND uc.user_activated = 1";
} elseif ($selected_status === 'DEACTIVATED') {
    $status_condition = "AND uc.user_activated = 2";
}


// Update your SQL to include role and status filter
$sql = "SELECT u.user_id, u.full_name, u.email, r.role_name, uc.user_activated, u.date_joined 
        FROM users u 
        JOIN user_control uc ON u.user_id = uc.user_id
        JOIN roles r ON u.role_id = r.role_id
        WHERE (u.full_name LIKE '%$search_query%' OR u.email LIKE '%$search_query%') 
        $date_condition 
        $role_condition
        $status_condition
        ORDER BY $order_by $order_direction
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css"> <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
                <a class="nav-link" href="AssetExchange_UserControl.php" style="color: #313131; font-size: 20px; font-weight: 600; border-bottom: 3px #F76B30 solid;">User Control</a>
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
            <!-- Sidebar (Offcanvas for smaller screens) -->
            <div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
                <div class="offcanvas-header" style= "margin-top: 50px">
                    <h1 class="offcanvas-title sidebar-title" id="offcanvasSidebarLabel">Welcome to AssetEx Admin System</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="AssetExchange_Admin.php">Dashboard</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_PushNotif.php">Push Notification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="AssetExchange_UserControl.php">User Control</a>
                        </li>
                    </ul>
                </div>
            </div>


           <!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="height: 100vh;">
    <div class="d-flex justify-content-between align-items-center">
        <h2 style="color: #313131; font-family: 'Inter'; font-size: 55.03px; font-weight: 700; border: none; outline: none;">User Control</h2>

        <!-- Search Form -->
        <div class="mb-1 d-flex justify-content-end">
            <label for="search" class="me-2" style="align-self: center; font-weight: 600; font-size: 20px">Search:</label>
            <form method="GET" class="form-inline">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search_query); ?>">
            </form>
        </div>
    </div>



<div class="d-flex justify-content-between mb-3" style="margin-top: 20px">
    <!-- Show Entries Form -->
    <form method="GET" class="form-inline">
        <label for="results_per_page" style="font-weight: 600; font-size: 18px">Show </label>
        <select name="results_per_page" id="results_per_page" class="form-select form-select-sm w-auto mx-2" onchange="this.form.submit()">
            <option value="5" <?php if($results_per_page == 5) echo 'selected'; ?>>5</option>
            <option value="10" <?php if($results_per_page == 10) echo 'selected'; ?>>10</option>
            <option value="25" <?php if($results_per_page == 25) echo 'selected'; ?>>25</option>
            <option value="50" <?php if($results_per_page == 50) echo 'selected'; ?>>50</option>
        </select>
    </form>
</div>

    <!-- User Table -->
<table class="table table-striped">
    <thead>
        <tr>
            
            <th>
                Full Name
                <a href="?sort=full_name&order=asc" class="text-dark"><i class="bi bi-arrow-up"></i></a>
                <a href="?sort=full_name&order=desc" class="text-dark"><i class="bi bi-arrow-down"></i></a>
            </th>
            <th>
                Email
                <a href="?sort=email&order=asc" class="text-dark"><i class="bi bi-arrow-up"></i></a>
                <a href="?sort=email&order=desc" class="text-dark"><i class="bi bi-arrow-down"></i></a>
            </th>
            
            <th>
                Role
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style= "color: #000000;">
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    <form method="GET" class="px-3">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                        <input type="hidden" name="results_per_page" value="<?php echo $results_per_page; ?>">
                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                        <label for="role_filter" class="visually-hidden">Select Role</label>
                        <select name="role_filter" id="role_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role; ?>" <?php if ($role == $selected_role) echo 'selected'; ?>>
                                    <?php echo $role; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </th>
            
            <th>
                Status
            </th>
            <th>
                Date Joined
                <a href="?sort=date_joined&order=asc" class="text-dark"><i class="bi bi-arrow-up"></i></a>
                <a href="?sort=date_joined&order=desc" class="text-dark"><i class="bi bi-arrow-down"></i></a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role_name']); ?></td>
                                    <td>
                                        <?php if ($row['user_activated'] == 1): ?>
                                            <span class="text-success">ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="text-danger">DEACTIVATED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['date_joined']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <?php if ($row['user_activated'] == 1): ?>
                                                <button type="submit" name="action" value="deactivate" class="btn btn-danger btn-sm">Deactivate</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="activate" class="btn btn-success btn-sm">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No results found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <nav aria-label="User Table Pagination">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= ceil($total_users / $results_per_page); $i++): ?>
                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&results_per_page=<?php echo $results_per_page; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </main>
        </div>
    </div>
    
    <script> 
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>