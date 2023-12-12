<?php
// Start or resume the session
session_start();

// Your existing check for logged-in status
if (!$_SESSION['loggedin'] || $_SESSION['user_type'] !== 'regular') {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Home Page</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function validateForm() {
            var form = document.getElementById('request_form');
            var requiredFields = form.querySelectorAll('[required]');

            // Check if any required field is empty
            for (var i = 0; i < requiredFields.length; i++) {
                if (!requiredFields[i].value) {
                    // Display the default browser behavior for required fields
                    requiredFields[i].reportValidity();
                    return false; // Prevent form submission
                }
            }

            return true; // Proceed with onSubmit function
        }

        function onSubmit(token) {
            if (validateForm()) {
                document.getElementById("request_form").submit();
            }
        }

        // Disable the back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };
    </script>
</head>

<body class="loggedin">
    <nav class="navtop">
        <div>
            <h1>Lovejoy’s Antique</h1>
            <a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </nav>
    <div class="content">
        <h2>Welcome back, <?= $_SESSION['name'] ?>!</h2>

        <!-- Modal for Request Evaluation Form -->
        <div id="request_modal">
            <div class="modal-content">
                <h2>Request Form</h2>
                <form id="request_form" action="handle_request.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
                    <div class="form-group">
                        <label for="object_details">Object Details:</label>
                        <textarea id="object_details" name="object_details" rows="4" required></textarea>
                    </div>

                    <!-- Inside the form group for image upload -->
                    <div class="form-group">
                        <label for="fileToUpload">Upload Image:</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" accept="image/*" required>
                    </div>

                    <!-- Inside the form group for contact method -->
                    <div class="form-group">
                        <label for="contact_method">Preferred Contact Method:</label>
                        <select id="contact_method" name="contact_method" required>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        </select>
                    </div>

                    <!-- Inside the form group for the submit button -->
                    <div class="form-group">
                        <button type="submit" class="submit-button g-recaptcha" data-sitekey="6LdGDiwpAAAAABX7xkZtqZmcjvfjkSiDvGIWyGPt"
                            data-callback='onSubmit' data-action='submit'>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
