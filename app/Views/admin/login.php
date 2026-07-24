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
    <title>LibroSys - Admin Login & Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>

<body class="auth-page">
    <header class="main-header">
        <div class="header-content">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
        </div>
    </header>

    <div class="auth-wrapper" id="authWrapper">
        <!-- Admin Login Form -->
        <div class="form-box login">
            <div class="login-card">
                <?php if (!empty($message)): ?>
                    <div class="notification-banner <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <h2>ADMIN LOGIN</h2>
                <form action="index.php?page=admin_authenticate" method="POST">
                    <div class="input-group">
                        <input type="text" id="admin_id" name="admin_id" placeholder=" " required>
                        <label for="admin_id">Admin ID</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <label for="password">Password</label>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>

        <!-- Admin Signup Form -->
        <div class="form-box register">
            <div class="login-card">
                <?php if (!empty($message)): ?>
                    <div class="notification-banner <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <h2>ADMIN SIGN UP</h2>
                <form action="index.php?page=admin_register" method="POST">
                    <div class="input-group">
                        <input type="text" id="signup_admin_id" name="admin_id" placeholder=" " required>
                        <label for="signup_admin_id">Admin ID</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="signup_password" name="password" placeholder=" " required>
                        <label for="signup_password">Password</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>

                    <button type="submit" class="login-btn">CREATE ACCOUNT</button>
                </form>
            </div>
        </div>

        <!-- Toggle Overlay Panels -->
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome Admin</h1>
                <p>Don't have an Account?</p>
                <button class="btn register-btn" id="registerBtn">Register</button>
            </div>

            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an Account?</p>
                <button class="btn login-btn" id="loginBtn">Login</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>/public/js/theme.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/loginSignupAnimation.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/adminLoginBG.js"></script>
</body>
</html>