<?php

use PHPMailer\PHPMailer\PHPMailer;
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
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Function to resend activation link
function resendActivationLink($email, $con) {
    $newToken = bin2hex(random_bytes(16));
    $updateStmt = $con->prepare('UPDATE accounts SET activation_token = ?, activation_expires = CURRENT_TIMESTAMP + INTERVAL 30 MINUTE WHERE email = ?');
    $updateStmt->bind_param('ss', $newToken, $email);
    $updateStmt->execute();

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
    $subject = 'Resend Activation Link';
    $mail->setFrom($from, 'Lovejoy-Antique.com');
    $mail->addAddress($email);
    $mail->isHTML(true);

    // Send verification email
    $activationLink = 'http://localhost/phplogin/activate.php?email=' . $email . '&token=' . $newToken;
    $message = '<p>Please click the following link to activate your account: <a href="' . $activationLink . '">' . $activationLink . '</a></p>';
    $mail->Subject = $subject;
    $mail->Body = $message;

    try {
        $mail->send();
        echo 'Activation link sent. Please check your email to activate your account!';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Check if email and token exist in the URL.
if (isset($_GET['email'], $_GET['token'])) {
    if ($stmt = $con->prepare('SELECT activation_token, activation_expires FROM accounts WHERE email = ? AND activation_token = ?')) {
        $stmt->bind_param('ss', $_GET['email'], $_GET['token']);
        $stmt->execute();
        $stmt->store_result();

        date_default_timezone_set('Europe/London');
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($activationToken, $activationExpires);
            $stmt->fetch();

            $currentTime = time();
            if (strtotime($activationExpires) > $currentTime) {
                $newToken = 'activated';
                $updateStmt = $con->prepare('UPDATE accounts SET activation_token = ?, activation_expires = NULL WHERE email = ? AND activation_token = ?');
                $updateStmt->bind_param('sss', $newToken, $_GET['email'], $activationToken);
                $updateStmt->execute();
                echo 'Your account is now activated. You can now <a href="index.html">login</a>!';
            } else {
                echo 'The activation link has expired. ';
                echo '<a href="?email=' . $_GET['email'] . '&resend=true">Click here</a> to resend the activation link.';
            }
        } else {
            echo 'Invalid activation link. Please make sure you have the correct email and token.';
        }
    } else {
        echo 'The account is already activated or doesn\'t exist!';
    }
} elseif (isset($_GET['resend'])) {
    // Resend activation link if the "resend" parameter is present in the URL
    resendActivationLink($_GET['email'], $con);
} else {
    echo 'Email and token not provided.';
}

// Close the database connection
$con->close();
?>
