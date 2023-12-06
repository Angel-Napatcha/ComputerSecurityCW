<?php
session_start();

// Change this to your connection info.
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

// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (!isset($_POST['username'], $_POST['password'])) {
    // Could not get the data that should have been sent.
    exit('Please fill both the username and password fields!');
}

if ($stmt = $con->prepare('SELECT id, password, activation_code FROM accounts WHERE username = ?')) {
    // Bind parameters (s = string)
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $activation_code);
        $stmt->fetch();

        // Check if the account is activated
        if ($activation_code === 'activated') {

            // Now verify the password.
            if (password_verify($_POST['password'], $password)) {

                // Verification success! User has logged in!
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['name'] = $_POST['username'];
                $_SESSION['id'] = $id;
                header('Location: home.php');
            } else {
                // Incorrect password
                echo 'Incorrect username and/or password!<br>';
            }
        } else {
            // Account is not activated
            echo 'Account not activated. Please check your email for activation instructions.<br>';
        }
    } else {
        // Incorrect username
        echo 'Incorrect username and/or password!<br>';
    }

    $stmt->close();
} else {
    // Something went wrong with the SQL statement
    echo 'Could not prepare statement!<br>';
}

$con->close();
?>
