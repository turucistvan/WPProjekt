<?php
session_start();
require_once 'db_config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if (isset($_SESSION['activation_token']) && $_SESSION['activation_token'] === $token) {
        $username = $_SESSION['username'];
        $email = $_SESSION['email'];
        $password = $_SESSION['password'];
        $first_name = $_SESSION['first_name'];
        $last_name = $_SESSION['last_name'];

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
        $user_id = $pdo->lastInsertId();

        unset($_SESSION['activation_token']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['password']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);

        echo '
        <!DOCTYPE html>
        <html lang="hu">
        <head>
            <meta charset="UTF-8">
            <title>PetLeet</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background: linear-gradient(135deg, #71b7e6, #9b59b6);
                }

                .success-container {
                    background: #fff;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                    max-width: 400px;
                    width: 100%;
                    text-align: center;
                }

                .success-container h2 {
                    margin-bottom: 20px;
                    color: #333;
                }

                .success-container p {
                    margin: 15px 0;
                }

                .success-container a {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #5cb85c;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    transition: background 0.3s;
                }

                .success-container a:hover {
                    background-color: #4cae4c;
                }
            </style>
        </head>
        <body>
            <div class="success-container">
                <h2>Sikeres aktiválás</h2>
                <p>A fiókod sikeresen aktiválva lett.</p>
                <a href="https://leet.stud.vts.su.ac.rs/login.php">Vissza a bejelentkezéshez</a>
            </div>
        </body>
        </html>';
        exit();
    } else {
        echo '
        <!DOCTYPE html>
        <html lang="hu">
        <head>
            <meta charset="UTF-8">
            <title>PET Adoption</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background: linear-gradient(135deg, #71b7e6, #9b59b6);
                }

                .error-container {
                    background: #fff;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                    max-width: 400px;
                    width: 100%;
                    text-align: center;
                }

                .error-container h2 {
                    margin-bottom: 20px;
                    color: #333;
                }

                .error-container p {
                    margin: 15px 0;
                }

                .error-container a {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #e74c3c;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    transition: background 0.3s;
                }

                .error-container a:hover {
                    background-color: #c73e2c;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h2>Hibás aktiválás</h2>
                <p>Az aktiváló link érvénytelen vagy lejárt.</p>
                <a href="https://leet.stud.vts.su.ac.rs/register.php">Vissza a regisztrációhoz</a>
            </div>
        </body>
        </html>';
        exit();
    }
} else {
    echo '
    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <title>Hibás aktiválás</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: linear-gradient(135deg, #71b7e6, #9b59b6);
            }

            .error-container {
                background: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                max-width: 400px;
                width: 100%;
                text-align: center;
            }

            .error-container h2 {
                margin-bottom: 20px;
                color: #333;
            }

            .error-container p {
                margin: 15px 0;
            }

            .error-container a {
                display: inline-block;
                padding: 10px 20px;
                background-color: #e74c3c;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
                transition: background 0.3s;
            }

            .error-container a:hover {
                background-color: #c73e2c;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h2>Hibás aktiválás</h2>
            <p>Az aktiváló link nem található.</p>
            <a href="https://leet.stud.vts.su.ac.rs/register.php">Vissza a regisztrációhoz</a>
        </div>
    </body>
    </html>';
    exit();
}
?>
