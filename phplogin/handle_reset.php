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

// Function to resend reset password link
function resendResetLink($email, $con) {
    $newToken = bin2hex(random_bytes(16));
    $updateStmt = $con->prepare('UPDATE accounts SET reset_token = ?, reset_expires = CURRENT_TIMESTAMP + INTERVAL 30 MINUTE WHERE email = ?');
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
    $subject = 'Resend Reset Password Link';
    $mail->setFrom($from, 'Lovejoy-Antique.com');
    $mail->addAddress($email);
    $mail->isHTML(true);

    // Send reset password email
    $resetLink = 'https://lovejoys-antique-249764.000webhostapp.com/reset_password.php?email=' . $email . '&token=' . $newToken;
    $message = '<p>Please click the following link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a></p>';
    $mail->Subject = $subject;
    $mail->Body = $message;

    try {
        $mail->send();
        echo 'Reset password link sent. Please check your email to reset your password!';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if passwords match
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        // Redirect back to the reset password page with an error parameter
        $email = htmlspecialchars($_POST['email']);
        $token = htmlspecialchars($_POST['token']);
        header("Location: reset_password.php?email=$email&token=$token&error=nomatch");
        exit();
    }

    // Retrieve the security answer from the user input
    $securityAnswer = htmlspecialchars($_POST['security_answer']);

    // Prepare and execute a database query to get the hashed security answer for the given email
    $checkSecurityStmt = $con->prepare('SELECT security_answer FROM accounts WHERE email = ?');
    $checkSecurityStmt->bind_param('s', $_POST['email']);
    $checkSecurityStmt->execute();
    $checkSecurityStmt->store_result();

    // Check if the security answer is correct
    if ($checkSecurityStmt->num_rows > 0) {
        $checkSecurityStmt->bind_result($hashedSecurityAnswer);
        $checkSecurityStmt->fetch();

        // Verify the security answer
        if (!password_verify($securityAnswer, $hashedSecurityAnswer)) {
            echo 'Wrong security answer.';
            exit();
        }
    }

    // Hash the new password before updating
    $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Retrieve the reset token and its expiration time
    $selectStmt = $con->prepare('SELECT reset_token, reset_expires FROM accounts WHERE email = ? AND reset_token = ?');
    $selectStmt->bind_param('ss', $_POST['email'], $_POST['token']);
    $selectStmt->execute();
    $selectStmt->store_result();

    // Check if the reset link exists and has not expired
    date_default_timezone_set('Europe/London');
    if ($selectStmt->num_rows > 0) {
        $selectStmt->bind_result($resetToken, $resetExpires);
        $selectStmt->fetch();

        // Check if the reset link has not expired
        $currentTime = time();
        if (strtotime($resetExpires) > $currentTime) {
            // Update the user's password and clear the reset token
            $updateStmt = $con->prepare('UPDATE accounts SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ? AND reset_token = ?');
            $updateStmt->bind_param('sss', $newPasswordHash, $_POST['email'], $_POST['token']);
            $updateResult = $updateStmt->execute();

            if ($updateResult === false) {
                // Provide an error message if the update query has an error
                echo 'Error: ' . $updateStmt->error;
            } else {
                // Provide a success message if the update was successful
                echo 'Password reset successful. You can now <a href="index.php">login</a>!';
            }

            // Close the update statement
            $updateStmt->close();
        } else {
            // Activation link has expired. Resend the link.
            echo 'The reset link has expired. ';
            echo '<a href="?email=' . urlencode($_POST['email']) . '&token=' . $_POST['token'] . '&resend=true">Click here</a> to resend the reset password link.';
        }
    } else {
        // Reset link not found or already used.
        echo 'Invalid reset link.';
    }

    // Close the select statement
    $selectStmt->close();
}

// Check for the resend parameter in the GET request
if (isset($_GET['resend']) && $_GET['resend'] === 'true') {
    // Call the resendResetLink function with the email and connection
    resendResetLink($_GET['email'], $con);
    // Close the database connection
    $con->close();
    // Exit to prevent further execution
    exit();
}

// Close the database connection
$con->close();

?>