<?php
session_start();
$servername = "localhost";
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the message and email from the POST request
$message = $_POST['message'];
$email = $_POST['email'];
$status = 'Sent';  // You can modify this status if needed

// Fetch user ID based on the email
$user_id = '';  
$sql_user = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_id = $result->fetch_assoc()['user_id'];
}

// Insert notification into push_notifications table
$sql_insert = "INSERT INTO push_notifications (title, message, user_id, created_at, status) 
               VALUES (?, ?, ?, NOW(), ?)";
$stmt_insert = $conn->prepare($sql_insert);
$title = 'Notification';  // Customizable title
$stmt_insert->bind_param("ssis", $title, $message, $user_id, $status);

if ($stmt_insert->execute()) {
    echo "Notification added to the database";
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();
?>
