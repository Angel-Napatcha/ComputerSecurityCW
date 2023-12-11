<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Start or resume the session
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

// Regenerate session ID to enhance security
session_regenerate_id(true);

$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

$secretKey = '6LdGDiwpAAAAAKaL68Q7TouTZP62BVUTqRK7H21d';
$recaptchaResponse = $_POST['g-recaptcha-response'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    // If there is an error with the connection, stop the script and display the error.
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (isset($recaptchaResponse) && !empty($recaptchaResponse)){
    $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse;
    $response = file_get_contents($url);
    $recaptchaResult = json_decode($response);

    if (!$recaptchaResult->success){
        exit('reCAPTCHA verification failed. Please try again.');
    }
} else {
    exit('reCAPTCHA response is missing.');
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    exit('Email is not valid!');
}

if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    exit('Username is not valid!');
}

if (!preg_match('/^[0-9]{11}$/', $_POST['telephone_no'])) {
    exit('Phone number is not valid!');
}

// Check if the username or email already exists
$check_stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? OR email = ?');
$check_stmt->bind_param('ss', $_POST['username'], $_POST['email']);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Username or email already exists
    exit('Username and/or email already exists, please choose another!');
}

$check_stmt->close();

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
$subject = 'Account Activation Required';
$mail->setFrom($from, 'Lovejoy-Antique.com');
$mail->addAddress($_POST['email']);
$mail->isHTML(true);

// Insert user data into the database
if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, telephone_no, activation_token, activation_expires) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP + INTERVAL 30 MINUTE)')) {
    // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));
    $stmt->bind_param('sssss', $_POST['username'], $password, $_POST['email'], $_POST['telephone_no'], $token);
    $stmt->execute();

    // Close the statement
    $stmt->close();

    // Send verification email
    $activatation_link = 'http://localhost/phplogin/activate.php?email=' . $_POST['email'] . '&token=' . $token;
    $message = '<p>Please click the following link to activate your account: <a href="' . $activatation_link . '">' . $activatation_link . '</a></p>';
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Check if email was sent successfully
    try {
        $mail->send();
        echo 'Please check your email to activate your account!';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    // Something is wrong with the SQL statement
    echo 'Could not prepare statement!';
}

// Close the database connection
$con->close();
?>