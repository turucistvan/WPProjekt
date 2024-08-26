<?php
require_once '../db_config.php'; 
include 'admin_navbar.php';

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adding a breed
    if (isset($_POST['add_breed'])) {
        $breed_name = $_POST['breed_name'];
        $category_id = $_POST['category_id'];
        
        $insert_sql = "INSERT INTO breeds (name, category_id) VALUES (?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$breed_name, $category_id]);
        
        // Send feedback
        echo "Fajta sikeresen hozzáadva.";
        exit;
    }

    // Deleting a breed
    if (isset($_POST['delete_breed'])) {
        $breed_id = $_POST['breed_id'];
        
        $delete_sql = "DELETE FROM breeds WHERE breed_id=?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$breed_id]);
        
        // Send feedback
        echo "Fajta sikeresen törölve.";
        exit;
    }
}

// Retrieve all breeds
$sql = "SELECT * FROM breeds";
$stmt = $pdo->query($sql);

// Retrieve all categories (for breed selection)
$category_sql = "SELECT * FROM categories";
$category_stmt = $pdo->query($category_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PetLeet</title>
    <link href="css/manage_breeds.css" rel="stylesheet"> <!-- Egyedi stíluslap betöltése -->
</head>
<body>
    <div class="manage-breeds">
    <h2>Fajták kezelése</h2>

    <!-- Fajta hozzáadása űrlap -->
    <form method="POST">

        <input type="text" id="breed_name" name="breed_name" required>

        <select id="category_id" name="category_id" required>
            <?php while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $row['category_id']; ?>"><?php echo $row['name']; ?></option>
            <?php } ?>
        </select>
        <button type="submit" name="add_breed">Fajta hozzáadása</button>
    </form>

    <!-- Fajták listázása és törlése -->
    <ul>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
            <li>
                <?php echo $row['name']; ?>
                <form method="POST">
                    <input type="hidden" name="breed_id" value="<?php echo $row['breed_id']; ?>">
                    <button class="delete-btn" type="submit" name="delete_breed">Fajta törlése</button>
                </form>
            </li>
        <?php } ?>
    </ul>
    </div>

</body>
</html>

<?php
$pdo = null; // Adatbázis kapcsolat lezárása
?>
