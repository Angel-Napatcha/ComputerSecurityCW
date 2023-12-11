<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Start or resume the session
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

// Regenerate session ID to enhance security
session_regenerate_id(true);

$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

$secretKey = '6LdGDiwpAAAAAKaL68Q7TouTZP62BVUTqRK7H21d';
$recaptchaResponse = $_POST['g-recaptcha-response'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (isset($recaptchaResponse) && !empty($recaptchaResponse)) {
    $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse;
    $response = file_get_contents($url);
    $recaptchaResult = json_decode($response);

    if (!$recaptchaResult->success) {
        exit('reCAPTCHA verification failed. Please try again.');
    }
} else {
    exit('reCAPTCHA response is missing.');
}

$target_dir = "uploads/";
$target_file = __DIR__ . '/' . $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if image file is a valid image
$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
if ($check === false) {
    echo "File is not an image.";
    $uploadOk = 0;
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
$allowedFormats = ["jpg", "jpeg", "png", "gif"];
if (!in_array($imageFileType, $allowedFormats)) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    // Move the image to the target directory
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";

        // Now you can insert the file path into the database
        // Update your SQL query accordingly to include the image file path
        $imageFilePath = $target_file;

        $userId = $_POST['user_id'];
        $objectDetails = $_POST['object_details'];
        $contactMethod = $_POST['contact_method'];

        // Fetch user's contact information from the database
        $query = "SELECT email, telephone_no FROM accounts WHERE id = $userId";
        $result = mysqli_query($con, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);

            // Determine contact type and information
            $contactType = '';
            $contact = '';
            $contactInformation = $row['email'];

            if ($contactMethod === 'email' && !empty($row['email'])) {
                $contact = $row['email'];
                $contactType = 'email';
            } elseif ($contactMethod === 'phone' && !empty($row['telephone_no'])) {
                $contact = $row['telephone_no'];
                $contactType = 'phone';
            } else {
                // Handle the case where the chosen contact method does not exist or is empty
                echo "Error: Invalid contact method, missing data, or contact information is empty.";
                exit;
            }

            // Function to send request confirmation email
            function sendRequestConfirmation($contactType, $contactInformation, $objectDetails, $con, $imageFilePath)
            {
                // PHPMailer configuration
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'napatcha.angel@gmail.com';
                $mail->Password = 'yvgizipkjbskvqgo';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $from = 'noreply@yourdomain.com';
                $subject = 'Request Confirmation';
                $mail->setFrom($from, 'Lovejoy-Antique.com');
                $mail->addAddress($contactInformation);
                $mail->isHTML(true);

                // Send request confirmation email
                $message = "<p>Thank you for your request. Here are the details:</p>";
                $message .= "<p><strong>Object Details:</strong> $objectDetails</p>";
                $message .= "<p><strong>Contact Method:</strong> $contactType</p>";
                $mail->Subject = $subject;
                $mail->Body = $message;

                try {
                    $mail->send();
                    echo 'Request submitted successfully! Confirmation email sent.';
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }

            // Add the $imageFilePath to your database query or update statement
            $stmt = $con->prepare("INSERT INTO requests (user_id, object_details, contact_method, contact, image_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $userId, $objectDetails, $contactMethod, $contact, $imageFilePath);

            if ($stmt->execute()) {
                // Send confirmation email
                sendRequestConfirmation($contactType, $contactInformation, $objectDetails, $con, $imageFilePath);
            } else {
                echo "Error submitting request. Please try again.";
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Invalid request method.";
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Close the database connection
$con->close();
?>
