<?php
// View template for Admin Users page
// Expects: $currentPage, $users, $message, $message_type
$currentPage = 'users';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Users</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="<?php echo $base_url; ?>/images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="status-badge <?php echo ($message_type === 'success') ? 'available' : 'returned'; ?>"
                 style="width: 100%; margin-top: 20px; padding: 12px; text-align: center; border-radius: 8px; display: block; min-width: 100%;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar Section -->
        <section class="activity-section" style="margin-top: 25px;">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="userSearch" placeholder="Search by name, email, or User ID...">
                </div>
            </div>
        </section>

        <!-- User Accounts Section -->
        <section class="user-table-container">
            <div class="user-table">
                <table class="user-activity-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Credits</th>
                            <th>Borrowing Status</th>
                            <th>   </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['credit_score'] ?? ''); ?></td>
                                    <td>wag pansinin</td>
                                    <td>
                                        <button type="button" 
                                            class="btn-view-details" 
                                            onclick="openUserModal(this)"
                                            data-id="<?php echo htmlspecialchars($user['id'] ?? ''); ?>"
                                            data-username="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                                            data-email="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                            data-credits="<?php echo htmlspecialchars(($user['credit_score'] ?? '0') . '/10'); ?>"
                                            data-fines="<?php echo htmlspecialchars(number_format($user['total_fines'] ?? 0, 2)); ?>"
                                            data-borrowed="<?php echo htmlspecialchars($user['active_borrows'] ?? ''); ?>"
                                            data-logs="<?php echo htmlspecialchars(json_encode($user['logs'] ?? []), ENT_QUOTES, 'UTF-8'); ?>">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data-cell">No user found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- User Modal Pop-up - START -->
    <div id="userModal" class="modal-overlay">
        <div class="modal-card">
            <!-- Left Column: User Profile -->
            <div class="modal-sidebar">
                <div class="avatar-circle">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                </div>
                <div class="user-info">
                    <p><strong>User ID:</strong> <span id="modalUserId"></span></p>
                    <p><strong>Username:</strong> <span id="modalUsername"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p><strong>Credits:</strong> <span id="modalCredits"></span></p>
                    <p><strong>Total Fines Owed:</strong> <span id="modalFines"></span></p>
                    <p><strong>Currently Borrowed:</strong> <span id="modalBorrowed"></span></p>
                </div>
                <div class="modal-actions">
                    <button type="button" 
                            class="btn-warning" 
                            id="viewFineHistoryBtn" 
                            onclick="triggerFineModal(this)">
                            View Fine History
                    </button>
                    <form id="deleteUserForm" action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <!-- JS fills this value dynamically -->
                        <input type="hidden" name="target_user_id" id="modalDeleteTargetId" value="">
                        <button type="submit" name="delete_user" value="1" class="btn-danger">Delete User</button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Circulation Logs -->
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Circulation Logs</h3>
                    <span class="close-btn" onclick="closeUserModal()">&times;</span>
                </div>
                <div class="logs-table-container">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Type</th>
                                <th>Time Borrowed</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Days Late</th>
                                <th>Total Fine</th>
                            </tr>
                        </thead>
                        <tbody id="modalLogsBody">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- User Modal Pop-up - END -->

    <!-- Fine History Modal -->
    <div id="fineModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="closeFineModal()">&times;</span>
            <h2 class="section-title">FINE HISTORY: <span id="modalUserName" style="color: var(--main-color);"></span></h2>
            <div class="ledger-table" style="margin-top: 20px;">
                <table class="ledger-activity-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Due Date</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Fine Amount</th>
                        </tr>
                    </thead>
                    <tbody id="fineTableBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    
    <script src="<?php echo $base_url; ?>/public/js/fines.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/userModal.js"></script>
</body>
</html>
