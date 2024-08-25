<?php
session_start();
require_once 'db_config.php';

// Redirecting to login page if we arent logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// If it's Post request we handle the deletion with AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_message') {
    $message_id = $_POST['message_id'];
    try {
        // Deleting the message form the database
        $sql_delete = "DELETE FROM messages WHERE message_id = ?";
        $stmt = $pdo->prepare($sql_delete);
        $stmt->execute([$message_id]);
        echo json_encode(['status' => 'success', 'message' => 'Üzenet sikeresen törölve.']);
        exit();
    } catch (PDOException $e) {
        // Error handling if we couldn't delete the message
        echo json_encode(['status' => 'error', 'message' => 'Hiba történt az üzenet törlése közben: ' . $e->getMessage()]);
        exit();
    }
}

// Checking incoming messages
try { 
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT messages.message_id, messages.sender_id, messages.receiver_id, messages.message_text, messages.sent_at, users.username AS sender_name
            FROM messages 
            INNER JOIN users ON messages.sender_id = users.user_id
            WHERE messages.receiver_id = ? 
            ORDER BY messages.sent_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { // Error handling if you can't send the message
    $_SESSION['error_message'] = "Hiba történt az üzenetek lekérése közben: " . $e->getMessage();
    header("Location: index.php"); // Redirecting to the index page
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLeet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/messages.css" rel="stylesheet">
    <script src="js/messages.js"></script>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="container mt-5" style="margin-bottom: 600px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Beérkező üzenetek</h2>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <div class="list-group">
                <?php foreach ($messages as $message): ?>
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($message['sender_name']); ?></h5>
                            <small><?php echo date('Y-m-d H:i:s', strtotime($message['sent_at'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($message['message_text']); ?></p>
                        <button class="btn btn-danger btn-sm delete-message" data-message-id="<?php echo $message['message_id']; ?>">Törlés</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11;"></div>

<?php include 'footer.php'; ?>

</body>
</html>
