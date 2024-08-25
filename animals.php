<?php

// Database connection setup
require_once 'db_config.php';

// Handling filter parameters
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$breedFilter = isset($_GET['breed']) ? $_GET['breed'] : '';

// Default SQL query
$sql = "SELECT a.animal_id, a.name AS animal_name, a.description, a.image, a.age, a.gender, u.username AS owner_name, c.name AS category_name, b.name AS breed_name 
        FROM animals a
        INNER JOIN users u ON a.user_id = u.user_id
        INNER JOIN categories c ON a.category_id = c.category_id
        INNER JOIN breeds b ON a.breed_id = b.breed_id
        WHERE 1=1";

// Filter by category if provided
if (!empty($categoryFilter)) {
    $sql .= " AND a.category_id = :category_id";
}

// Filter by breed if provided
if (!empty($breedFilter)) {
    $sql .= " AND a.breed_id = :breed_id";
}

$sql .= " ORDER BY a.animal_id";

// Execute the query
try {
    $stmt = $pdo->prepare($sql);

     // Bind parameters based on category and breed
    if (!empty($categoryFilter)) {
        $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
    }

    if (!empty($breedFilter)) {
        $stmt->bindParam(':breed_id', $breedFilter, PDO::PARAM_INT);
    }

    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hiba a lekérdezés végrehajtása során: " . $e->getMessage());
}


// If the like button was pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    session_start();
    
    // Check if the user is logged in (e.g., using session management)
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $animal_id = $_POST['animal_id'];

    // Check if the animal is already added to favorites
    $check_query = "SELECT * FROM favorite_animals WHERE user_id = :user_id AND animal_id = :animal_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 0) {
         // If not in favorites yet, add to favorites
        $insert_query = "INSERT INTO favorite_animals (user_id, animal_id) VALUES (:user_id, :animal_id)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
        
        if ($insert_stmt->execute()) {
            // On successful addition, send a JSON response to the frontend
            $response = [
                'status' => 'success',
                'message' => 'Az állatot hozzáadták kedvenceihez!'
            ];
            echo json_encode($response);
            exit(); // Exit to prevent HTML code from being included in the response
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Hiba történt a kedvenc állat hozzáadásakor.'
            ];
            echo json_encode($response);
            exit();
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Ez az állat már a kedvencek között van.'
        ];
        echo json_encode($response);
        exit();
    }
}

// Login
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>PetLeet</title>
    <link href="css/animals.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <?php include 'navigation.php';?>
    <div class="containerList" style="margin-bottom:400px;">
        <h1>Örökbefogadható állataink</h1>

        <form method="GET" action="" id="filterForm">
            <label for="category">Kategória:</label>
            <select name="category" id="category">
                <option value="">Válasszon kategóriát</option>
                <?php
                $categoryQuery = "SELECT * FROM categories";
                $categoryStmt = $pdo->query($categoryQuery);
                $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($categories as $cat) {
                    $selected = ($cat['category_id'] == $categoryFilter) ? 'selected' : '';
                    echo "<option value=\"" . $cat['category_id'] . "\" $selected>" . $cat['name'] . "</option>";
                }
                ?>
            </select>

            <div id="breedContainer">
            </div>

            <button type="submit">Szűrés</button>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var categorySelect = document.getElementById('category');
            var breedContainer = document.getElementById('breedContainer');

            categorySelect.addEventListener('change', function() {
                var categoryId = this.value;
                if (categoryId) {
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'get_breeds.php?category_id=' + categoryId, true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                
                                breedContainer.innerHTML = xhr.responseText;
                            } else {
                                console.error('AJAX hiba: ' + xhr.status);
                            }
                        }
                    };
                    xhr.send();
                } else {
                    breedContainer.innerHTML = ''; 
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const toastContainer = document.getElementById('toast-container');
            const likeButtons = document.querySelectorAll('.favorite-btn');
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

            likeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!isLoggedIn) {
                        window.location.href = 'login.php';
                        return;
                    }

                    const animalId = this.parentNode.querySelector('input[name="animal_id"]').value;

                    // AJAX Request
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Showing toast messsage
                                const toast = document.createElement('div');
                                toast.classList.add('toast', 'show', 'bg-success', 'text-white');
                                toast.innerHTML = '<div class="toast-header"><strong class="me-auto">Kedvenc hozzáadva</strong></div><div class="toast-body">' + response.message + '</div>';
                                toastContainer.appendChild(toast);
                                setTimeout(function() {
                                    toast.remove();
                                }, 1000);
                            } else if (response.status === 'error') {
                                // Toast on error
                                const toast = document.createElement('div');
                                toast.classList.add('toast', 'show', 'bg-danger', 'text-white');
                                toast.innerHTML = '<div class="toast-header"><strong class="me-auto">Hiba történt</strong></div><div class="toast-body">' + response.message + '</div>';
                                toastContainer.appendChild(toast);
                                setTimeout(function() {
                                    toast.remove();
                                }, 1000);
                            }
                        }
                    };

                    xhr.send('like=true&animal_id=' + encodeURIComponent(animalId));
                });
            });
        });

        </script>

        <!-- Listing of animals -->
<ul>
    <?php foreach ($animals as $animal): ?>
        <li>
            <?php if (!empty($animal['image'])): ?>
                <img class="photo" src="<?php echo htmlspecialchars($animal['image']); ?>" class="card-img-top" alt="...">
            <?php else: ?>
                <p>Kép nem elérhető</p>
            <?php endif; ?>
            <h2><?= htmlspecialchars($animal['animal_name']) ?></h2>
            
            <p>Kor: <?= htmlspecialchars($animal['age']) ?> év</p>
            <p>Nem: <?= htmlspecialchars($animal['gender']) ?></p>
            <p>Leírás: <?= htmlspecialchars($animal['description']) ?></p>

            <!-- Show owner -->
            <p><strong>Hirdető: <?= htmlspecialchars($animal['owner_name']) ?></strong></p>

            <!-- Favourite button -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" name="animal_id" value="<?php echo htmlspecialchars($animal['animal_id']); ?>">
                <button type="submit" name="like" class="favorite-btn">
                 <img src="images/star.png" alt="Girl in a jacket"> 
                </button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

    </div>
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
    <?php include 'footer.php';?>
</body>
</html>
