<?php
// Start or resume the session
session_start();

// PHPMailer classes for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Database connection configuration
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'id21662357_lovejoys_antiqueuser';
$DATABASE_PASS = '@Lovejoy1234';
$DATABASE_NAME = 'id21662357_lovejoys_antique';

// Establish a connection to the database
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

// reCAPTCHA configuration
$secretKey = '6LdGDiwpAAAAAKaL68Q7TouTZP62BVUTqRK7H21d';
$recaptchaResponse = $_POST['g-recaptcha-response'];

// Exit if there is an error connecting to the database
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Validate reCAPTCHA response
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

// Validate email format
if (!filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {
    exit('Email is not valid!');
}

// Validate username format using regular expression
if (preg_match('/^[a-zA-Z0-9]+$/', htmlspecialchars($_POST['username'])) === 0) {
    exit('Username is not valid!');
}

// Validate phone format
if (!preg_match('/^(?:\+\d{1,4}\s?)?\d{10,}$/', htmlspecialchars($_POST['telephone_no']))) {
    exit('Phone number is not valid!');
}

// Check if the username or email already exists
$check_stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? OR email = ?');
$check_stmt->bind_param('ss', $_POST['username'], $_POST['email']);
$check_stmt->execute();
$check_stmt->store_result();

// Check if the username exists
if ($check_stmt->num_rows > 0) {
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
if ($stmt = $con->prepare('INSERT INTO accounts (username, email, telephone_no, password, security_question, security_answer, activation_token, activation_expires, admin) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP + INTERVAL 30 MINUTE, ?)')) {
    // Hash the password and security answer
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $securityQuestion = $_POST['security_question'];
    $securityAnswer = password_hash($_POST['security_answer'], PASSWORD_DEFAULT);

    // Generate a unique activation token
    $token = bin2hex(random_bytes(16));
    // Check if the username is 'admin' to determine admin role
    $isAdmin = ($_POST['username'] === 'admin') ? true : false;
    
    $stmt->bind_param('sssssssi', $_POST['username'], $_POST['email'], $_POST['telephone_no'], $password, $securityQuestion, $securityAnswer, $token, $isAdmin);
    $stmt->execute();

    // Close the statement
    $stmt->close();

    // Send verification email
    $activatation_link = 'https://lovejoys-antique-249764.000webhostapp.com/activate.php?email=' . $_POST['email'] . '&token=' . $token;
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