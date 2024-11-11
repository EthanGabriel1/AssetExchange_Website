<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'email' and 'message' are set in the POST request
    if (isset($_POST['email']) && isset($_POST['message'])) {
        $to = $_POST['email'];
        $subject = "New Notification from Asset Exchange";
        $message = $_POST['message']; // Get the message from the POST request

        // Send email and check the result
        if (sendEmail($to, $subject, $message)) {
            echo "Email sent successfully to $to.";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "No email address or message provided.";
    }
} else {
    echo "Invalid request method.";
}

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

?>