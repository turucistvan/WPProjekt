<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sending the message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message_text = $_POST['message_text'];

    try {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sender_id, $receiver_id, $message_text]);
        
        $_SESSION['success_message'] = "Az üzenet elküldve.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Hiba történt az üzenet küldése közben: " . $e->getMessage();
    }
}


try {
    $sql = "SELECT user_id, username FROM users WHERE user_id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Hiba történt a felhasználók lekérése közben: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üzenet küldése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/compose_message.css">
    <script src="js/compose_message.js"></script>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="container mt-5" style="margin-bottom: 200px;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Üzenet küldése</h2>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_message'])): ?>
                <!-- Start of toast message-->
                <div class="toast-container">
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?php echo $_SESSION['success_message']; ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                <!-- End of toast message  -->
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="receiver_id" class="form-label">Címzett</label>
                    <select name="receiver_id" id="receiver_id" class="form-select" required>
                        <option value="">Válassz címzettet...</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo $user['username']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="message_text" class="form-label">Üzenet</label>
                    <textarea class="form-control" id="message_text" name="message_text" rows="5" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-primary">Küldés</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
