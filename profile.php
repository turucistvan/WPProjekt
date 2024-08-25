<?php

require_once 'db_config.php';

// Including navbar
include 'navigation.php';

if (!isset($_SESSION['user_id'])) {
    // If we arent logged in we get redirected to login page
    header("Location: login.php");
    exit();
}

// Getting user data
$userId = $_SESSION['user_id'];
$userDataSql = "SELECT username, first_name, last_name, phone_number FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($userDataSql);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$toastMessage = '';

// If we get a POST request we start dealing with password and profile changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dealing with password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Check for the new password 
        if ($newPassword !== $confirmPassword) {
            $toastMessage = 'Az új jelszó és megerősítése nem egyezik meg!';
        } else {
            // Check for the current password
            $checkPasswordSql = "SELECT password_hash FROM users WHERE user_id = :user_id";
            $stmt = $pdo->prepare($checkPasswordSql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $hashedPassword = $stmt->fetchColumn();

            if (password_verify($currentPassword, $hashedPassword)) {
                // If the current password IS correct, we hash the NEW password with byCrypt
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':password_hash', $hashedNewPassword, PDO::PARAM_STR);
                $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $updateStmt->execute();
                $toastMessage = 'A jelszó sikeresen megváltoztatva!';
            } else {
                $toastMessage = 'A jelenlegi jelszó helytelen!';
            }
        }
    }

    // Updating profile data
    if (isset($_POST['update_profile'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $phoneNumber = $_POST['phone_number'];

        $updateProfileSql = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone_number = :phone_number WHERE user_id = :user_id";
        $stmt = $pdo->prepare($updateProfileSql);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $toastMessage = 'Profil adatok sikeresen frissítve!';

        // Updating userData with the new
        $userData['first_name'] = $firstName;
        $userData['last_name'] = $lastName;
        $userData['phone_number'] = $phoneNumber;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLeet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/profile.css" rel="stylesheet">
    <!-- Meta tags for no caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>

<div class="container mt-4 profile-container">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($userData['username']); ?></h5>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Profil adatok frissítése</h5><br>
            <form class="profile-form" method="post">
                <div class="mb-3">
                    <label for="first_name" class="form-label">Keresztnév</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Vezetéknév</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Telefonszám</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                </div>
                <button type="submit" class="btn btn-primary" name="update_profile">Profil frissítése</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Jelszóváltoztatás</h5><br>
            <form class="profile-form" method="post">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Jelenlegi jelszó</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">Új jelszó</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Új jelszó megerősítése</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary" name="change_password">Jelszó változtatása</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Toast message on succcess -->
<div class="toast-container">
    <div id="toastMessage" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?php echo htmlspecialchars($toastMessage); ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Showing toast message with bootstrap javascript
    document.addEventListener('DOMContentLoaded', function () {
        var toastMessage = '<?php echo $toastMessage; ?>';
        if (toastMessage) {
            var toastElement = document.getElementById('toastMessage');
            var toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    });
</script>
</body>
</html>
