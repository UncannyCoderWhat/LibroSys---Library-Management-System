<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LibroSys Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <img src="LibroSys.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-card">
        <h2>ADMIN LOGIN</h2>
        <form action="authenticate.php" method="POST">
            <div class="input-row">
                <label>Admin ID</label>
                <input type="text" name="admin_id" required>
            </div>
            <div class="input-row">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">LOGIN</button>
        </form>
    </div>

</body>
</html>
