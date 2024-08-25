<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['count'] > 0) {
        $message = "A megadott e-mail cím már foglalt.";
    } else {
        $activation_token = bin2hex(random_bytes(50));
        $activation_link = "https://leet.stud.vts.su.ac.rs/activate.php?token=$activation_token";

        $_SESSION['activation_token'] = $activation_token;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        $email_subject = 'PetLeet - Fiók azonosítás';
        $email_body = '<!DOCTYPE html>
                        <html lang="hu">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>PetLeet - Fiók azonosítás</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    background-color: #FCF8EE;
                                    margin: 0;
                                    padding: 0;
                                }
                                .container {
                                    max-width: 550px;
                                    margin: 0 auto;
                                    padding: 15px;
                                    background: #ffffff;
                                    border-radius: 5px;
                                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                }
                                .header {
                                    background-color: #FBF7EB;
                                    color: #ffffff;
                                    text-align: center;
                                    padding: 10px;
                                    border-top-left-radius: 5px;
                                    border-top-right-radius: 5px;
                                }
                                .content {
                                    padding: 15px;
                                    text-align: left;
                                }
                                .button {
                                    display: inline-block;
                                    padding: 10px 20px;
                                    background-color: #0f52ba;
                                    color: #ffffff;
                                    text-decoration: none;
                                    border-radius: 10px;
                                    margin-top: 20px;
                                }
                                .button:hover {
                                    background-color: #4cae4c;
                                }
                                .footer {
                                    text-align: center;
                                    margin-top: 20px;
                                    color: #999999;
                                    font-size: 12px;
                                }
                                @media only screen and (max-width: 600px) {
                                    .container {
                                        max-width: 100%;
                                    }
                                }
                            </style>
                        </head>
                        <body>
                            <table class="container" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="header" colspan="2">
                                        <img src="https://leet.stud.vts.su.ac.rs/images/logo.png" alt="PetLeet Logo" style="max-width: 150px; display: block; margin: 0 auto;">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="content">
                                        <p style="font-size: 18px;">Kedves ' . $username . ',</p>
                                        <p>Köszönjük a regisztrációt a házikedvence örökbefogadó oldalunkra!</p>
                                        <p>Már csak egy kattintástól vagy hogy örökbefogadj egy házikedvencet:</p>
                                        <a class="button" href="' . $activation_link . '">Fiók aktiválása</a>
                                        <p style="margin-top: 20px;">Ez a link csak 24 óráig érvényes.</p>
                                        <p>Üdvözlettel,<br>Turuc István a PetLeet megalakítója</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="footer">
                                        <p>Ha bármilyen kérdésed akad kérlek lépj kapcsolatba velünk.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>';

        if (sendEmail($email, $email_subject, $email_body)) {
            $message = "A regisztráció sikeres volt. Kérem ellenőrizze e-mail fiókját a fiók aktiválásához szükséges linkért.";
        } else {
            $message = "Az emailt nem sikerült elküldeni, próbáld meg késpbb.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PetLeet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    
    <div class="register-container">
        <img src="images/logo.png" alt="PET Logo">
        <h2><i class="fas fa-user-plus"></i> Regisztráció</h2>
        <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>
        <form action="register.php" method="post">
            <label for="username"><i class="fas fa-user"></i> Felhasználónév:</label>
            <input type="text" name="username" id="username" required>
            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" name="email" id="email" required>
            <label for="password"><i class="fas fa-lock"></i> Jelszó:</label>
            <input type="password" name="password" id="password" required>
            <label for="first_name"><i class="fas fa-user"></i> Keresztnév:</label>
            <input type="text" name="first_name" id="first_name" required>
            <label for="last_name"><i class="fas fa-user"></i> Vezetéknév:</label>
            <input type="text" name="last_name" id="last_name" required>
            <input type="submit" value="Regisztráció">
        </form>
        <p>Már van fiókja? <a href="login.php">Jelentkezzen be itt</a>.</p>
    </div>
    
</body>
</html>
