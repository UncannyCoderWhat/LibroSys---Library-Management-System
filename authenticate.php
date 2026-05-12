<?php
session_start();
require_once 'dbForLogin/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];

    try {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $password === $admin['password']) {
            // Success! Create a session for the user
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $admin['admin_id'];

            header("Location: dashboard.php");
            exit();
        } else {

            echo "<script>
                    alert('Invalid Admin ID or Password. Please try again.');
                    window.location.href = 'login.php';
                  </script>";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}
?>