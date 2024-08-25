<?php
// db_config.php

$host = 'localhost'; // database host
$dbname = 'leet'; // database hostname
$username = 'leet'; // database username
$password = 'FRust4jPtoKs115'; // database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Pdo error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Hiba az adatbázis kapcsolódás során: " . $e->getMessage());
}
?>
