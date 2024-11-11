<?php 
require_once 'phpmailer_demo.php';
require_once 'config_assetexchange.php';

// Start a session to store the verification email
session_start();

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && $_POST['action'] == 'signup') {
            // Signup logic
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                die(json_encode(["code"=>'Error: Missing email or password.']));
            }
            $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
            if (empty($email) || empty($_POST['password'])) {
                die(json_encode(['code'=> 'Error: Empty email or password.']));
            }

            try {
                $stmt = $conn->prepare("SELECT count(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if (((int) $stmt->fetchColumn()) > 0) {
                    die(json_encode(['code'=> 'Error: Email already exists.']));
                }
            } catch (PDOException $e) {
                die(json_encode(['code'=> 'Error: User registration failed due to a database error. ' . $e->getMessage()]));
            }

            if (isset($_POST['full_name']) && isset($_POST['role_id'])) {
                $pw_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $fullName = trim($_POST["full_name"]);
                try {
                    $stmt = $conn->prepare("INSERT INTO users (pw_hash, full_name, email, role_id) VALUES (?, ?, ?, ?);");
                    if ($stmt->execute([$pw_hash, $fullName, $email, $_POST['role_id']])) {
                        // Set the session variable for the verification email
                        $_SESSION['verification_email'] = $email;

                        $stmt = $conn->prepare("SELECT (email_verification_code) FROM users WHERE email = ?;");
                        if ($stmt->execute([$email])) {
                            $code = $stmt->fetchColumn();
                            if ($code) {
                                send_verification_mail($email, $code);
                            }
                        }
                    }
                } catch (PDOException $e) {
                    die('{"code": "Error: User registration failed due to a database error. "' . $e->getMessage() . '"}');
                }
            }
        }
    }
} catch (PDOException $e) {
    die('{"code": "Error: Connection failed: ' . $e->getMessage() . '"}');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <style>
        /* Same styling as before */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        
        
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            height: 500px;
        }
        
        
        .container-div {
            margin-top: 40px;
        }
        
        
         .container img {
            position: relative;
            left: 120px;
            top: 20px;
        }
        
        
        h2 {
            font-family: "Roboto", Helvetica;
            color: #222;
            text-align: center;
        }
        
        
        input[type="text"], input[type="email"], input[type="password"] {
            width: 93%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            background-color: #F8523C;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="./images/Image_20240530_002818_747 2.png" alt="logo">
        <div class="container-div">
        <h2>Input your Credentials</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="signup">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="role_id" placeholder="Role ID" required>
            <input type="submit" value="Signup">
        </form>
        <p style="text-align: center;">Already have an account? <a href="admin_login.php">Login</a></p>
    </div>
</div>
</body>
</html>
