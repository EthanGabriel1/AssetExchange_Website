<?php
$servername = "localhost"; // Update as necessary
$username = "u843230181_group7_2";
$password = "Zugzwang6969";
$dbname = "u843230181_test2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the file hash from the query string
$file_hash = $_GET['file_hash'];

// Prepare and execute the SQL statement
$sql = "SELECT file_data FROM files WHERE file_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $file_hash);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($file_data);
$stmt->fetch();

if ($stmt->num_rows > 0) {
    // Output the image data
    header("Content-Type: image/png"); // Change this based on your file type
    echo $file_data;
} else {
    // Handle error
    http_response_code(404);
    echo "Image not found.";
}

$stmt->close();
$conn->close();
?>
