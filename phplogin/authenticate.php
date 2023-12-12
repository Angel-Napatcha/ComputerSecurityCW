<?php
// Start or resume the session
session_start();

// Set default timezone
date_default_timezone_set('Europe/London');

// Database connection configuration
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

// Establish a connection to the database
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

// reCAPTCHA configuration
$secretKey = '6LdGDiwpAAAAAKaL68Q7TouTZP62BVUTqRK7H21d';
$recaptchaResponse = $_POST['g-recaptcha-response'];

// Exit if there is an error connecting to the database
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check if username and password are provided
if (!isset($_POST['username'], $_POST['password'])) {
    exit('Please fill both the username and password fields!');
}

// Validate reCAPTCHA response
if (isset($recaptchaResponse) && !empty($recaptchaResponse)){
    $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse;
    $response = file_get_contents($url);
    $recaptchaResult = json_decode($response);
    
    // Exit if reCAPTCHA verification fails
    if (!$recaptchaResult->success){
        exit('reCAPTCHA verification failed. Please try again.');
    }
} else {
    exit('reCAPTCHA response is missing.');
}

// Prepare and execute the statement to retrieve user details
if ($stmt = $con->prepare('SELECT id, password, activation_token, failed_attempts, locked_until, admin FROM accounts WHERE username = ?')) {
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    // Check if the username exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $activation_token, $failedAttempts, $lockedUntil, $adminRole);
        $stmt->fetch();

        // Check if the user is locked out
        if (isset($_SESSION['locked_until'])) {
            $lockedUntil = strtotime($_SESSION['locked_until']);
            $currentTime = time();

            // Check if the current time is before the expected unlock time
            if ($currentTime < $lockedUntil) {
                // User is still locked out
                exit('You are temporarily locked out. Please try again later.');
            } else {
                // Unlock the user
                $_SESSION['locked_until'] = null;  // Set to null instead of unsetting
            }
        }

        // Account is registered
        if ($activation_token === 'activated') {
            // Account is not locked, verify the password
            if (password_verify($_POST['password'], $password)) {
                // Verification success, user has logged-in
                session_regenerate_id();
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['name'] = htmlspecialchars($_POST['username']);
                $_SESSION['id'] = $id;
                $_SESSION['user_type'] = ($adminRole == 1) ? 'admin' : 'regular';
                
                // Reset failed attempts on successful login
                $updateStmt = $con->prepare("UPDATE accounts SET failed_attempts = NULL WHERE id = ?");
                $updateStmt->bind_param('i', $id);
                $updateStmt->execute();
                $updateStmt->close();

                // Redirect based on user role
                if ($adminRole == 1) {
                    header('Location: admin_home.php');
                } else {
                    header('Location: home.php');
                }

            } else {
                // Incorrect password
                echo 'Incorrect password!<br>';

                // Increment and update failed login attempts
                $failedAttempts = $failedAttempts + 1;
                $updateStmt = $con->prepare("UPDATE accounts SET failed_attempts = ?, locked_until = NULL WHERE id = ?");
                $updateStmt->bind_param('ii', $failedAttempts, $id);
                $updateStmt->execute();
                $updateStmt->close();

                // Lock the user after 3 failed attempts
                if ($failedAttempts >= 3) {
                    $lockedUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $_SESSION['locked_until'] = $lockedUntil;
                    $updateStmt = $con->prepare("UPDATE accounts SET locked_until = ? WHERE id = ?");
                    $updateStmt->bind_param('si', $lockedUntil, $id);
                    $updateStmt->execute();
                    $updateStmt->close();

                    exit('You have reached the maximum number of login attempts. Please try again after 30 minutes.');
                }
            }
        } else {
            // Account is not activated
            echo 'Account not activated. Please check your email for activation instructions.<br>';
        }
    } else {
        // Username not registered
        echo 'Username not registered!<br>';
    }
    // Close the statement
    $stmt->close();
} else {
    echo 'Could not prepare statement! Error: ' . $con->error;
}
// Close the database connection
$con->close();
?>