<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="top-header">
        <div class="logo-container">
            <img src="LibroSys.png" alt="LibroSys Logo" class="brand-logo">
        </div>
    </header>


    <div class="login-wrapper">
        <div class="login-card">
            <h2>ADMIN LOGIN</h2>
            
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" name="admin_id" id="admin_id" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                
                <button type="submit" name="login_btn" class="login-button">LOGIN</button>
            </form>

            <?php
            // Simple PHP Logic for processing login
            if (isset($_POST['login_btn'])) {
                $adminId = $_POST['admin_id'];
                $password = $_POST['password'];

                // Replace these with your database validation logic
                if ($adminId === "admin" && $password === "1234") {
                    // header("Location: dashboard.php");
                } else {
                    echo "<Invalid ID or Password.";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
