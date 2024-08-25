<?php
// adoptions.php

// Include the database configuration file
require_once 'db_config.php';

// Start session to check login status
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID of the currently logged in user
$user_id = $_SESSION['user_id'];

// Query to fetch adopted animals for the current user
$sql = "SELECT animals.animal_id, animals.name AS animal_name, categories.name AS category_name, breeds.name AS breed_name
        FROM adoptions
        INNER JOIN animals ON adoptions.animal_id = animals.animal_id
        INNER JOIN breeds ON animals.breed_id = breeds.breed_id
        INNER JOIN categories ON animals.category_id = categories.category_id
        WHERE adoptions.user_id = ?
        ORDER BY adoptions.adoption_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$adoptions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopted Animals</title>
    <link href="css/adoptions.css" rel="stylesheet">
</head>
<body>
<?php include 'navigation.php'; ?>
<h2 style="padding-left: 20px;background-color:white;">Örökbefogadott állatok</h2><br>

<table>
    <thead>
        <tr>
            <th>Állat neve</th>
            <th>Kategória</th>
            <th>Fajta</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($adoptions as $adoption): ?>
        <tr>
            <td><?php echo htmlspecialchars($adoption['animal_name']); ?></td>
            <td><?php echo htmlspecialchars($adoption['category_name']); ?></td>
            <td><?php echo htmlspecialchars($adoption['breed_name']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include 'footer.php'; ?>
</body>
</html>
