<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Admin Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <img src="images/LibroSys.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-container">
        <div class="logo-area">
            <h1>LibroSys</h1>
            <p>Create a New Administrator Account</p>
        </div>

        <div class="login-card">
            <h2>Sign Up</h2>
            <form action="register.php" method="POST">
                <div class="input-group">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" id="admin_id" name="admin_id" placeholder="Create Admin ID" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create Password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat Password" required>
                </div>

                <button type="submit" class="login-btn">Register Admin</button>
            </form>
            <div style="margin-top: 20px; text-align: center;">
                <a href="login.php" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>