<?php
session_start();
$currentPage = 'settings';
require_once '../dbForLogin/db.php';

// Authentication Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$admin_session_user = $_SESSION['admin_user'];
$message = '';
$message_type = '';

// Fetch current admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->execute([$admin_session_user]);
$admin = $stmt->fetch();

// Handle Admin ID Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $new_id = trim($_POST['admin_id']);
    if (!empty($new_id)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_id = ? AND admin_id != ?");
        $check->execute([$new_id, $admin_session_user]);
        if ($check->fetchColumn() > 0) {
            $message = "Error: This Admin ID is already taken.";
            $message_type = "error";
        } else {
            $update = $pdo->prepare("UPDATE admins SET admin_id = ? WHERE admin_id = ?");
            $update->execute([$new_id, $admin_session_user]);
            $_SESSION['admin_user'] = $new_id;
            $admin_session_user = $new_id;
            $message = "Admin ID updated successfully.";
            $message_type = "success";
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $repeat = $_POST['repeat_pass'];

    if (password_verify($old, $admin['password'])) {
        if ($new === $repeat && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
            $update->execute([$hashed, $admin_session_user]);
            $message = "Password updated successfully.";
            $message_type = "success";
        } else {
            $message = "Error: New passwords do not match or are too short.";
            $message_type = "error";
        }
    } else {
        $message = "Error: Current password incorrect.";
        $message_type = "error";
    }
}

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $del = $pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
    $del->execute([$admin_session_user]);
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
}

include 'sidebar.php';
?>

<div class="main-content-container">
    <div class="section-header">
        <div class="header-left">
            <img src="../images/lineMenu.png" class="menu-icon" alt="Menu">
            <h2>Admin Settings</h2>
        </div>
        <div class="header-right">
            <span>Admin</span>
            <img src="../images/profile.png" class="profile-pic" alt="Admin Profile">
        </div>
    </div>

    <div class="settings-center-wrapper">
        <main class="settings-profile-card" style="width: 100%; max-width: 700px; border-radius: 12px; margin-top: 0; min-height: auto; background-color: #ffffff;">
            <a href="dashboard.php" style="text-decoration: none; color: #666; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;"><i class="fa-solid fa-arrow-left"></i> Return to Dashboard</a>
            
            <?php if ($message): ?>
                <div class="status-badge <?php echo $message_type === 'success' ? 'available' : 'returned'; ?>" style="width: 100%; margin-bottom: 20px; padding: 12px; text-align: center; border-radius: 8px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="settings-section-title">ADMIN ACCOUNT MODIFICATION</div>
            <form action="settings.php" method="POST">
                <div class="settings-form-row">
                    <label>Admin ID:</label>
                    <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_session_user); ?>" required>
                </div>
                <div class="settings-save-area">
                    <button type="submit" name="update_account" class="save-changes-btn">Save Changes</button>
                </div>
            </form>

            <div class="settings-section-title" style="margin-top: 40px;">SECURITY</div>
            <form action="settings.php" method="POST">
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
            <form action="settings.php" method="POST" onsubmit="return confirm('Are you ABSOLUTELY certain? This will permanently delete your administrator account.');">
                <div class="settings-save-area" style="justify-content: flex-start; padding-left: 20px; border: none;">
                    <button type="submit" name="delete_account" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Delete Admin Account</button>
                </div>
            </form>
        </main>
    </div>
</div>
