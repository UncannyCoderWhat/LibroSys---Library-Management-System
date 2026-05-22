<?php 
$currentPage = 'users';
include 'sidebar.php';
require_once '../dbForLogin/db.php';
require_once 'dashboardController.php';

// Initialize DashboardController to use its methods
$controller = new DashboardController($pdo);

$message = '';
$message_type = '';

// Handle User Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $targetId = $_POST['target_user_id'];
    
    // 1. Check for active borrowed books
    $checkLoans = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'borrowed'");
    $checkLoans->execute([$targetId]);
    $activeLoans = $checkLoans->fetchColumn();

    // 2. Check for fines
    $fines = $controller->getUserTotalFines($targetId);

    if ($activeLoans > 0 || $fines > 0) {
        $message = "Deletion Blocked: User still has active loans or unpaid fines.";
        $message_type = "error";
    } else {
        try {
            $pdo->beginTransaction();
            
            // 1. Delete notifications related to this user
            $delNotifs = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $delNotifs->execute([$targetId]);

            // 2. Delete borrow history (returned/cancelled records)
            $delHistory = $pdo->prepare("DELETE FROM borrows WHERE user_id = ?");
            $delHistory->execute([$targetId]);

            // 3. Finally, delete the user
            $delUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delUser->execute([$targetId]);

            $pdo->commit();
            $message = "User account and all related history have been permanently removed.";
            $message_type = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Database Error: Could not delete user. " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch real users and their borrow counts
$stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM borrows WHERE user_id = u.id AND status = 'borrowed') as active_borrows FROM users u");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <img src="../images/LibroSys.png" alt="Logo">
    </div>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="../images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Users</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="../images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="status-badge <?php echo $message_type === 'success' ? 'available' : 'returned'; ?>" style="width: 100%; margin-top: 20px; padding: 12px; text-align: center; border-radius: 8px; display: block; min-width: 100%;">
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

        <!-- Populated User Cards Container -->
        <div class="user-card-container" id="userContainer">
            <?php foreach ($users as $user): ?>
                <div class="user-card" 
                     data-name="<?php echo strtolower(htmlspecialchars($user['name'])); ?>" 
                     data-id="<?php echo strtolower(htmlspecialchars($user['user_id'])); ?>"
                     data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>">
                    
                    <div class="user-avatar">
                        <img src="../images/profile.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Credit Score:</strong> <span style="color: <?php echo ($user['credit_score'] <= 5) ? 'red' : 'green'; ?>; font-weight: bold;"><?php echo htmlspecialchars($user['credit_score']); ?> / 10</span></p>
                        <?php $totalFines = $controller->getUserTotalFines($user['id']); ?>
                        <p><strong>Total Fines Owed:</strong> <span style="color: <?php echo ($totalFines > 0) ? 'red' : 'green'; ?>; font-weight: bold;">₱<?php echo number_format($totalFines, 2); ?></span></p>
                        <p><strong>Books Borrowed:</strong> <?php echo htmlspecialchars($user['active_borrows']); ?></p>
                    </div>
                    
                    <?php $fineDetails = $controller->getUserFineDetails($user['id']); ?>
                    <button class="submit-btn" style="width: 100%; margin-top: 15px; font-size: 11px; padding: 8px;" 
                            onclick='openFineModal(<?php echo htmlspecialchars(json_encode($fineDetails), ENT_QUOTES); ?>, "<?php echo addslashes($user['name']); ?>")'>
                        <i class="fa-solid fa-clock-rotate-left"></i> View Fine History
                    </button>

                    <form action="users.php" method="POST" onsubmit="return confirm('Delete this user? This cannot be undone.');" style="width: 100%;">
                        <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="delete-btn-modal" style="width: 100%; margin-top: 8px; font-size: 11px; padding: 8px;">
                            <i class="fa-solid fa-user-minus"></i> Delete User
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearch = document.getElementById('userSearch');
        const userContainer = document.getElementById('userContainer');

        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = userContainer.querySelectorAll('.user-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const id = card.getAttribute('data-id');
                const email = card.getAttribute('data-email');

                const matches = name.includes(searchTerm) || 
                                id.includes(searchTerm) || 
                                email.includes(searchTerm);
                card.style.display = matches ? 'flex' : 'none';
            });
        });
    });

    function openFineModal(details, userName) {
        document.getElementById('modalUserName').innerText = userName.toUpperCase();
        const tbody = document.getElementById('fineTableBody');
        tbody.innerHTML = '';

        if (details.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #888;">No history records found for this user.</td></tr>';
        }

        details.forEach(item => {
            const dueDate = item.due_date ? new Date(item.due_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : 'N/A';
            const returnDate = item.return_date ? new Date(item.return_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : (item.is_live_overdue ? '<span style="color:red; font-weight:bold;">OVERDUE</span>' : '---');
            
            const statusLabel = item.is_live_overdue ? 'Live Penalty' : 'Late Return';
            const statusClass = item.is_live_overdue ? 'on-queue' : 'unavailable';

            const row = `
                <tr>
                    <td><strong>${item.title}</strong></td>
                    <td>${dueDate}</td>
                    <td>${returnDate}</td>
                    <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                    <td style="color: red; font-weight: bold;">₱${parseFloat(item.calculated_fine).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('fineModal').style.display = 'flex';
    }

    function closeFineModal() {
        document.getElementById('fineModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('fineModal');
        if (event.target == modal) {
            closeFineModal();
        }
    }
    </script>
</body>
</html>
