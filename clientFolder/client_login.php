<?php
session_start();
require_once '../dbForLogin/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = $_POST['user_id']; // This could be Email or User ID
    $password = $_POST['user_password'];

    try {
        // Allow login via the generated User ID or the Email address
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? OR email = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Success! Create session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('Invalid ID/Email or Password.'); window.location.href = 'client_login.php';</script>";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - User Login</title>
    <link rel="stylesheet" href="clientstyle.css">
</head>
<body class="auth-page">

    <img src="../images/library-background.png" alt="Library Background" class="bg-image">

    <header class="main-header">
        <div class="header-content">
            <img src="../images/LibroSys.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-container">
        <div class="logo-area">
            <h1>LibroSys</h1>
            <p>Library Management System</p>
        </div>

        <div class="login-card">
            <h2>User Login</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="user_id">User ID or Email</label>
                    <input type="text" id="user_id" name="user_id" placeholder="Enter your ID or Email" required>
                </div>
                
                <div class="input-group">
                    <label for="user_password">Password</label>
                    <input type="password" id="user_password" name="user_password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
            <div style="margin-top: 20px; text-align: center;">
                <a href="client_signup.php" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Create Account</a>
            </div>
        </div>
    </div>


</body>
</html>
