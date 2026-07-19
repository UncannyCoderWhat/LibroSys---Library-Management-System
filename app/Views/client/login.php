<?php
// app/Views/client/login.php
// Expects $data array with keys: message, message_type
$message = $data['message'] ?? '';
$message_type = $data['message_type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - User Login</title>
<link rel="stylesheet" href="<?php echo $base_url; ?>/css/clientstyle.css">
    
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
</script>
</head>
<body class="auth-page">

    <img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">

    <header class="main-header">
    <div class="header-content" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
        <label class="switch-container">
            <input type="checkbox" id="theme-toggle" class="switch-input">
            <div class="switch-track">
                <div class="switch-thumb"></div>
            </div>
            <span class="switch-label">Dark Mode</span>
        </label>
    </div>
    </header>



    <div class="login-container">
        <div class="login-card">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="card-logo">
            <?php if (!empty($message)): ?>
                <div class="notification-banner" style="margin-bottom: 20px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border-left-color: <?php echo $message_type === 'success' ? '#28a745' : '#dc3545'; ?>;">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

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
                <a href="index.php?page=signup" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Create Account</a>
            </div>
        </div>
    </div>
    
    <script src="<?php echo $base_url; ?>/public/js/theme.js"></script>
    
</body>
</html>
