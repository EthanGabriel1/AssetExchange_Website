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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $notificationId = intval($data['id']);
    
    // Mark the notification as deleted instead of actually deleting it
    $sql = "UPDATE push_notifications SET deleted = 1 WHERE id = $notificationId";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

$conn->close();
?>
