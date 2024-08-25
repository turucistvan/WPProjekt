<?php

require_once 'db_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST requests (edit or delete pet)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'edit_pet' && isset($_POST['animal_id'])) {
        // Edit pet
        $animal_id = $_POST['animal_id'];
        $animal_data = explode(',', $_POST['animal_data']);

        if (count($animal_data) != 4) {
            $response = ['success' => false, 'message' => 'Hibás állat adatok'];
            echo json_encode($response);
            exit();
        }

        // Prepare data
        $name = trim($animal_data[0]);
        $description = trim($animal_data[1]);
        $age = intval(trim($animal_data[2]));
        $gender = trim($animal_data[3]);

        // Validate age
        if ($age <= 0) {
            $response = ['success' => false, 'message' => 'Életkor értéke nem megfelelő'];
            echo json_encode($response);
            exit();
        }

        // Update pet in the database
        try {
            $stmt = $pdo->prepare("UPDATE animals SET name = :name, description = :description, age = :age, gender = :gender WHERE animal_id = :animal_id AND user_id = :user_id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT);
            $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
            $stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $response = ['success' => true, 'message' => 'Állat sikeresen szerkesztve'];
            echo json_encode($response);
            exit();
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Hiba az adatbázis művelet során: ' . $e->getMessage()];
            echo json_encode($response);
            exit();
        }
    } elseif ($_POST['action'] == 'delete_pet' && isset($_POST['animal_id'])) {
        // Delete pet
        $animal_id = $_POST['animal_id'];

        try {
            // Delete from animals table
            $stmt_delete_animal = $pdo->prepare("DELETE FROM animals WHERE animal_id = :animal_id AND user_id = :user_id");
            $stmt_delete_animal->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt_delete_animal->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_delete_animal->execute();

            // Delete from favorite_animals table
            $stmt_delete_favorite = $pdo->prepare("DELETE FROM favorite_animals WHERE animal_id = :animal_id");
            $stmt_delete_favorite->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt_delete_favorite->execute();

            // Delete from adoptions table
            $stmt_delete_adoption = $pdo->prepare("DELETE FROM adoptions WHERE animal_id = :animal_id");
            $stmt_delete_adoption->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt_delete_adoption->execute();

            $response = ['success' => true, 'message' => 'Állat sikeresen törölve'];
            echo json_encode($response);
            exit();
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Hiba az adatbázis művelet során: ' . $e->getMessage()];
            echo json_encode($response);
            exit();
        }
    }
}

// Fetch animals from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM animals WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hiba az adatbázis lekérdezés során: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>PetLeet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="css/manage_animals.css" rel="stylesheet">
  <script src="js/manage_animals.js"></script>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="container mt-4" style="margin-bottom:600px;">
  <div class="row justify-content-center">
    <?php if (!empty($animals)): ?>
      <?php foreach ($animals as $animal): ?>
        <!-- Pet card -->
        <div class="col-md-6 mb-4">
          <div class="card">
            <img src="<?php echo htmlspecialchars($animal['image']); ?>" class="card-img-top photo" alt="<?php echo htmlspecialchars($animal['name']); ?>">
            <div class="card-body" id="animal-card-body-<?php echo $animal['animal_id']; ?>">
              <h5 class="card-title" id="animal-name-<?php echo $animal['animal_id']; ?>"><?php echo htmlspecialchars($animal['name']); ?></h5>
              <p class="card-text" id="animal-description-<?php echo $animal['animal_id']; ?>"><?php echo htmlspecialchars($animal['description']); ?></p>
              <p class="card-text">Életkor: <span id="animal-age-<?php echo $animal['animal_id']; ?>"><?php echo htmlspecialchars($animal['age']); ?></span> év</p>
              <p class="card-text">Nem: <span id="animal-gender-<?php echo $animal['animal_id']; ?>"><?php echo htmlspecialchars($animal['gender']); ?></span></p>
              <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-edit me-2" onclick="editPet(<?php echo $animal['animal_id']; ?>)">
                  Szerkesztés
                </button>
                <button type="button" class="btn btn-delete" onclick="deletePet(<?php echo $animal['animal_id']; ?>)">
                  Törlés
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- No pets message -->
      <div class="col-12 text-center">
        <p><h2>Nincsenek hirdetett állataid.<h2></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
