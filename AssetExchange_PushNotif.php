<?php
session_start();

$servername = "localhost"; 
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from users and roles tables
$sql_users = "SELECT * FROM users";
$sql_roles = "SELECT * FROM roles";
$result_users = $conn->query($sql_users);

$designer_notifications = [];
$client_notifications = [];

// Fetch data from the push_notifications table
$sql_pushed_notifications = "SELECT * FROM push_notifications";
$result_pushed_notifications = $conn->query($sql_pushed_notifications);

$pushed_notifications = [];
if ($result_pushed_notifications->num_rows > 0) {
    while ($row_pushed = $result_pushed_notifications->fetch_assoc()) {
        $pushed_notifications[] = $row_pushed['message'];
    }
} else {
    $pushed_notifications = [];
}

// Function to send email
function sendEmail($to, $subject, $message) {
    // Fetch the HTML content from the custom_email.php page
    $customEmailContent = file_get_contents("https://beige-snake-192211.hostingersite.com/custom_email.php");

    // Append the fetched content to the original message
    $fullMessage = $message . "<br><br>" . $customEmailContent;

    // Set the headers for HTML email
    $headers = "From: assetexchange@gmail.com\r\n"; // Replace with your sender email
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // Send the email with the combined message and HTML content
    return mail($to, $subject, $fullMessage, $headers);
}


if ($result_users->num_rows > 0) {
    while ($row_user = $result_users->fetch_assoc()) {
        $role_id = $row_user['role_id'];
        $user_email = $row_user['email'];

        // Fetch the role name for each user
        $sql_role = "SELECT role_name FROM roles WHERE role_id = $role_id";
        $result_role = $conn->query($sql_role);
        $role = $result_role->fetch_assoc()['role_name'];

        // Customize notifications based on the role
        if ($role === "Designer") {
            $designer_notifications[] = [
                'message' => "Hello " . $row_user['full_name'] . ", check out the new design tasks and tools.",
                'email' => $user_email
            ];
        } elseif ($role === "Client") {
            $client_notifications[] = [
                'message' => "Hello " . $row_user['full_name'] . ", your project has new updates. Please review.",
                'email' => $user_email
            ];
        }
    }
} else {
    echo "No users found.";
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
    <title>Asset Exchange Admin Website</title>
</head>
<style>
    /* Disable hover effect on Bootstrap cards */
    .card {
        transition: none; /* Disable any transition effect */
    }
    .card:hover {
        transform: none; /* Disable hover transformation */
    }
    
    /* Notification list styling */
    .notification-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: .3s;
    }

    .notification-item:hover {
        transform: scale(1.01);
    }

    .notification-item img {
        width: 20px;
        height: 20px;
        cursor: pointer; /* Show pointer cursor for clickability */
    }

    /* Style for sent notifications */
    .sent-notification {
        background-color: #d3d3d3; /* Gray background */
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
                <a class="nav-link" href="AssetExchange_PushNotif.php" style="color: #313131; font-size: 20px; font-weight: 600; border-bottom: 3px #F76B30 solid;">Push Notification</a>
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


            <!-- Sidebar (Offcanvas for smaller screens) -->
            <div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
                <div class="offcanvas-header" style="margin-top: 50px">
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
                            <a class="nav-link" href="AssetExchange_UserControl.php">User Control</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            
              <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="height: 100vh;">
                <h2 style="color: #313131; font-family: 'Inter'; font-size: 55.03px; font-weight: 700; border: none; outline: none;">Push Notifications</h2>

                <!-- Notification Grid -->
                <div class="container mt-4">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <!-- Designer Notifications -->
                        <div class="col">
                            <div class="card h-100" style="background-color: rgba(100, 229, 114, 1); outline: #313131 1px solid;">
                                <div class="card-body">
                                    <h5 class="card-title">Designer Notifications</h5>
                                    <?php if (count($designer_notifications) > 0): ?>
                                        <ul class="list-group">
                                            <?php foreach ($designer_notifications as $notification) : ?>
                                                <li class="list-group-item notification-item" id="notification-<?php echo $notification['email']; ?>">
                                                    <?php echo $notification['message']; ?>
                                                    <img src="./assets/push_arrow.png" alt="Send Email" onclick="sendEmail('<?php echo $notification['email']; ?>', '<?php echo addslashes($notification['message']); ?>', this)" />
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else : ?>
                                        <p>No designer notifications available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Client Notifications -->
                        <div class="col">
                            <div class="card h-100" style="background-color: rgba(255, 242, 99, 1); outline: #313131 1px solid;">
                                <div class="card-body">
                                    <h5 class="card-title">Client Notifications</h5>
                                    <?php if (count($client_notifications) > 0): ?>
                                        <ul class="list-group">
                                            <?php foreach ($client_notifications as $notification) : ?>
                                                <li class="list-group-item notification-item" id="notification-<?php echo $notification['email']; ?>">
                                                    <?php echo $notification['message']; ?>
                                                    <img src="./assets/push_arrow.png" alt="Send Email" onclick="sendEmail('<?php echo $notification['email']; ?>', '<?php echo addslashes($notification['message']); ?>', this)" />
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else : ?>
                                        <p>No client notifications available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pushed Notifications Card -->
                    <div class="row mt-4">
                        <div class="col">
                            <div class="card h-100" style="background-color: rgba(255, 150, 85, 1); outline: #313131 1px solid;">
                                <div class="card-body">
                                    <h5 class="card-title">Pushed Notifications</h5>
                                    <?php if (count($pushed_notifications) > 0): ?>
                                        <ul class="list-group">
                                            <?php foreach ($pushed_notifications as $pushed_message) : ?>
                                                <li class="list-group-item">
                                                    <?php echo $pushed_message; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else : ?>
                                        <p>No pushed notifications available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
    // Function to send email via AJAX
    function sendEmail(email, message, imgElement) {
        if (confirm("Do you want to send an email notification?")) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "send_email.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText); // Display the response from PHP
                    
                    // Insert into push_notifications table after email is sent
                    insertNotification(message, email);
                    
                    // Mark notification as sent
                    markNotificationAsSent(email, imgElement);
                }
            };
            xhr.send("email=" + encodeURIComponent(email) + "&message=" + encodeURIComponent(message));
        }
    }

    // Insert notification into the database
    function insertNotification(message, email) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "insert_notification.php", true); // Your PHP file to handle DB insert
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("message=" + encodeURIComponent(message) + "&email=" + encodeURIComponent(email));
        
        // Store notification email in localStorage
        localStorage.setItem(email, 'sent'); // Use email as key to track sent notifications
    }

    // Function to mark notification as sent
    function markNotificationAsSent(email, imgElement) {
        // Add sent class to the notification item
        var notificationItem = document.getElementById("notification-" + email);
        if (notificationItem) {
            notificationItem.classList.add('sent-notification'); // Add class to change background
        }
    }

    // Function to apply sent notification style on page load
    function applySentNotifications() {
        for (var i = 0; i < localStorage.length; i++) {
            var key = localStorage.key(i);
            var value = localStorage.getItem(key);
            if (value === 'sent') {
                var notificationItem = document.getElementById("notification-" + key);
                if (notificationItem) {
                    notificationItem.classList.add('sent-notification'); // Add class to change background
                }
            }
        }
    }

    // Run the function on page load
    window.onload = function() {
        applySentNotifications();
    };
    
    
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
                <!-- Bootstrap JS Bundle with Popper -->
                <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
            </main>
        </div>
    </div>
</body>

</html>