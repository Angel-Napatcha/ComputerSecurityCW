<?php
// Start or resume the session
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

// Regenerate session ID to enhance security
session_regenerate_id(true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Home Page</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://www.google.com/recaptcha/api.js"></script>
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
        <h2>Welcome back, <?=$_SESSION['name']?>!</h2>

        <!-- Display List of Requests -->
        <div>
            <h3>List of Requests</h3>

            <?php
            // Include your database connection here
            $DATABASE_HOST = '127.0.0.1';
            $DATABASE_USER = 'root';
            $DATABASE_PASS = '';
            $DATABASE_NAME = 'phplogin';

            $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

            if (mysqli_connect_errno()) {
                exit('Failed to connect to MySQL: ' . mysqli_connect_error());
            }

            // Fetch all requests from the database
            $query = "SELECT id, object_details, contact, contact_method, image_path, posted_at FROM requests";
            $result = mysqli_query($con, $query);

            if ($result) {
                // Output table header
                echo '<table class="custom-table">';
                echo '<tr><th>ID</th><th>Object Details</th><th>Contact Method</th><th>Contact Method</th><th>Image</th><th>Posted At</th></tr>';

                // Output data from rows
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td class="center-id">' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['object_details']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['contact']) . '</td>';
                    echo '<td class="contact-method">' . htmlspecialchars($row['contact_method']) . '</td>';
                    echo '<td><img src="uploads/' . htmlspecialchars(basename($row['image_path'])) . '" alt="Image" style="max-width: 100px; max-height: 100px;"></td>';
                    echo '<td>' . htmlspecialchars($row['posted_at']) . '</td>';
                    echo '</tr>';
                }

                // Output table footer
                echo '</table>';

                // Free result set
                mysqli_free_result($result);
            } else {
                echo "Error fetching requests: " . mysqli_error($con);
            }

            // Close the database connection
            mysqli_close($con);
            ?>
        </div>   
    </div>
</body>

</html>
