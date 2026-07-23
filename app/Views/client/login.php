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
    <title>LibroSys - User Login & Signup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/clientstyle.css">
    
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>

<body class="auth-page">
    <div class="bg-slider-container">
        <?php 
            $covers = array_merge(
                glob('uploads/*_cover*.jpg'), 
                glob('uploads/*_cover*.png'), 
                glob('uploads/*_cover*.webp')
            );
            if (empty($covers)) {
                $covers = glob('uploads/*.{jpg,png,jpeg,webp}', GLOB_BRACE);
            }
        ?>

        <!-- Row 1: Right to Left -->
        <div class="bg-slider-row row-right-to-left">
            <div class="track">
                <?php for ($i = 0; $i < 2; $i++): ?>
                    <div class="cover-group">
                        <?php foreach ($covers as $cover): ?>
                            <img src="<?php echo $base_url . '/' . $cover; ?>" alt="Book Cover">
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Row 2: Left to Right -->
        <div class="bg-slider-row row-left-to-right">
            <div class="track">
                <?php for ($i = 0; $i < 2; $i++): ?>
                    <div class="cover-group">
                        <?php foreach ($covers as $cover): ?>
                            <img src="<?php echo $base_url . '/' . $cover; ?>" alt="Book Cover">
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Row 3: Right to Left -->
        <div class="bg-slider-row row-right-to-left">
            <div class="track">
                <?php for ($i = 0; $i < 2; $i++): ?>
                    <div class="cover-group">
                        <?php foreach ($covers as $cover): ?>
                            <img src="<?php echo $base_url . '/' . $cover; ?>" alt="Book Cover">
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <!-- <img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image"> -->
    <header class="main-header">
        <div class="header-content">
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

    <div class="auth-wrapper" id="authWrapper">
        <!-- Login Form -->
        <div class="form-box login">
            <div class="login-card">
                <?php if (!empty($message)): ?>
                    <div class="notification-banner <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <h2>User Login</h2>
                <form action="" method="POST">
                    <div class="input-group">
                        <input type="text" id="user_id" name="user_id" placeholder=" " required>
                        <label for="user_id">User ID or Email</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="user_password" name="user_password" placeholder=" " required>
                        <label for="user_password">Password</label>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>

        <!-- Signup Form -->
        <div class="form-box register">
            <div class="login-card">
                <?php if (!empty($message)): ?>
                    <div class="notification-banner <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <h2>Create Account</h2>
                <form action="" method="POST">
                    <!-- Side-by-side First & Last Name -->
                    <div class="input-row">
                        <div class="input-group">
                            <input type="text" id="first_name" name="first_name" placeholder=" " required>
                            <label for="first_name">First Name</label>
                        </div>
                        <div class="input-group">
                            <input type="text" id="last_name" name="last_name" placeholder=" " required>
                            <label for="last_name">Last Name</label>
                        </div>
                    </div>

                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder=" " required>
                        <label for="email">Email</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <label for="password">Password</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>

                    <button type="submit" class="login-btn">Create Account</button>
                </form>
            </div>
        </div>

        <!-- Toggle Overlay Panels -->
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome to LibroSys</h1>
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
    <script>
        const authWrapper = document.getElementById('authWrapper');
        const registerBtn = document.getElementById('registerBtn');
        const loginBtn = document.getElementById('loginBtn');

        registerBtn.addEventListener('click', () => {
            authWrapper.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            authWrapper.classList.remove('active');
        });
    </script>
</body>
</html>