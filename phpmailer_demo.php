<?php

//Load composer's autoloader
require 'vendor/autoload.php';
require_once 'config_phpmailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

function send_verification_mail($recipient, $code) {
    global $mail, $mail_username, $mail_password;
    $verification_code = str_pad($code, 6, '0', STR_PAD_LEFT); // str_pad(random_int(0,999999), 6, '0', STR_PAD_LEFT);
    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                 //Enable verbose debug output
        $mail->isSMTP();                                    //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';               //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                           //Enable SMTP authentication
        $mail->SMTPKeepAlive = true;                        //SMTP connection will not close after sending
        $mail->Username   = $mail_username;                 //SMTP username
        $mail->Password   = $mail_password;                 //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    //Enable implicit TLS encryption
        $mail->Port       = 465;                            //TCP port to connect to

        $mail->setFrom($mail_username, 'Asset Exchange'); // or alias
        $mail->addAddress($recipient);

        // //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        $mail->isHTML(true);
        $mail->Subject = 'Account Verification Code for Asset Exchange';
        $mail->Body    = 'Hello, here is your account verification code: <b>' . $verification_code . '</b>. This code will expire in 10 minutes.';

        if (!$mail->send()) {
            http_response_code(500);
            echo '{"code": "Error: Verification mail sending failed"}';
            // echo '{"code": 500, "message": "Verification mail sending failed"}';
        }
        else {
            echo '{"code": "Verification mail sent successfully"}';
            // echo '{"code": 200, "message": "Verification mail sent successfully"}';
            $mail->clearAllRecipients();
        }
    }
    catch (Exception $e) {
        echo '{"code": "Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '"}';
    }
}

// send_verification_mail('cburgess6969@protonmail.com')

?>