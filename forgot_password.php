<?php
// Munkamenet kezdése vagy folytatása
session_start();

// Adatbázis konfigurációs fájl és e-mail küldő fájl betöltése
require_once 'db_config.php';
require_once 'mailer.php';

// Időzóna beállítása Budapestre
date_default_timezone_set('Europe/Budapest');

// Funkció, ami véletlenszerű token-t generál
function generateToken($length = 20) {
    return bin2hex(random_bytes($length));
}

// Ha a kérés POST metódussal érkezett
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; // POST kérésből kiolvasott e-mail cím

    try {
        // Adatbázis lekérdezés: ellenőrizzük, hogy létezik-e a megadott e-mail cím a users táblában
        $stmt = $pdo->prepare("SELECT user_id, username, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); // Lekérdezett felhasználó adatainak eltárolása

        if ($user) {
            // Ha találtunk felhasználót az adott e-mail cím alapján

            // Véletlenszerű token generálása
            $resetToken = generateToken();

            // Token és lejárati idő tárolása a password_reset_requests táblában
            $expiryDate = date('Y-m-d H:i:s', strtotime('+24 hour')); // 24 óra múlva lejár a token
            $stmt = $pdo->prepare("INSERT INTO password_reset_requests (user_id, reset_token, token_expiry) VALUES (?, ?, ?)");
            $stmt->execute([$user['user_id'], $resetToken, $expiryDate]);

            // E-mail tartalom összeállítása
            $activation_link = "https://mam.stud.vts.su.ac.rs//reset_password.php?token=$resetToken";
            $subject = "Jelszó visszaállítás kérelem";

            $body = '<!DOCTYPE html>
            <html lang="hu">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Jelszó visszaállítás - PET Adoption</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
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
                        padding: 20px;
                        text-align: left;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #5cb85c;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
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
                            <img src="https://mam.stud.vts.su.ac.rs//images/logo.png" alt="PET Adoption Logo" style="max-width: 150px; display: block; margin: 0 auto;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="content">
                            <p style="font-size: 18px;">Kedves ' . $user['username'] . ',</p>
                            <p>Kattints az alábbi gombra a jelszavad megváltoztatásához:</p>
                            <a class="button" href="' . $activation_link . '">Jelszó megváltoztatása</a>
                            <p style="margin-top: 20px;">A link 24 órán belül érvényes.</p>
                            <p>Üdvözlettel,<br>PET Adoption Csapat</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="footer">
                            <p>Ha bármilyen kérdésed van, kérlek lépj kapcsolatba velünk.</p>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

            // E-mail küldése a sendEmail() függvénnyel, amit a mailer.php-ban definiáltak
            if (sendEmail($email, $subject, $body)) {
                $_SESSION['success_message'] = "Jelszó visszaállító link elküldve az e-mail címre.";
            } else {
                $_SESSION['error_message'] = "Nem sikerült elküldeni a jelszó visszaállító e-mailt. Kérlek próbáld újra később.";
            }
        } else {
            // Ha nem találtunk felhasználót az adott e-mail cím alapján
            $_SESSION['error_message'] = "Az e-mail cím nem található. Kérlek adj meg egy érvényes e-mail címet.";
        }
    } catch (PDOException $e) {
        // Adatbázis hiba esetén kiírjuk a hibaüzenetet
        die("Adatbázis kapcsolat sikertelen: " . $e->getMessage());
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
    <link rel="stylesheet" href="css/forgot_password.css"> <!-- CSS fájl betöltése -->
</head>
<body>
    
    <div class="form-container">
        <img src="images/logo.png" alt="PET Logo">
        <h2><i class="fas fa-unlock-alt"></i> Jelszó visszaállítása</h2>
        
        <!-- Error message form the PHP code -->
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
        
        <!-- Password reset page -->
        <form action="forgot_password.php" method="post">
            <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
            <input type="email" name="email" id="email" required>
            <input type="submit" value="Jelszó visszaállítás">
        </form>
        
        <p>Emlékszel a jelszavadra? <a href="login.php">Jelentkezz be itt</a>.</p>
    </div>

    <!-- JS code for toast messages -->
    <script>
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.classList.add('toast', type);
            toast.innerText = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);

        }
    </script>
</body>
</html>
