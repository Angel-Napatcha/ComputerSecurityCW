<?php

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if passwords match
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        // Redirect back to the reset password page with an error parameter
        $email = urlencode($_POST['email']);
        $token = urlencode($_POST['token']);
        header("Location: reset_password.php?email=$email&token=$token&error=nomatch");
        exit();
    }

    // Hash the new password before updating
    $newPasswordHash = password_hash($_POST['confirm_password'], PASSWORD_DEFAULT);

    // Update the user's password and clear the reset token
    $updateStmt = $con->prepare('UPDATE accounts SET password = ?, reset_token = NULL WHERE email = ? AND reset_token = ?');
    $updateStmt->bind_param('sss', $newPasswordHash, $_POST['email'], $_POST['token']);
    $updateResult = $updateStmt->execute();

    if ($updateResult === false) {
        // Provide an error message if the update query has an error
        echo 'Error: ' . $updateStmt->error;
    } else {
        // Provide a success message if the update was successful
        echo 'Password reset successful!';
    }

    // Close the statement
    $updateStmt->close();
}

// Close the database connection
$con->close();
?>
