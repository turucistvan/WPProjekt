<?php
require_once '../db_config.php'; // Adatbázis kapcsolat inicializálása
include 'admin_navbar.php'; // Adminisztrációs menü betöltése

// Start of POST 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if we recievied an ajax request or not
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete_user':
                    if (isset($_POST['user_id'])) {
                        $user_id = $_POST['user_id'];

                        // Deletion from the adoption table
                        $delete_adoptions_sql = "DELETE FROM adoptions WHERE user_id=?";
                        $delete_adoptions_stmt = $pdo->prepare($delete_adoptions_sql);
                        $delete_adoptions_stmt->execute([$user_id]);

                        // Deletion frop the adoption_requests table
                        $delete_adoption_requests_sql = "DELETE FROM adoption_requests WHERE user_id=?";
                        $delete_adoption_requests_stmt = $pdo->prepare($delete_adoption_requests_sql);
                        $delete_adoption_requests_stmt->execute([$user_id]);

                        // Deletion from the favorites table
                        $delete_favorites_sql = "DELETE FROM favorite_animals WHERE user_id=?";
                        $delete_favorites_stmt = $pdo->prepare($delete_favorites_sql);
                        $delete_favorites_stmt->execute([$user_id]);

                        // Deletion from the animals table
                        $delete_animals_sql = "DELETE FROM animals WHERE user_id=?";
                        $delete_animals_stmt = $pdo->prepare($delete_animals_sql);
                        $delete_animals_stmt->execute([$user_id]);

                        // Deletion from the password reset table
                        $delete_password_reset_sql = "DELETE FROM password_reset_requests WHERE user_id=?";
                        $delete_password_reset_stmt = $pdo->prepare($delete_password_reset_sql);
                        $delete_password_reset_stmt->execute([$user_id]);

                        // Deletion from the messages table
                        $delete_messages_sql = "DELETE FROM messages WHERE sender_id=? OR receiver_id=?";
                        $delete_messages_stmt = $pdo->prepare($delete_messages_sql);
                        $delete_messages_stmt->execute([$user_id, $user_id]);

                        // Deletion from the users table
                        $delete_user_sql = "DELETE FROM users WHERE user_id=?";
                        $delete_user_stmt = $pdo->prepare($delete_user_sql);
                        $delete_user_stmt->execute([$user_id]);

                        echo "success";
                    } else {
                        echo "error";
                    }
                    break;

                default:
                    echo "error";
                    break;
            }
            exit; // End of ajax request
        }
    }
}

// Getting all the users from the table
$sql = "SELECT * FROM users WHERE is_admin = 0"; // except the admin
$stmt = $pdo->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="css/manage_users.css" rel="stylesheet"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/manage_users.js"></script> 
</head>
<body>
    <div class="container">
        <h2>Felhasználók kezelése</h2>

        <table>
            <thead>
                <tr>
                    <th>Felhasználó ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Vezetéknév</th>
                    <th>Keresztnév</th>
                    <th>Telefonszám</th>
                    <th>Admin</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['first_name']; ?></td>
                        <td><?php echo $row['last_name']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td><?php echo $row['is_admin'] ? 'Igen' : 'Nem'; ?></td>
                        <td>
                            <button onclick="deleteUser(<?php echo $row['user_id']; ?>)">Felhasználó törlése</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$pdo = null;
?>
