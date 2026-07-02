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
        <div class="section-header">
            <div class="header-left">
                <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="menu-icon" alt="Menu">
                <h2>Admin Settings</h2>
            </div>
            <div class="header-right">
                <span>Admin</span>
                <img src="<?php echo $base_url; ?>/images/profile.png" class="profile-pic" alt="Admin Profile">
            </div>
        </div>

        <div class="settings-center-wrapper">
            <main class="settings-profile-card" style="width: 100%; max-width: 700px; border-radius: 12px; margin-top: 0; min-height: auto; background-color: #ffffff;">
                <a href="index.php?page=admin_dashboard" style="text-decoration: none; color: #666; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;"><i class="fa-solid fa-arrow-left"></i> Return to Dashboard</a>
                
                <?php if ($message): ?>
                    <div class="status-badge <?php echo $message_type === 'success' ? 'available' : 'returned'; ?>" style="width: 100%; margin-bottom: 20px; padding: 12px; text-align: center; border-radius: 8px;">
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

                <div class="settings-section-title" style="margin-top: 40px;">USER DATA MANAGEMENT (XML)</div>
                <div style="padding-left: 20px; margin-bottom: 20px;">
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">Backup your library members and their entire borrow history.</p>
                    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                        <a href="index.php?page=admin_settings&export_users_xml=1" class="save-changes-btn" style="text-decoration: none; display: inline-block;">Export All Users & Borrows</a>
                        <a href="index.php?page=admin_settings&export_full_xml=1" class="save-changes-btn" style="text-decoration: none; display: inline-block; background-color: #2a9d8f;">Export Full System (Books + Users)</a>
                        
                        <form action="index.php?page=admin_settings" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; border-left: 1px solid #ddd; padding-left: 20px;">
                            <input type="file" name="user_xml_file" accept=".xml" required style="font-size: 12px;">
                            <button type="submit" name="import_users_xml" class="save-changes-btn">Import XML</button>
                        </form>
                    </div>
                </div>

                <div class="settings-section-title" style="margin-top: 40px;">SECURITY</div>
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

                <div class="settings-section-title" style="margin-top: 40px; color: #dc3545;">DANGER ZONE</div>
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px; padding-left: 20px;">Once you delete your admin account, you will lose all access to the LibroSys dashboard.</p>
                <form action="index.php?page=admin_settings" method="POST" onsubmit="return confirm('Are you ABSOLUTELY certain? This will permanently delete your administrator account.');">
                    <div class="settings-save-area" style="justify-content: flex-start; padding-left: 20px; border: none;">
                        <button type="submit" name="delete_account" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Delete Admin Account</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>
