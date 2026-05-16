<?php
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys | Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <img src="images/LibroSys.png" alt="Logo">
    </div>
    <div class="main-content-container">
        <div class="section-header">
            <div class="header-left">
                <img src="images/lineMenu.png" class="menu-icon" alt="Menu">
                <h2>Settings</h2>
            </div>
            <div class="header-right">
                <span>Admin</span>
                <img src="images/profile.png" class="profile-pic" alt="Admin Profile">
            </div>
        </div>

        <div class="settings-center-wrapper">
            <div class="settings-grid">
                <div class="settings-nav">
                    <button class="settings-nav-btn active">
                        <i class="fa-solid fa-user-gear"></i> Profile Settings
                    </button>
                    <button class="settings-nav-btn">
                        <i class="fa-solid fa-sliders"></i> System Preferences
                    </button>
                    <button class="settings-nav-btn">
                        <i class="fa-solid fa-bell"></i> Notifications
                    </button>
                </div>

                <div class="settings-profile-card">
                    <div class="settings-section-title">Account</div>
                    <div class="account-info">
                        <p>Admin ID: <strong><?php echo $admin_id; ?></strong></p>
                        <p>Email: <strong><?php echo $admin_email; ?></strong></p>
                    </div>
                
                    <div class="settings-section-title">Change Password</div>
                    <form action="update_password.php" method="POST">
                        <div class="settings-form-row">
                            <label>Old Password:</label>
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
                            <button type="submit" class="save-changes-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
