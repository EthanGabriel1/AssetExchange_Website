<?php
require_once 'config_assetexchange.php';

// Start a session to store the email temporarily
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verification_code'])) {
    // Retrieve the email from the session
    if (!isset($_SESSION['verification_email'])) {
        echo json_encode(['code' => 'Error: Email not set.']);
        exit;
    }
    $email = $_SESSION['verification_email'];
    $verification_code = $_POST['verification_code'];

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare("SELECT email_verification_code FROM `users` WHERE email = ?");
        $stmt->execute([$email]);
        $stored_code = $stmt->fetchColumn();

        if ($stored_code && $stored_code === $verification_code) {
            // Mark the user as verified
            $update_stmt = $conn->prepare("UPDATE `users` SET email_verified = 1, email_verified = 1 WHERE email = ?");
            if ($update_stmt->execute([$email])) {
                echo json_encode(['code' => 'Success: Your account has been verified.']);
            }
        } else {
            echo json_encode(['code' => 'Error: Invalid verification code.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['code' => 'Error: ' . $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account</title>
</head>
<body>
    <div class="container">
        <h2>Verify Your Account</h2>
        <form method="POST" action="">
            <input type="text" name="verification_code" placeholder="Verification Code" required>
            <input type="submit" value="Verify">
        </form>
    </div>
</body>
</html>
