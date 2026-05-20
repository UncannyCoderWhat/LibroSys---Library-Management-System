<?php
session_start();

// Centralized entry point for the client side
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: home.php");
} else {
    header("Location: client_login.php");
}
exit();
?>