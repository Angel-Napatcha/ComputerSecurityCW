<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
</head>
<body>
    <div class="reset-password">
        <h2>Reset Password</h2>
        <form action="handle_reset.php" method="post" onsubmit="return validatePassword()" autocomplete="off">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" oninput="updatePasswordStrength()" required>
            <div id="strength-text"></div>
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

<script>
    // Function to update password strength in real-time
    function updatePasswordStrength() {
        var password = document.getElementById('new_password').value;
        var strengthText = document.getElementById('strength-text');
        var passwordInput = document.getElementById('new_password');

        // Use zxcvbn for password strength estimation
        var result = zxcvbn(password);

        // Display zxcvbn feedback
        strengthText.textContent = result.feedback.suggestions.join(', ');

        // Set color and text based on zxcvbn score
        switch (result.score) {
            case 0:
            case 1:
                passwordInput.style.border = '2px solid red';
                strengthText.style.color = 'red';
                strengthText.textContent = 'Weak: ' + strengthText.textContent;
                break;
            case 2:
                passwordInput.style.border = '2px solid orange';
                strengthText.style.color = 'orange';
                strengthText.textContent = 'Moderate: Consider adding a mix of uppercase letters, numbers, and symbols for additional security.';
                break;
            case 3:
                passwordInput.style.border = '2px solid orange';
                strengthText.style.color = 'orange';
                strengthText.textContent = 'Moderate: Good job! Consider adding a mix of uppercase letters, numbers, and symbols for additional security.';
                break;
            case 4:
                passwordInput.style.border = '2px solid green';
                strengthText.style.color = 'green';
                strengthText.textContent = 'Strong: Excellent! Your password meets the highest standards of security.';
                break;
        }

        // Apply styles to the strength text
        strengthText.style.textAlign = 'center';
        strengthText.style.fontSize = '14px';
        strengthText.style.padding = '10px';
        strengthText.style.marginBottom = '20px';
    }

    // Add an event listener to the password input to update strength in real-time
    document.getElementById('new_password').addEventListener('input', updatePasswordStrength);

    function validatePassword() {
        var password = document.getElementById('new_password').value;
        var result = zxcvbn(password);

        // Check if password strength is strong (score 4)
        if (result.score < 3) {
            alert('Please choose a stronger password.');
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
</script>

</html>