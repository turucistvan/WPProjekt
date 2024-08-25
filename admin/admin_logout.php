<?php
session_start();
session_destroy(); // Session destroy, closing the workflow
header("Location: admin_login.php"); // Redirection to login page
exit();
?>
