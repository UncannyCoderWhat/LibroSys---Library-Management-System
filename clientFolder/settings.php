<?php
session_start();
require_once '../dbForLogin/db.php';

// Redirect to login if session is not set
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: client_login.php");
    exit();
}

$db_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // 'success' or 'error'

// Fetch current user data
$userStmt = $pdo->prepare("SELECT name, email, password FROM users WHERE id = ?");
$userStmt->execute([$db_id]);
$user = $userStmt->fetch();

if (!$user) {
    // This should ideally not happen if user_id is valid
    header("Location: client_login.php");
    exit();
}

$current_name = $user['name'];
$current_email = $user['email'];

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);

    // Basic validation
    if (empty($new_name) || empty($new_email)) {
        $message = 'Name and Email cannot be empty.';
        $message_type = 'error';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $message_type = 'error';
    } else {
        try {
            // Check if email already exists for another user
            $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $checkEmailStmt->execute([$new_email, $db_id]);
            if ($checkEmailStmt->fetchColumn() > 0) {
                $message = 'This email is already registered to another account.';
                $message_type = 'error';
            } else {
                $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $updateStmt->execute([$new_name, $new_email, $db_id]);

                // Update session variable if name changed
                $_SESSION['user_name'] = $new_name;
                $current_name = $new_name; // Update displayed value
                $current_email = $new_email; // Update displayed value

                $message = 'Account details updated successfully!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            error_log("Error updating user settings: " . $e->getMessage());
            $message = 'An error occurred while updating your details. Please try again.';
            $message_type = 'error';
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $message = 'All password fields are required.';
        $message_type = 'error';
    } elseif (!password_verify($current_pass, $user['password'])) {
        $message = 'Current password is incorrect.';
        $message_type = 'error';
    } elseif ($new_pass !== $confirm_pass) {
        $message = 'New passwords do not match.';
        $message_type = 'error';
    } elseif (strlen($new_pass) < 6) {
        $message = 'New password must be at least 6 characters long.';
        $message_type = 'error';
    } else {
        try {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $updatePassStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updatePassStmt->execute([$hashed_pass, $db_id]);

            // Refresh user data to get the new hash for subsequent checks
            $user['password'] = $hashed_pass;

            $message = 'Password updated successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
            $message_type = 'error';
        }
    }
}

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        // 1. Check for active borrowed books
        $checkBorrowStmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'borrowed'");
        $checkBorrowStmt->execute([$db_id]);
        $activeLoans = $checkBorrowStmt->fetchColumn();

        // 2. Check for unpaid fines (Only those where is_fine_paid is FALSE)
        $checkFineStmt = $pdo->prepare("SELECT status, due_date, fine_amount FROM borrows WHERE user_id = ? AND is_fine_paid = FALSE");
        $checkFineStmt->execute([$db_id]);
        $fines = $checkFineStmt->fetchAll();

        $unpaidFines = 0;
        foreach ($fines as $f) {
            $amt = $f['fine_amount'] ?? 0;
            // Account for live fines on overdue books that are still out
            if ($f['status'] === 'borrowed' && !empty($f['due_date'])) {
                $now = time();
                $dueDate = strtotime($f['due_date']);
                if ($now > $dueDate) {
                    $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
                    if ($daysLate <= 3) $amt = $daysLate * 50;
                    elseif ($daysLate <= 10) $amt = $daysLate * 100;
                    else $amt = $daysLate * 150;
                } else {
                    $amt = 0;
                }
            }
            $unpaidFines += $amt;
        }

        // If the user has books or owes money, block the deletion
        if ($activeLoans > 0 || $unpaidFines > 0) {
            $message = "Account deletion blocked: You currently have " . ($activeLoans > 0 ? "$activeLoans active book loan(s) " : "");
            if ($unpaidFines > 0) {
                $message .= ($activeLoans > 0 ? "and " : "") . "₱" . number_format($unpaidFines, 2) . " in outstanding fines. ";
            }
            $message .= "Please return all books and settle fines before closing your account.";
            $message_type = 'error';
        } else {
            // 3. If clear, proceed with deletion
            $pdo->beginTransaction();
            // Clear related records to satisfy foreign key constraints
            $pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$db_id]);
            $pdo->prepare("DELETE FROM borrows WHERE user_id = ?")->execute([$db_id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$db_id]);
            $pdo->commit();

            // Clear session and redirect to login
            $_SESSION = array();
            session_destroy();
            header("Location: client_login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error deleting account: " . $e->getMessage());
        $message = 'An error occurred while deleting your account.';
        $message_type = 'error';
    }
}

$cartCount = isset($_SESSION['borrow_cart']) ? count($_SESSION['borrow_cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Settings</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="clientstyle.css">
</head>
<body class="profile-page">
    <img src="../images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="../images/LibroSys.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="home.php"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="browse.php"><i class='bx bx-compass'></i>Browse</a>
                    <a href="cart.php" class="nav-cart-link">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php" class="active"><i class='bx bx-user-circle'></i>Profile</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="profile-container" style="justify-content: center;">
        <main class="profile-main" style="max-width: 800px; width: 100%;">
            <a href="profile.php" class="back-link"><i class='bx bx-arrow-back'></i> Return to Profile</a>
            
            <div class="records-section">
                <h3 style="margin-bottom: 20px;">ACCOUNT SETTINGS</h3>
                
                <?php if ($message): ?>
                    <div class="notification-banner" style="margin-bottom: 20px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border-left-color: <?php echo $message_type === 'success' ? '#28a745' : '#dc3545'; ?>;">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form action="settings.php" method="POST">
                    <div class="settings-form-row">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($current_name); ?>" required>
                    </div>
                    <div class="settings-form-row">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
                    </div>
                    <!-- Add more settings fields here if needed, e.g., password change -->
                    <div class="settings-save-area" style="margin-top: 30px;">
                        <button type="submit" name="update_account" class="save-changes-btn">Save Changes</button>
                    </div>
                </form>
            </div>
            
            <div class="records-section" style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">CHANGE PASSWORD</h3>
                <form action="settings.php" method="POST">
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
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">Once you delete your account, there is no going back. Please be certain.</p>
                <form action="settings.php" method="POST" onsubmit="return confirm('ARE YOU ABSOLUTELY SURE? This will permanently delete your account and all borrowing history.');">
                    <div class="settings-save-area" style="border-top: none; padding-top: 0;">
                        <button type="submit" name="delete_account" class="delete-account-btn">Delete My Account</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>