<?php
session_start();
require_once '../dbForLogin/db.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: client_login.php");
    exit();
}

$cart_items = [];
if (!empty($_SESSION['borrow_cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['borrow_cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
    $stmt->execute($_SESSION['borrow_cart']);
    $cart_items = $stmt->fetchAll();
}

$cartCount = count($cart_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LibroSys | My Cart</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="clientstyle.css">
</head>
<body class="profile-page">
    <img src="../images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="../images/LibroSys.png" alt="Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="home.php"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="browse.php"><i class='bx bx-compass'></i>Browse</a>
                    <a href="cart.php" class="nav-cart-link active">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php"><i class='bx bx-user-circle'></i>Profile</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <main class="profile-main" style="flex: 1;">
            <div class="records-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>YOUR BORROW CART</h3>
                    <?php if(!empty($cart_items)): ?>
                        <button class="borrow-btn" onclick="processAction('checkout')" style="width: auto; padding: 10px 30px;">RENT ALL ITEMS</button>
                    <?php endif; ?>
                </div>
                
                <table class="records-table cart-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cart_items)): ?>
                            <?php foreach ($cart_items as $book): ?>
                                <tr id="cart-row-<?php echo $book['id']; ?>">
                                    <td><img src="../<?php echo htmlspecialchars($book['cover_path']); ?>"></td>
                                    <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo $book['is_exclusive'] ? 'Exclusive' : 'Regular'; ?></td>
                                    <td>
                                        <button class="remove-btn" onclick="removeFromCart(<?php echo $book['id']; ?>)">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding: 50px;">Your cart is empty. <a href="browse.php" style="color: var(--main-color);">Browse books now.</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function removeFromCart(bookId) {
            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('action', 'remove_from_cart');
            fetch('borrow_handler.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => { if(data.status === 'success') window.location.reload(); });
        }
        function processAction(action) {
            const formData = new FormData();
            formData.append('action', action);
            fetch('borrow_handler.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => { alert(data.message); if(data.status === 'success') window.location.href = 'profile.php'; });
        }
    </script>
</body>
</html>