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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" ></script>
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
            strengthText.style.marginTop = '10px';
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

        function validateForm() {
            var form = document.getElementById('reset_password');
            var requiredFields = form.querySelectorAll('[required]');

            // Check if any required field is empty
            for (var i = 0; i < requiredFields.length; i++) {
                if (!requiredFields[i].value) {
                    // Display the default browser behavior for required fields
                    requiredFields[i].reportValidity();
                    return false; // Prevent form submission
                }
            }

            return validatePassword(); // Proceed with custom password validation
        }

        function onSubmit(token) {
            if (validateForm()) {
                document.getElementById("reset_password").submit();
            }
        }
    </script>
</head>
<body>
    <div class="reset-password">
        <h2>Reset Password</h2>
        <form id="reset_password" action="handle_reset.php" method="post" onsubmit="return validatePassword()" autocomplete="off">
        <?php
            // Include your database connection code
            $DATABASE_HOST = '127.0.0.1';
            $DATABASE_USER = 'root';
            $DATABASE_PASS = '';
            $DATABASE_NAME = 'phplogin';

            // Try and connect using the info above.
            $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

            if (mysqli_connect_errno()) {
                exit('Failed to connect to MySQL: ' . mysqli_connect_error());
            }

            // Fetch the security question based on the user's email
            $email = $_GET['email'];
            $stmt = $con->prepare('SELECT security_question FROM accounts WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($securityQuestion);
            $stmt->fetch();
            $stmt->close();
            ?>
            
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

            <!-- Print the chosen question from the security_question column -->
            <?php
            if (isset($securityQuestion)) {
                echo '<div class="security-question">';
                echo '<p class="question-label">Security Question:</p>';
                echo '<p class="question-text">' . htmlspecialchars($securityQuestion) . '</p>';
                echo '</div>';
            }
            ?>

            <!-- Add textfield for the answer to the security question -->
            <label for="security_answer">Security Answer:</label>
            <input type="text" id="security_answer" name="security_answer" required>

            <button type="submit" class="g-recaptcha"
                data-sitekey="6LdGDiwpAAAAABX7xkZtqZmcjvfjkSiDvGIWyGPt"
                data-callback='onSubmit'
                data-action='submit'>Reset Password</button>
        </form>
    </div>
</body>
</html>