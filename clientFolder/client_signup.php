<?php
session_start();
require_once '../dbForLogin/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['user_password'];
    $confirm_password = $_POST['confirm_user_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href = 'client_signup.php';</script>";
        exit();
    }

    // Securely hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate a unique User ID
    // This generates a unique ID based on the current time in microseconds.
    // We prepend 'USR-' for better readability and identification.
    $user_id = 'USR-' . uniqid(); 

    try {
        // Check if the generated User ID already exists (highly unlikely with uniqid, but good practice)
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
        $checkStmt->execute([$user_id]);
        
        if ($checkStmt->fetchColumn() > 0) {
            // If by some rare chance it exists, try again or log an error
            echo "<script>alert('Failed to generate a unique User ID. Please try again.'); window.location.href = 'client_signup.php';</script>";
        } else {
            // Insert new user into the users table with default credit score of 10
            $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, password, credit_score) VALUES (?, ?, ?, ?, 10)");
            if ($stmt->execute([$user_id, $name, $email, $hashed_password])) {
                // Automatically log the user in after registration
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $pdo->lastInsertId(); // Store the integer PK for database relations
                $_SESSION['user_name'] = $name;

                echo "<script>alert('Registration successful! Your unique Login ID is: " . $user_id . ". Please save this ID for future logins. Redirecting to home...'); window.location.href = 'home.php';</script>";
                exit();
            }
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - User Registration</title>
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
            <p>Create a New Account</p>
        </div>

        <div class="login-card">
            <h2>Sign Up</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="user_password">Password</label>
                    <input type="password" id="user_password" name="user_password" placeholder="Create Password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_user_password" name="confirm_user_password" placeholder="Repeat Password" required>
                </div>

                <button type="submit" class="login-btn">Register</button>
            </form>
            <div style="margin-top: 20px; text-align: center;">
                <a href="client_login.php" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>