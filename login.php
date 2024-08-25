<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ellenőrizze, hogy a felhasználó megadta-e az e-mailt és a jelszót
    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
    } else {
        try {
            // Lekérdezés az adatbázisból a felhasználó ellenőrzésére
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, is_admin FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Ellenőrzés, hogy a felhasználó létezik-e és a jelszó helyes-e
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: index.php"); // Átirányítás a főoldalra
                exit;
            } else {
                $message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PetLeet - Bejelntkezés</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    
<div class="login-container">
    <img src="images/logo.png" alt="PETLeet logo">
    <h2><i class="fas fa-user"></i> Login</h2>
    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <label for="email"><i class="fas fa-envelope"></i> Email cím:</label>
        <input type="email" name="email" id="email" required>
        <label for="password"><i class="fas fa-lock"></i> Jelszó:</label>
        <input type="password" name="password" id="password" required>
        <input type="submit" value="Login">
    </form>
    <p><a href="forgot_password.php">Elfelejtetted a jelszavad?</a></p>
    <p><a href="register.php">Új fiók létrehozása.</a></p>
</div>

</body>
</html>
