<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_user_id'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $userId = $_SESSION['reset_user_id'];

    // Validate passwords
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: change_password.php");
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        // Update the user's password in the users table
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        // Delete the reset token from password_reset_requests table
        $stmt = $pdo->prepare("DELETE FROM password_reset_requests WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Redirect to login page with success message
        $_SESSION['success_message'] = "Password updated successfully. You can now login with your new password.";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION['reset_user_id'])) {
    // Display change password form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PET Adoption</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/change_password.css">
    <script src="js/change_password.js"></script>
</head>
<body>
    
    <div class="form-container">
        <img src="images/logo.png" alt="PET Logo">
        <h2><i class="fas fa-lock"></i> Change Password</h2>
        <?php if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if (isset($_SESSION['error_message'])): ?>
                        showToast("<?php echo $_SESSION['error_message']; ?>", 'error');
                    <?php elseif (isset($_SESSION['success_message'])): ?>
                        showToast("<?php echo $_SESSION['success_message']; ?>", 'success');
                    <?php endif; ?>
                });
            </script>
            <?php unset($_SESSION['error_message']);
                  unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form action="change_password.php" method="post">
            <label for="new_password"><i class="fas fa-key"></i> New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <label for="confirm_password"><i class="fas fa-key"></i> Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <input type="submit" value="Change Password">
        </form>
    </div>

</body>
</html>

<?php
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: login.php");
    exit();
}
?>
