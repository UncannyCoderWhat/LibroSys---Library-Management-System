<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - User Registration</title>
    <link rel="stylesheet" href="style.css">
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
                    <label for="user_id">User ID</label>
                    <input type="text" id="user_id" name="user_id" placeholder="Create User ID" required>
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