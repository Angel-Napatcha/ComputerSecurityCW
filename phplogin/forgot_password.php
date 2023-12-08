<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    // If there is an error with the connection, stop the script and display the error.
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    exit('Email is not valid!');
}

// Check if the email exists
$check_stmt = $con->prepare('SELECT id, username, activation_token FROM accounts WHERE email = ?');
$check_stmt->bind_param('s', $_POST['email']);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    // Email does not exist
    exit('This email is not registered!');
}

$check_stmt->bind_result($user_id, $username, $activation_token);
$check_stmt->fetch();
$check_stmt->close();

// Check if the account is activated
if ($activation_token !== 'activated') {
    exit('This account is not activated. Please activate your account before resetting the password.');
}

// PHPMailer configuration
$mail = new PHPMailer(true);
$mail->SMTPDebug = 0;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'napatcha.angel@gmail.com';
$mail->Password = 'yvgizipkjbskvqgo';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$from = 'noreply@yourdomain.com';
$subject = 'Password Reset';

// Generate a unique token
$token = bin2hex(random_bytes(16));

// Update the user's token and expiration time in the database
$update_token_stmt = $con->prepare('UPDATE accounts SET reset_token = ?, reset_expires = CURRENT_TIMESTAMP + INTERVAL 30 MINUTE WHERE id = ?');
$update_token_stmt->bind_param('si', $token, $user_id);
$update_token_stmt->execute();
$update_token_stmt->close();

// Send password reset email
$reset_link = 'http://localhost/phplogin/reset_password.php?email=' . urlencode($_POST['email']) . '&token=' . urlencode($token);
$mail->setFrom($from, 'Lovejoy-Antique.com');
$mail->addAddress($_POST['email']);
$mail->isHTML(true);

$message = '<p>You have requested to reset your password. Click the following link to reset it: <a href="' . $reset_link . '">' . $reset_link . '</a></p>';
$mail->Subject = $subject;
$mail->Body = $message;

// Check if email was sent successfully
try {
    $mail->send();
    echo 'Please check your email to reset your password!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

// Close the database connection
$con->close();
?>
