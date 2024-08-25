<?php
session_start();

// Database config
require_once 'db_config.php';


date_default_timezone_set('Europe/Budapest'); //Europe/Budapest timezone

// Check if the request method is GET and if the 'token' parameter is present in the URL
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    // Retrieve the reset token from the URL parameter
    $resetToken = $_GET['token'];

    try {
        // Get the current date and time in the 'Y-m-d H:i:s' format
        $currentDateTime = date('Y-m-d H:i:s');

          // Prepare an SQL statement to check if the reset token exists and is still valid
        $stmt = $pdo->prepare("SELECT * FROM password_reset_requests WHERE reset_token = ? AND token_expiry > ?");
          // Execute the SQL statement with the reset token and current date as parameters
        $stmt->execute([$resetToken, $currentDateTime]);
          // Fetch the result row
        $request = $stmt->fetch();

        // Check if the request is valid 
        if ($request) {
            // Save the user ID in the session for password reset
            $_SESSION['reset_user_id'] = $request['user_id'];
            // Redirect to the change password page
            header("Location: change_password.php");
            exit();
        } else {
            // If the token is invalid, save an error message
            $_SESSION['error_message'] = "Érvénytelen vagy lejárt jelszó visszaállító link. Kérj újat.";
            header("Location: forgot_password.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Adatbázis kapcsolódási hiba: " . $e->getMessage()); 
    }
} else {
    // If the request method is not GET or the 'token' parameter is missing, save an error message in the session
    $_SESSION['error_message'] = "Érvénytelen kérés.";
    header("Location: forgot_password.php");
    exit();
}
?>
