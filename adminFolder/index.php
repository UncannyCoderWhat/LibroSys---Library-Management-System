<?php
session_start();

// Centralized entry point for the admin side
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>