<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php"); // Redirection if not logged into an admin account
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <title>PET Adoption</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/admin_navbar.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #FCF8ED;">
    <div class="container">
        <a class="navbar-brand" href="index.php">
        <img src="../images/logo.png" alt="Állatok Örökbefogadása" style="border-radius: 10px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                <a class="nav-link" href="manage_animals.php">Kiskedvencek</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="manage_users.php">Felhasználók</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="manage_categories.php">Állat kategóriák</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="manage_breeds.php">Állat fajták</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="admin_logout.php">Kijelentkezés</a>
                </li>

            </ul>
        </div>
        
    </div>
</nav>


<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
