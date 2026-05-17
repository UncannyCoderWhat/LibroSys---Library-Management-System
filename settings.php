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
        <button class="settings-nav-btn active" onclick="showSection(event, 'profile')">
            <i class="fa-solid fa-user-gear"></i> Profile Settings
        </button>
        <button class="settings-nav-btn" onclick="showSection(event, 'system')">
            <i class="fa-solid fa-sliders"></i> System Preferences
        </button>
        <button class="settings-nav-btn" onclick="showSection(event, 'notifications')">
            <i class="fa-solid fa-bell"></i> Notifications
        </button>
    </div>

    <div class="settings-profile-card">
        
        <div id="profile-section" class="settings-content">
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

        <div id="system-section" class="settings-content" style="display: none;">
            <div class="settings-section-title">System Preferences</div>
            <div class="preference-item">
                <span>Toggle Auto-Logout</span>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider round"></span>
                </label>
            </div>
            <div class="preference-item">
                <span>Language</span>
                <select class="settings-select">
                    <option>English (Default)</option>
                    <option>Filipino</option>
                </select>
            </div>
            <div class="settings-save-area">
                <button class="save-changes-btn">Save Preferences</button>
            </div>
        </div>

        <div id="notifications-section" class="settings-content" style="display: none;">
            <div class="settings-section-title">
                Due Reminders <span class="title-line"></span>
            </div>
            <div class="preference-item">
                <span>Enable Due Date Notifications</span>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                </label>
            </div>
            <div class="settings-section-title" style="margin-top: 50px;">
                Email Alerts <span class="title-line"></span>
            </div>
            <div class="preference-item">
                <span>Receive Email Reports</span>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider round"></span>
                </label>
            </div>
        
            <div class="settings-save-area">
                <button class="save-changes-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(event, sectionName) {
    // 1. Itago lahat ng content sections
    const contents = document.querySelectorAll('.settings-content');
    contents.forEach(content => {
        content.style.display = 'none';
    });

    // 2. Alisin ang 'active' class sa lahat ng buttons
    const buttons = document.querySelectorAll('.settings-nav-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });

    // 3. Ipakita ang click na section
    document.getElementById(sectionName + '-section').style.display = 'block';

    // 4. Gawing 'active' ang button na pinindot
    event.currentTarget.classList.add('active');
}
</script>

        </div>
    </div>
</body>
</html>
