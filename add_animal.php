<?php
// Check if the user is logged in
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (empty($user_id)) {
    header("Location: login.php");
    exit; // Important to stop further code execution after the redirect
}

// Load database connection 
require_once 'db_config.php';


$errors = []; 
$success_message = ''; 

// Validate and process POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
       // Check if all required fields are received from the POST request
    $required_fields = ['category_id', 'breed_id', 'name', 'description', 'age', 'gender'];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "A(z) " . ucfirst($field) . " mező kitöltése kötelező.";
        }
    }

    // Optional image upload processing
    if (empty($errors)) {
        $category_id = $_POST['category_id'];
        $breed_id = $_POST['breed_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];

          // Optional image upload processing
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_name = $_FILES['image']['name'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_size = $_FILES['image']['size'];
            $image_type = $_FILES['image']['type'];

            
            $max_image_size = 2 * 1024 * 1024; // 2 MB = max image size

            if ($image_size > $max_image_size) {
                $errors[] = "A kép mérete túl nagy. Maximum 2MB lehet.";
            } else {
                // Uploading the picture to the server
                $uploads_dir = 'images/';
                $image_path = $uploads_dir . $image_name;

                if (!move_uploaded_file($image_tmp_name, $image_path)) {
                    $errors[] = "A kép feltöltése sikertelen.";
                }
            }
        }

        // If no errors we continue the upload to the database
        if (empty($errors)) {
            try {
    
                $stmt = $pdo->prepare("INSERT INTO animals (user_id, category_id, breed_id, name, description, age, image, gender) 
                                       VALUES (:user_id, :category_id, :breed_id, :name, :description, :age, :image, :gender)");

                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':breed_id', $breed_id, PDO::PARAM_INT);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':age', $age, PDO::PARAM_INT);
                $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
                $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);


                $stmt->execute();

                $success_message = "Az állathirdetés sikeresen hozzáadva az adatbázishoz.";

            } catch (PDOException $e) {
                // Adatbázis hiba esetén
                $errors[] = "Adatbázis hiba: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLeet</title>
    <link href="css/add_animal.css" rel="stylesheet">
    <script src="js/add_animal.js"></script>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <form class="add" method="post" enctype="multipart/form-data">
        <label for="category_id">Kategória:</label>
        <select name="category_id" id="category_id">
            
            <?php
            $categoryQuery = "SELECT * FROM categories";
            $categoryStmt = $pdo->query($categoryQuery);
            $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($categories as $cat) {
                $selected = ($cat['category_id'] == $categoryFilter) ? 'selected' : '';
                echo "<option value=\"" . $cat['category_id'] . "\" $selected>" . $cat['name'] . "</option>";
            } ?>
        </select>
        <br><br>

        <label for="breed_id">Fajta:</label>
<select name="breed_id" id="breed_id">
    <?php
    
    $default_category_id = 1; 
   
    $breedQuery = "SELECT * FROM breeds WHERE category_id = :category_id";
    $breedStmt = $pdo->prepare($breedQuery);
    $breedStmt->bindParam(':category_id', $default_category_id, PDO::PARAM_INT);
    $breedStmt->execute();
    $breeds = $breedStmt->fetchAll(PDO::FETCH_ASSOC);

    
    foreach ($breeds as $breed) {
        echo "<option value=\"" . $breed['breed_id'] . "\">" . $breed['name'] . "</option>";
    }
    ?>
</select>

        <br><br>

        <label for="name">Név:</label>
        <input type="text" id="name" name="name" required>
        <br><br>

        <label for="description">Leírás:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea>
        <br><br>

        <label for="age">Életkor:</label>
        <input type="number" id="age" name="age" required>
        <br><br>

        <label for="gender">Nem:</label>
        <select name="gender" id="gender">
            <option value="Male">Hím</option>
            <option value="Female">Nőstény</option>
            <option value="Unknown">Ismeretlen</option>
        </select>
        <br><br>

        <label for="image">Kép kiválasztása (opcionális):</label>
        <input type="file" id="image" name="image">
        <br><br>

        <button type="submit">Hirdetés feladása</button>
    </form>

    <?php include 'footer.php'; ?>
</body>
</html>
