<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <img src="images/library-background.png" alt="Library Background" class="bg-image">

    <header class="main-header">
        <div class="header-content">
            <img src="images/LibroSys.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-container">
        <div class="logo-area">
            <h1>LibroSys</h1>
            <p>Library Management System</p>
        </div>

        <div class="login-card">
            <h2>Admin Login</h2>
            <form action="authenticate.php" method="POST">
                <div class="input-group">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" id="admin_id" name="admin_id" placeholder="Enter your ID" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
            <div style="margin-top: 20px; text-align: center;">
                <a href="register.php" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Admin Registration</a>
            </div>
        </div>
    </div>


</body>
</html>
