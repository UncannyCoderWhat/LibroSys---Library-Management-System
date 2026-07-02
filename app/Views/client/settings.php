<?php
// app/Views/client/settings.php
// Expects $data array keys from SettingsController
$data = $data ?? [];

$cartCount       = (int)($data['cartCount'] ?? 0);
$current_name   = $data['current_name'] ?? '';
$current_email  = $data['current_email'] ?? '';
$message         = $data['message'] ?? '';
$message_type    = $data['message_type'] ?? ''; // success|error
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Settings</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/css/clientstyle.css">
</head>
<body class="profile-page">
    <img src="/images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="/images/LibroSys.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="index.php?page=home"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=browse"><i class='bx bx-compass'></i>Browse</a>
                    <a href="index.php?page=cart" class="nav-cart-link">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=profile" class="active"><i class='bx bx-user-circle'></i>Profile</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="profile-container" style="justify-content: center;">
        <main class="profile-main" style="max-width: 800px; width: 100%;">
            <a href="index.php?page=profile" class="back-link"><i class='bx bx-arrow-back'></i> Return to Profile</a>

            <div class="records-section">
                <h3 style="margin-bottom: 20px;">ACCOUNT SETTINGS</h3>

                <?php if (!empty($message)): ?>
                    <div class="notification-banner"
                         style="margin-bottom: 20px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border-left-color: <?php echo $message_type === 'success' ? '#28a745' : '#dc3545'; ?>;">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=settings" method="POST">
                    <div class="settings-form-row">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($current_name); ?>" required>
                    </div>
                    <div class="settings-form-row">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
                    </div>

                    <div class="settings-save-area" style="margin-top: 30px;">
                        <button type="submit" name="update_account" class="save-changes-btn">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="records-section" style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">CHANGE PASSWORD</h3>
                <form action="index.php?page=settings" method="POST">
                    <div class="settings-form-row">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                    </div>
                    <div class="settings-form-row">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="settings-form-row">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password" required>
                    </div>

                    <div class="settings-save-area" style="margin-top: 30px;">
                        <button type="submit" name="update_password" class="save-changes-btn">Update Password</button>
                    </div>
                </form>
            </div>

            <div class="records-section" style="margin-top: 30px; border: 1px solid #f8d7da;">
                <h3 style="margin-bottom: 10px; color: #721c24;">DANGER ZONE</h3>
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">
                    Once you delete your account, there is no going back. Please be certain.
                </p>

                <form action="index.php?page=settings" method="POST"
                      onsubmit="return confirm('ARE YOU ABSOLUTELY SURE? This will permanently delete your account and all borrowing history.');">
                    <div class="settings-save-area" style="border-top: none; padding-top: 0;">
                        <button type="submit" name="delete_account" class="delete-account-btn">Delete My Account</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
