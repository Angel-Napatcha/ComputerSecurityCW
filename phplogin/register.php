<?php
session_start();

// If the user is already logged in, redirect to the home page
if ($_SESSION['loggedin']) {
    if ($_SESSION['user_type'] === 'admin') {
        // Redirect admin users to the admin home page
        header('Location: admin_home.php');
    } else {
        // Redirect regular users to the home page
        header('Location: home.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" ></script>
    <script>
        // Function to update password strength in real-time
        function updatePasswordStrength() {
            var password = document.getElementById('password').value;
            var strengthText = document.getElementById('strength-text');
            var passwordInput = document.getElementById('password');

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
                    passwordInput.style.border = '2px solid #f0ce3a';
                    strengthText.style.color = '#f0ce3a';
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
            strengthText.style.marginBottom = '30px';
        }

        // Add an event listener to the password input to update strength in real-time
        document.getElementById('password').addEventListener('input', updatePasswordStrength);

        // Function to validate password strength and form submission
        function validatePassword() {
            var password = document.getElementById('password').value;
            var confirm_password = document.getElementById('confirm_password').value;
            var result = zxcvbn(password);

            // Check if passwords match
            if (password !== confirm_password) {
                alert('Passwords do not match!');
                return false; // Prevent form submission
            }

            // Check if password strength is strong (score 4)
            if (result.score < 4) {
                alert('Please choose a stronger password.');
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }

        // Function to validate required fields and initiate custom password validation
        function validateForm() {
            var form = document.getElementById('register_form');
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

        // Function to submit the form after validating
        function onSubmit(token) {
            if (validateForm()) {
                document.getElementById("register_form").submit();
            }
        }
    </script>

</head>
<body>
    <!-- Register form -->
    <div class="form-container">
        <h1>Register</h1>
        <form id="register_form" method="post" action="handle_register.php" onsubmit="return validatePassword()" autocomplete="off">
            <!-- Form group for username -->
            <label for="username">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            
            <!-- Form group for email -->
            <label for="email">
                <i class="fas fa-envelope"></i>
            </label>
            <input type="email" name="email" placeholder="Email" id="email" required>
            
            <!-- Form group for telephone number -->
            <label for="telephone_no">
                <i class="fas fa-phone"></i>
            </label>
            <input type="tel" name="telephone_no" placeholder="e.g., 07123 456 789 or +44 7123 676834" id="telephone_no" required>
            
            <!-- Form group for password -->
            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" id="password" name="password" placeholder="Password" oninput="updatePasswordStrength()" required>
            <div id="strength-text"></div>
            <label for="confirm password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            
            <!-- Form group for security question -->
            <label for="security_question">
                <i class="fas fa-key"></i>
            </label>
            <select name="security_question" required>
                <option value="" disabled selected>Select a Security Question</option>
                <option value="Which city were you born in?">Which city were you born in?</option>
                <option value="What was the name of your first school?">What was the name of your first school?</option>
                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
            </select>
            
            <!-- Form group for security answer -->
            <label for="security_answer">
                <i class="fas fa-key"></i>
            </label>
            <input type="text" name="security_answer" placeholder="Answer to Security Question" required>
            
            <!-- Form group for the submit button with reCAPTCHA -->
            <input type="submit" class="g-recaptcha"
                data-sitekey="6LdGDiwpAAAAABX7xkZtqZmcjvfjkSiDvGIWyGPt"
                data-callback='onSubmit'
                data-action='submit' value='Create Account'>
        </form>
    </div>
</body>
</html>
