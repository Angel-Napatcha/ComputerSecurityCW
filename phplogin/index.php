<!DOCTYPE html>
<html lang="en">
<head>
    <link href="style.css" rel="stylesheet" type="text/css">
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://www.google.com/recaptcha/api.js" ></script>
    <script>
        function validateForm() {
            var form = document.getElementById('login_form');
            var requiredFields = form.querySelectorAll('[required]');

            // Check if any required field is empty
            for (var i = 0; i < requiredFields.length; i++) {
                if (!requiredFields[i].value) {
                    // Display the default browser behavior for required fields
                    requiredFields[i].reportValidity();
                    return false; // Prevent form submission
                }
            }

            return true; // Proceed with custom password validation
        }

        function onSubmit(token) {
            if (validateForm()) {
                document.getElementById("login_form").submit();
            }
        }
    </script>
</head>
<body>
</head>
<body class="login-page"> 
    <div class="form-container">
        <h1>Login</h1>
        <form id=login_form class=register action="authenticate.php" method="post">
            <label for="username">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <div class="forgot-password">
                <a href="forgot_password.html">Forgot Password?</a>
            </div>
            <input type="submit" class="g-recaptcha"
                    data-sitekey="6LdGDiwpAAAAABX7xkZtqZmcjvfjkSiDvGIWyGPt"
                    data-callback='onSubmit'
                    data-action='submit' value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>