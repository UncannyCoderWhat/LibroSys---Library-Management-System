<?php
// app/Views/client/cart.php
// Expects $data array with keys: cart_items, cartCount
$cart_items = $data['cart_items'] ?? [];
$cartCount = (int)($data['cartCount'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LibroSys | My Cart</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/css/clientstyle.css">
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
</script>
</head>
<body class="profile-page">
    <img src="/images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="/images/LibroSys.png" alt="Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="index.php?page=home"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=browse"><i class='bx bx-compass'></i>Browse</a>
                    <a href="index.php?page=cart" class="nav-cart-link active">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
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

    <div class="profile-container" style="justify-content: center;">
        <main class="profile-main" style="max-width: 1000px; width: 100%;">
            <div class="records-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-size: 1.5rem; letter-spacing: 1px;">
                        <i class='bx bx-shopping-bag' style="color: var(--main-color);"></i> BORROW CART
                        <span style="color: #888; font-weight: 400; font-size: 1rem;">(<?php echo $cartCount; ?>)</span>
                    </h3>
                    <?php if(!empty($cart_items)): ?>
                        <button class="borrow-btn" onclick="processAction('checkout')">RENT ALL ITEMS</button>
                    <?php endif; ?>
                </div>

                <table class="records-table cart-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Type</th>
                            <th>Availability</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cart_items)): ?>
                            <?php foreach ($cart_items as $book): ?>
                                <tr id="cart-row-<?php echo (int)$book['id']; ?>">
                                    <td><img src="<?php echo htmlspecialchars($book['cover_path'] ?? ''); ?>"></td>
                                    <td><strong><?php echo htmlspecialchars($book['title'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($book['author'] ?? ''); ?></td>
                                    <td><?php echo !empty($book['is_exclusive']) ? 'Exclusive' : 'Regular'; ?></td>
                                    <td>
                                        <?php if (($book['is_borrowed'] ?? 0) > 0): ?>
                                            <span class="status-badge unavailable">Unavailable</span>
                                        <?php else: ?>
                                            <span class="status-badge available">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="remove-btn" onclick="removeFromCart(<?php echo (int)$book['id']; ?>)">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="empty-cart-message">Your cart is empty. <a href="index.php?page=browse">Browse books now.</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="public/js/cart.js"></script>
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
