<?php
session_start(); 
require_once '../db_config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; 
    $password = $_POST['password']; 

    try {
        // Checking for the user in the database
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_admin']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = true;
                header("Location: index.php"); // Redirection to admin page
            } else {
                $message = "Access denied. Only admin users can log in here."; // Error if it's not an admin trying to login
            }
        } else {
            $message = "Invalid email or password."; // Error if credentials are incorrect
        }
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage()); // Database connection error
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLeet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/admin_login.css"> <!-- Stylesheet -->
</head>
<body>
    
    <div class="login-container">
        <img src="../images/logo.png" alt="PET Logo">
        <h2><i class="fas fa-user"></i> Admin Login</h2>
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?> <!-- Error code if something goes wrong -->
        <form action="admin_login.php" method="post">
            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" name="email" id="email" required>
            <label for="password"><i class="fas fa-lock"></i> Password:</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Login">
        </form>
    </div>
    
</body>
</html>
