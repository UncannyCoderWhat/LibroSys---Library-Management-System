<?php
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Admin Registration</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body class="auth-page">

    <img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">

    <header class="main-header">
        <div class="header-content">
            <img src="<?php echo $base_url; ?>/images/LibroSys.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-container">
        <div class="signup-card">
            
            <div class="logo-area">
            <img src="<?php echo $base_url; ?>/images/LibroSys.png" alt="LibroSys Logo">
            </div>
            
            <h2>ADMIN SIGN UP</h2>
            <form action="index.php?page=admin_register" method="POST">
                <div class="input-group-signup">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" id="admin_id" name="admin_id" placeholder="Create Admin ID" required>
                </div>
                
                <div class="input-group-signup">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create Password" required>
                </div>

                <div class="input-group-signup">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat Password" required>
                </div>

                <button type="submit" class="signup-btn">CREATE ACCOUNT</button>
            </form>
            <div style="margin-top: 25px; text-align: center; font-size: 0.95rem; color: #000000">
                <span>Already have an account?</span>
                <a href="index.php?page=admin_login" style="color: #FFD44A; text-decoration: underline; font-weight: bold;">Login here!</a>
            </div>
        </div>
    </div>
</body>
</html>
