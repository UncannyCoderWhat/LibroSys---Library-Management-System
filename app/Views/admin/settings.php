<?php
// View template for Admin Settings page
// Expects: $currentPage, $message, $message_type, $admin_session_user, $admin
$currentPage = 'settings';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Admin Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-container">
        <!-- Sub-Topbar Navigation -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Settings</span>
                </div>
                <div class="right-profile">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="<?php echo $base_url; ?>/images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-center-wrapper">
            <main class="settings-profile-card">
                <a href="index.php?page=admin_dashboard" class="back-link"><i class="fa-solid fa-arrow-left"></i> Return to Dashboard</a>
                
                <?php if ($message): ?>
                    <div class="status-badge <?php echo $message_type === 'success' ? 'available' : 'returned'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="settings-section-title">ADMIN ACCOUNT MODIFICATION</div>
                <form action="index.php?page=admin_settings" method="POST">
                    <div class="settings-form-row">
                        <label>Admin ID:</label>
                        <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_session_user); ?>" required>
                    </div>
                    <div class="settings-save-area">
                        <button type="submit" name="update_account" class="save-changes-btn">Save Changes</button>
                    </div>
                </form>

                <div class="settings-section-title settings-section-title-spaced">BOOK DATA MANAGEMENT (XML)</div>
                <div class="settings-data-section">
                    <p class="settings-desc">Backup and restore your library book collection.</p>
                    <div class="settings-actions-group">
                        <a href="index.php?page=admin_settings&export_books_xml=1" class="save-changes-btn btn-blue">Export Books to XML</a>
                        
                        <form action="index.php?page=admin_settings" method="POST" enctype="multipart/form-data" class="xml-import-form">
                            <input type="file" name="books_xml_file" accept=".xml" required class="xml-file-input">
                            <button type="submit" name="import_books_xml" class="save-changes-btn btn-blue">Import Books XML</button>
                        </form>
                    </div>
                </div>

                <div class="settings-section-title settings-section-title-spaced">USER DATA MANAGEMENT (XML)</div>
                <div class="settings-data-section">
                    <p class="settings-desc">Backup your library members and their entire borrow history.</p>
                    <div class="settings-actions-group">
                        <a href="index.php?page=admin_settings&export_users_xml=1" class="save-changes-btn btn-link">Export All Users & Borrows</a>
                        <a href="index.php?page=admin_settings&export_full_xml=1" class="save-changes-btn btn-teal">Export Full System (Books + Users)</a>
                        
                        <form action="index.php?page=admin_settings" method="POST" enctype="multipart/form-data" class="xml-import-form">
                            <input type="file" name="user_xml_file" accept=".xml" required class="xml-file-input">
                            <button type="submit" name="import_users_xml" class="save-changes-btn">Import XML</button>
                        </form>
                    </div>
                </div>

                <div class="settings-section-title settings-section-title-spaced">SECURITY</div>
                <form action="index.php?page=admin_settings" method="POST">
                    <div class="settings-form-row">
                        <label>Current Password:</label>
                        <input type="password" name="old_pass" required>
                    </div>
                    <div class="settings-form-row">
                        <label>New Password:</label>
                        <input type="password" name="new_pass" required>
                    </div>
                    <div class="settings-form-row">
                        <label>Repeat Password:</label>
                        <input type="password" name="repeat_pass" required>
                    </div>
                    <div class="settings-save-area">
                        <button type="submit" name="update_password" class="save-changes-btn">Update Password</button>
                    </div>
                </form>

                <div class="settings-section-title settings-section-title-spaced settings-section-title-danger">DANGER ZONE</div>
                <p class="settings-desc settings-desc-left">Once you delete your admin account, you will lose all access to the LibroSys dashboard.</p>
                <form action="index.php?page=admin_settings" method="POST" onsubmit="return confirm('Are you ABSOLUTELY certain? This will permanently delete your administrator account.');">
                    <div class="settings-save-area settings-save-area-left">
                        <button type="submit" name="delete_account" class="btn-danger">Delete Admin Account</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>