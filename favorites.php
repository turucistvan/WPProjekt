<?php
// favorites.php

require_once 'db_config.php';
session_start();

// Check for logged in
if (!isset($_SESSION['user_id'])) {
    // If the user isn't logged in they get redirected to login page
    header("Location: login.php");
    exit();
}

// Getting the ID of the user
$user_id = $_SESSION['user_id'];

// Ajax request for delete and adding favourites
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'remove_animal' && isset($_POST['animal_id'])) {
        // Delete logic
        $animal_id = $_POST['animal_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM favorite_animals WHERE user_id = :user_id AND animal_id = :animal_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt->execute();

            $response = ['success' => true, 'message' => 'Állat sikeresen eltávolítva a kedvencek közül'];
            echo json_encode($response);
            exit();
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Hiba az adatbázis művelet során: ' . $e->getMessage()];
            echo json_encode($response);
            exit();
        }
    } elseif ($_POST['action'] == 'submit_adoption_request' && isset($_POST['animal_id'])) {
        // Adoption request
        $animal_id = $_POST['animal_id'];

        try {
            $stmt_check_owner = $pdo->prepare("SELECT user_id FROM animals WHERE animal_id = :animal_id");
            $stmt_check_owner->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
            $stmt_check_owner->execute();
            $animal_owner_id = $stmt_check_owner->fetchColumn();

            if ($animal_owner_id == $user_id) {
                $response = ['success' => false, 'message' => 'Nem fogadhatja örökbe saját hirdetett állatát.'];
                echo json_encode($response);
                exit();
            } else {
                // If it's not our own animal we get to adopt it
                $request_date = date('Y-m-d');

                $stmt = $pdo->prepare("INSERT INTO adoption_requests (user_id, animal_id, request_date) VALUES (:user_id, :animal_id, :request_date)");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
                $stmt->bindParam(':request_date', $request_date, PDO::PARAM_STR);
                $stmt->execute();

                $response = ['success' => true, 'message' => 'Örökbefogadási kérelem sikeresen elküldve'];
                echo json_encode($response);
                exit();
            }
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Hiba az adatbázis művelet során: ' . $e->getMessage()];
            echo json_encode($response);
            exit();
        }
    }
}

// SQL query for the favourite animals
try {
    // PDO object creation with an already exisisting $pdo
    $stmt = $pdo->prepare("SELECT animals.animal_id, animals.name, animals.description, animals.age, animals.image, users.username as owner_name
                       FROM favorite_animals 
                       INNER JOIN animals ON favorite_animals.animal_id = animals.animal_id 
                       INNER JOIN users ON animals.user_id = users.user_id
                       WHERE favorite_animals.user_id = :user_id");

    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $favorite_animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <link href="css/favorites.css rel="stylesheet">
  <script src="js/favorites.js"></script>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="container mt-4" style="margin-bottom: 800px;">
  <?php if (isset($_SESSION['message'])): ?>
    
    <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?php echo $_SESSION['message']; ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <div class="row justify-content-center" id="animal-container">
    <?php if (!empty($favorite_animals)): ?>
      <?php foreach ($favorite_animals as $animal): ?>
        <div class="col-md-4 mb-4" id="animal-card-<?php echo $animal['animal_id']; ?>">
          <div class="card">
            <img src="<?php echo htmlspecialchars($animal['image']); ?>" class="card-img-top photo" alt="<?php echo htmlspecialchars($animal['name']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($animal['name']); ?></h5>
              <p class="card-text"><?php echo htmlspecialchars($animal['description']); ?></p>
              <p class="card-text">Életkor: <?php echo htmlspecialchars($animal['age']); ?> év</p>
              <p class="card-text"><strong>Hirdető: <?php echo htmlspecialchars($animal['owner_name']); ?></strong></p>
              <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-remove me-2" onclick="removeAnimal(<?php echo $animal['animal_id']; ?>)">
                  Törlés
                </button>
                <button type="button" width="30" height="30" class="btn btn-adopt" onclick="submitAdoptionRequest(<?php echo $animal['animal_id']; ?>)">
                  Örökbefogadás kérelem küldés
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p><h2>Nincsenek kedvenc állataid.<h2></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'footer.php'; ?>



<div class="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>

</body>
</html>
