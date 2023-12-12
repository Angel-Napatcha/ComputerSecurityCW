<?php
session_start();

// If the user is already logged in, redirect to the home page
if ($_SESSION['loggedin']) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin_home.php');
        exit;
    } else {
        header('Location: home.php');
        exit;
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="style.css" rel="stylesheet" type="text/css">
    <meta charset="utf-8">
    <title>Forgot Password</title>
</head>

<body>
    <div class="form-container">
        <h1>Reset Password</h1>
        <form action="reset_link.php" method="post">
            <label for="email">
                <i class="fas fa-envelope"></i>
            </label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Reset Password">
        </form>
    </div>
</body>

</html>