<?php
session_start();
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: admin_login.php"); // Replace with your actual login page
exit();
?>
