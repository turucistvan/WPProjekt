<?php
session_start(); // Session start

// Database connection
require_once 'db_config.php';

// Search engine 
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    // SQL query for the search bar
    $sql = "SELECT a.animal_id, a.name AS animal_name, a.description, a.image, a.age, a.gender, u.username AS advertiser, c.name AS category_name, b.name AS breed_name 
        FROM animals a
        INNER JOIN users u ON a.user_id = u.user_id
        INNER JOIN categories c ON a.category_id = c.category_id
        INNER JOIN breeds b ON a.breed_id = b.breed_id
        WHERE a.name LIKE :search_term OR a.description LIKE :search_term OR u.username LIKE :search_term";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search_term', '%' . $search_term . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 

// Check for the Star button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    if (!isset($_SESSION['user_id'])) { // Checking if the user is logged in
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $animal_id = $_POST['animal_id'];

    
    $check_query = "SELECT * FROM favorite_animals WHERE user_id = :user_id AND animal_id = :animal_id"; // Check to see if the animal isn't already starred
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Binds the user ID
    $check_stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT); // Binds the animal ID
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 0) { // If the check says it isn't there, it gets added
        $insert_query = "INSERT INTO favorite_animals (user_id, animal_id) VALUES (:user_id, :animal_id)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Binds the user ID
        $insert_stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT); // Binds the animal ID
        
        if ($insert_stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Az állatot hozzáadták kedvenceihez!' // Json message when it's successful
            ];
            echo json_encode($response);
            exit(); 

        } else {
            $response = [
                'status' => 'error',
                'message' => 'Hiba történt a kedvenc állat hozzáadásakor.' // Json messaqge when it's unsuccessful 
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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLeet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/search_results.css" rel="stylesheet">
</head>

<body>
<?php include 'navigation.php'; ?>
<div class="containerList">
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <?php
        if (isset($results) && count($results) > 0) {
            foreach ($results as $row) {
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-img-container">
                            <img class="photo" src="<?php echo htmlspecialchars($row['image']); ?>" alt="...">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['animal_name']); ?></h5>
                            <p class="card-text flex-grow-1">
                                <strong>Kategória:</strong> <?php echo htmlspecialchars($row['category_name']); ?><br>
                                <strong>Fajta:</strong> <?php echo htmlspecialchars($row['breed_name']); ?><br>
                                <strong>Kor:</strong> <?php echo htmlspecialchars($row['age']); ?> év<br>
                                <strong>Nem:</strong> <?php echo htmlspecialchars($row['gender']); ?><br>
                                <strong>Hirdető:</strong> <?php echo htmlspecialchars($row['advertiser']); ?><br>
                                <strong>Leírás:</strong><br>
                                <?php echo htmlspecialchars($row['description']); ?><br>
                            </p>

                            
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="like-form">
                                <input type="hidden" name="animal_id" value="<?php echo $row['animal_id']; ?>">
                                <button type="submit" name="like" class="favorite-btn">
                                    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                                    </svg>
                                     Kedvenc
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {  // If no results are found, display a message
            echo '<div class="col-12 text-center">';
            echo '<p><h2>Nincs találat<h2></p>';
            echo '</div>';
        }
        ?>
    </div>
</div>
</div>
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
    const toastContainer = document.getElementById('toast-container');
    const likeForms = document.querySelectorAll('.like-form');

    likeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Login check
            const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if (!isUserLoggedIn) {
                window.location.href = 'login.php'; // Redirects to the login page if the user is not logged in
                return;
            }

            const animalId = this.querySelector('input[name="animal_id"]').value;

            // Send an AJAX request to like the animal
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        // Displays a success toast message
                        const toast = document.createElement('div');
                        toast.classList.add('toast', 'show', 'bg-success', 'text-white');
                        toast.innerHTML = '<div class="toast-header"><strong class="me-auto">Kedvenc hozzáadva</strong></div><div class="toast-body">' + response.message + '</div>';
                        toastContainer.appendChild(toast);
                        setTimeout(function() {
                            toast.remove();
                        }, 3000);
                    } else if (response.status === 'error') {
                        // Displays an error toast message
                        const toast = document.createElement('div');
                        toast.classList.add('toast', 'show', 'bg-danger', 'text-white');
                        toast.innerHTML = '<div class="toast-header"><strong class="me-auto">Hiba történt</strong></div><div class="toast-body">' + response.message + '</div>';
                        toastContainer.appendChild(toast);
                        setTimeout(function() {
                            toast.remove();
                        }, 2000);
                    }
                }
            };

            xhr.send('like=true&animal_id=' + encodeURIComponent(animalId));
        });
    });
});
</script>

<?php include 'footer.php'; ?>

</body>
</html>
