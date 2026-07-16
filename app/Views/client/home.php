<?php
// View template: expects $data injected by wrapper/entrypoint.
$generated_user_id = $data['generated_user_id'] ?? null;
$cartCount = $data['cartCount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Home</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/css/clientstyle.css">
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>
<body>
    <img src="/images/library-background.png" alt="Library Background" class="bg-image">

    <header>
        <div class="client-top-bar">
            <img src="/images/LibroSys.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <button class="upgrade-btn" onclick="openPremiumModal()">Upgrade premium</button>
                    <a href="index.php?page=home" class="active"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=browse"><i class='bx bx-compass'></i>Browse</a>
                    <a href="index.php?page=cart" class="nav-cart-link">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo (int)$cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=profile"><i class='bx bx-user-circle'></i>Profile</a>
                    <label class="switch-container">
                    <input type="checkbox" id="theme-toggle" class="switch-input">
                    <div class="switch-track">
                        <div class="switch-thumb"></div>
                    </div>
                    <span class="switch-label">Dark Mode</span>
                   </label>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <?php if ($generated_user_id): ?>
                <div class="notification-banner" style="margin-bottom: 20px; background-color: #d4edda; color: #155724; border-left-color: #28a745; max-width: 600px; margin: 0 auto 30px auto; padding: 15px; border-radius: 10px;">
                    <span><i class='bx bx-check-circle'></i> Registration successful! Your unique Login ID is: <strong><?php echo htmlspecialchars($generated_user_id); ?></strong>. Please save this ID for future logins.</span>
                </div>
                <?php
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        unset($_SESSION['temp_user_id_for_display']);
                    }
                ?>
            <?php endif; ?>

            <div class="hero-card">
                <h1>Discover Your Next Chapter</h1>
                <p>Your all-in-one digital library for browsing books</p>
                <button class="b-button" onclick="window.location.href='index.php?page=browse'">Start Browsing</button>
            </div>
        </section>
    </main>

    <!-- Upgrade Premium Modal -->
    <div id="premiumModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePremiumModal()">&times;</span>
            <img src="/images/LibroSys.png" alt="LibroSys Logo" class="modal-logo">
            <h3>Level-up your LibroSys Experience!</h3>

            <table class="premium-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Regular</th>
                        <th>Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>(Perks to)</td>
                        <td>X</td>
                        <td>/</td>
                    </tr>
                </tbody>
            </table>

            <p class="trial-text">Start your 7 day free trial</p>

            <div class="price-boxes">
                <button type="button" class="price-box" onclick="window.location.href='index.php?page=browse'">
                    <span class="price-title">P100 /month</span>
                    <span class="price-sub">1 MONTH</span>
                </button>
                <button type="button" class="price-box" onclick="window.location.href='index.php?page=browse'">
                    <span class="price-title">P90 /month</span>
                    <span class="price-sub">P1080 annually</span>
                    <span class="price-sub">1 YEAR</span>
                </button>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>© 2026 LibroSys. All rights reserved.</p>
            <div class="social-links">
                <a href="#"><i class="bx bxl-twitter"></i></a>
                <a href="#"><i class="bx bxl-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script src="<?php echo $base_url; ?>/public/js/upgradePremium.js"></script>
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