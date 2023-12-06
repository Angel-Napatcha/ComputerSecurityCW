<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="reset-password">
        <h2>Reset Password</h2>
        <form action="handle_reset.php" method="post">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <!-- Display error message if passwords don't match -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'nomatch') : ?>
                <p class="error">Passwords do not match</p>
            <?php endif; ?>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>

