<?php
// app/Views/client/signup.php
// Expects $data array with keys: message, message_type
$message = $data['message'] ?? '';
$message_type = $data['message_type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Create Account</title>
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
        <div class="header-content">
            <img src="<?php echo $base_url; ?>/images/LibroSys.png" alt="LibroSys Logo" class="logo">
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
            <img src="<?php echo $base_url; ?>/images/LibroSys.png" alt="LibroSys Logo" class="card-logo">
            <?php if (!empty($message)): ?>
                <div class="notification-banner" style="margin-bottom: 20px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border-left-color: <?php echo $message_type === 'success' ? '#28a745' : '#dc3545'; ?>;">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <h2>Create Account</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="login-btn">Create Account</button>
            </form>
            <div style="margin-top: 20px; text-align: center;">
                <a href="index.php?page=login" style="color: #fca311; text-decoration: none; font-size: 0.9rem;">Already have an account? Login</a>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const themeToggle = document.getElementById("theme-toggle");

        // 1. Set initial state based on saved preference
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // If dark mode, checkbox should be checked
        themeToggle.checked = (savedTheme === 'dark');

        // 2. Click Handler
        themeToggle.addEventListener("change", function () {
            const isDark = this.checked;
            const newTheme = isDark ? "dark" : "light";

            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
        });
    });
</script>
</body>
</html>
