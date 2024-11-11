<?php 
require_once 'phpmailer_demo.php';
require_once 'config_assetexchange.php';

// Start a session to store the user session
session_start();

$error_message = ''; // Variable to store error message

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && $_POST['action'] == 'login') {
            // Login logic
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                $error_message = 'Error: Missing email or password.';
            } else {
                $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
                $password = $_POST['password'];

                try {
                    // Check if user has role_id = 1 and verify password
                    $stmt = $conn->prepare("SELECT pw_hash, role_id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['pw_hash'])) {
                        if ($user['role_id'] == 1) {
                            // Fetch the full_name of the logged-in user
                            $stmt = $conn->prepare("SELECT full_name FROM users WHERE email = ?");
                            $stmt->execute([$email]);
                            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            // Set session variables
                            $_SESSION['user_email'] = $email; // Store user email in session
                            $_SESSION['full_name'] = $userData['full_name']; // Store full name in session
                            
                            header("Location: AssetExchange_Admin.php"); // Redirect to dashboard
                            exit; // Make sure to exit after the redirect
                        } else {
                            $error_message = 'Error: Access denied. Admin role required.';
                        }
                    } else {
                        $error_message = 'Error: Invalid email or password.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error: User login failed due to a database error. ' . $e->getMessage();
                }
            }
        }
    }
} catch (PDOException $e) {
    $error_message = 'Error: Connection failed: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400&display=swap" rel="stylesheet">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url("./assets/LOGIN_BACKGROUND.png");
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .login-container {
            background-color: transparent;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 450px;
            margin-left: 140px; /* Move container away from left edge */
            animation: slideIn 1s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .login-container img {
            position: relative;
            top: 20px;
            left: -145px;
            width: 30px;
            height: 30px;
        }

        .asset-ex-txt {
            position: relative;
            top: 20px;
            left: -150px;
            font-size: 15px;
            font-weight: 600;
            color: #DBDBDB;
            font-family: 'Satoshi', sans-serif;
        }

        h2 {
            font-size: 40px;
            color: #E3E3E3;
            font-family: "Roboto", sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="email"], input[type="password"] {
            margin-bottom: 15px;
        }

        input[type="submit"] {
            background-color: #101213;
            height: 45px;
            color: white;
        }
        
          input[type="submit"]:hover {
            background-color: #101213; /* Remove hover effect */
            color: white;
        }

        .error {
            color: red;
            text-align: center;
        }
        
         /* Toast positioning */
        .toast-container {
            position: fixed;
            top: 20px; /* Adjust as needed for spacing */
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050; /* Ensure it stays on top of other elements */
        }
        
    </style>
</head>
<body>
    <div class="login-container text-center p-4">
        <img src="./assets/Image_20240530_002818_747 2.png" alt="logo">
        <span class="asset-ex-txt ms-2">AssetEx.</span>
        <div class="mt-4">
            <h2>Welcome Back, Boss!</h2>
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <input type="submit" class="btn w-100" value="Continue">
            </form>
        </div>
    </div>
    
    
    <!-- Bootstrap Toast positioned at the top center -->
    <div class="toast-container">
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo $error_message; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
     <script>
        // Show toast if there is an error
        const errorMessage = "<?php echo $error_message; ?>";
        if (errorMessage) {
            const toast = new bootstrap.Toast(document.getElementById('errorToast'));
            toast.show();
        }
    </script>
    
</body>
</html>
