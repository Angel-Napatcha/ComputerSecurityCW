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
            document.getElementById('password').addEventListener('input', updatePasswordStrength);

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
            if (result.score < 3) {
                alert('Please choose a stronger password.');
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }

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

        function onSubmit(token) {
            if (validateForm()) {
                document.getElementById("register_form").submit();
            }
        }
    </script>

</head>
<body>
    <div class="form-container">
        <h1>Register</h1>
        <form id="register_form" method="post" action="handle_register.php" onsubmit="return validatePassword()" autocomplete="off">
            <label for="username">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" id="password" name="password" placeholder="Password" oninput="updatePasswordStrength()" required>
            <div id="strength-text"></div>
            <label for="confirm password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <label for="email">
                <i class="fas fa-envelope"></i>
            </label>
            <input type="email" name="email" placeholder="Email" id="email" required>
            <label for="telephone_no">
                <i class="fas fa-phone"></i>
            </label>
            <input type="text" name="telephone_no" placeholder="Phone Number" id="telephone_no" required>
            <input type="submit" class="g-recaptcha"
                data-sitekey="6LdGDiwpAAAAABX7xkZtqZmcjvfjkSiDvGIWyGPt"
                data-callback='onSubmit'
                data-action='submit' value='Create Account'>
            <!-- <input type="submit" value="Register"> -->
        
    </div>
</body>
</html>