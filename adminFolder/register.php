<?php
require_once 'dbForLogin/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href = 'signup.php';</script>";
        exit();
    }

    // Securely hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if admin_id already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_id = ?");
        $checkStmt->execute([$admin_id]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo "<script>alert('Admin ID already exists. Please choose another.'); window.location.href = 'signup.php';</script>";
        } else {
            // Insert new admin
            $stmt = $pdo->prepare("INSERT INTO admins (admin_id, password) VALUES (?, ?)");
            if ($stmt->execute([$admin_id, $hashed_password])) {
                echo "<script>
                        alert('Registration successful! You can now login.');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            }
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: signup.php");
    exit();
}
?>