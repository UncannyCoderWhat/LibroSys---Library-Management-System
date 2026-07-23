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
    <title>LibroSys - Admin Login</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body class="auth-page">

    <img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">

    <header class="main-header">
        <div class="header-content">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="login-container">
        <div class="login-card">
        
        <div class="logo-area">
            <img src="<?php echo $base_url; ?>/images/LibroSysdark.png" alt="LibroSys logo">
        </div>

            <h2>ADMIN LOGIN</h2>
            <form action="index.php?page=admin_authenticate" method="POST">
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
            <div style="margin-top: 25px; text-align: center; font-size: 0.95rem; color: #FFFFFF">
                <span>Don't have an account?</span>
                <a href="index.php?page=admin_signup" style="color: #FFD54A; text-decoration: underline; font-weight: bold;">Sign up here!</a>
            </div>
        </div>
    </div>

</body>
</html>
