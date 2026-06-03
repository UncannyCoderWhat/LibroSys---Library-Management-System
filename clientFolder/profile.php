<?php
session_start();
require_once '../dbForLogin/db.php';
date_default_timezone_set('Asia/Manila');

// Redirect to login if session is not set
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: client_login.php");
    exit();
}

$db_id = $_SESSION['user_id']; // The integer primary key

// 1. Fetch User details and Credit Score
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$db_id]);
$user = $userStmt->fetch();

$displayname = $user['name'];
$username = $user['user_id']; // The generated USR- string
$member_id = $user['user_id'];
$credit_score = $user['credit_score'];
$user_status = ($credit_score > 5) ? "Exclusive" : "Regular";

// 2. Calculate Dashboard Metrics
// Total Borrowed (History)
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ?");
$stmtTotal->execute([$db_id]);
$totalBorrowed = $stmtTotal->fetchColumn();

// Total Returned
$stmtReturned = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'returned'");
$stmtReturned->execute([$db_id]);
$totalReturned = $stmtReturned->fetchColumn();

// Total Pending (Currently out)
$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'borrowed'");
$stmtPending->execute([$db_id]);
$totalPending = $stmtPending->fetchColumn();

// 3. Fetch Borrowing Records for the table
$recordsStmt = $pdo->prepare("
    SELECT br.id as borrow_id, b.title, b.author, br.borrow_date, br.due_date, br.return_date, br.status, br.fine_amount, br.is_fine_paid
    FROM borrows br 
    JOIN books b ON br.book_id = b.id 
    WHERE br.user_id = ? AND br.status != 'reserved'
    ORDER BY br.borrow_date DESC
");
$recordsStmt->execute([$db_id]);
$records = $recordsStmt->fetchAll();

// 3.5 Fetch Reservation Records with Availability
$resStmt = $pdo->prepare("
    SELECT br.id as res_id, b.id as book_id, b.title, b.author, br.borrow_date as reservation_date,
        (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as is_currently_borrowed,
        (SELECT id FROM borrows WHERE book_id = b.id AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1) as next_in_line_res_id
    FROM borrows br 
    JOIN books b ON br.book_id = b.id 
    WHERE br.user_id = ? AND br.status = 'reserved'
    ORDER BY br.borrow_date DESC
");
$resStmt->execute([$db_id]);
$resRecords = $resStmt->fetchAll();

// 4. Fetch Unread Notifications
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$notifStmt->execute([$db_id]);
$notifications = $notifStmt->fetchAll();

$cartCount = isset($_SESSION['borrow_cart']) ? count($_SESSION['borrow_cart']) : 0;
$totalFinesOwed = 0; // Initialize total fines owed
$fineItems = []; // To store items for the receipt
// Pre-calculate total fines owed (only unpaid ones)
$stmtOutstandingFines = $pdo->prepare("SELECT br.id, b.title, br.due_date, br.status, br.fine_amount FROM borrows br JOIN books b ON br.book_id = b.id WHERE br.user_id = ? AND br.is_fine_paid = FALSE");
$stmtOutstandingFines->execute([$db_id]);
$outstandingFinesRecords = $stmtOutstandingFines->fetchAll();

foreach ($outstandingFinesRecords as $row) {
    $fine = $row['fine_amount']; // This is the incurred fine
    if ($row['status'] === 'borrowed' && !empty($row['due_date'])) {
        $now = time();
        $dueDate = strtotime($row['due_date']);
        if ($now > $dueDate) {
            $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
            if ($daysLate <= 3) $fine = $daysLate * 50;
            elseif ($daysLate <= 10) $fine = $daysLate * 100;
            else $fine = $daysLate * 150;
        } else {
            $fine = 0;
        }
    }

    if ($fine > 0) { // Only add to fineItems if there's an actual fine amount
        $fineItems[] = [
            'title' => $row['title'],
            'amount' => $fine
        ];
    }
    $totalFinesOwed += $fine;
}

$credit_tooltip = ($credit_score <= 5) 
    ? "Bad Standing: Your score is 5 or below, likely due to late returns. Exclusive perks are currently locked until your score improves." 
    : "Good Standing: Your account is in great shape! You have full access to all library perks.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Profile</title>
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

    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="user-avatar-large">
                <img src="../images/profile.png" alt="User Avatar">
            </div>
            <h2 class="display-name"><?php echo htmlspecialchars($displayname); ?></h2>
            
            <div class="info-group">
                <label>Username:</label>
                <span><strong><?php echo htmlspecialchars($username); ?></strong></span>
            </div>
            <div class="info-group">
                <label>Member ID:</label>
                <span><strong><?php echo htmlspecialchars($member_id); ?></strong></span>
            </div>
            <div class="info-group">
                <label>Membership Status:</label>
                <span><strong><?php echo htmlspecialchars($user_status); ?></strong></span>
            </div>
            
            <div class="info-group">
                <label>Credit Score:</label>
                <span class="score-display tooltip-trigger" data-tooltip="<?php echo $credit_tooltip; ?>" style="color: <?php echo ($credit_score <= 5) ? '#e74c3c' : '#27ae60'; ?>;">
                    <strong><?php echo $credit_score; ?> / 10</strong>
                </span>
            </div>
        
            <div class="sidebar-footer">
                <a href="settings.php"><i class='bx bx-cog'></i> Settings</a>
                <a href="logout.php"><i class='bx bx-log-out'></i> Logout</a>
            </div>
        </aside>

        <main class="profile-main">
            <?php if (isset($_SESSION['browse_error'])): ?>
                <div class="notification-banner" style="background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <span><i class='bx bx-error-circle' style="font-size: 1.2rem; vertical-align: middle;"></i> <?php echo $_SESSION['browse_error']; ?></span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #721c24; font-size: 1.2rem;"><i class='bx bx-x-circle'></i></button>
                </div>
                <?php unset($_SESSION['browse_error']); ?>
            <?php endif; ?>

            <?php foreach ($notifications as $notif): ?>
                <div class="notification-banner" id="notif-<?php echo $notif['id']; ?>" style="background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 5px solid #ffc107; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class='bx bxs-bell-ring'></i> <?php echo htmlspecialchars($notif['message']); ?></span>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <small style="color: #666;"><?php echo date("M d", strtotime($notif['created_at'])); ?></small>
                        <button onclick="markAsRead(<?php echo $notif['id']; ?>)" style="background: none; border: none; cursor: pointer; color: #856404; font-size: 1.2rem;"><i class='bx bx-x-circle'></i></button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="metrics-row">
                <div class="metric-card">
                    <p class="metric-label">TOTAL RETURNED BOOKS</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo $totalReturned; ?></span>
                    </div>
                    <p class="metric-sub">All returned books</p>
                </div>
            
                <div class="metric-card">
                    <p class="metric-label">TOTAL BORROWED BOOKS</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo $totalBorrowed; ?></span>
                    </div>
                    <p class="metric-sub">All borrowed books</p>
                </div>
            
                <div class="metric-card">
                    <p class="metric-label">TOTAL PENDING RETURN</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo $totalPending; ?></span>
                    </div>
                    <p class="metric-sub">All unreturned books</p>
                </div>
            
                <div class="metric-card credit-card" style="background-color: <?php echo ($credit_score <= 5) ? '#e74c3c' : 'var(--main-color)'; ?>;">
                    <p class="metric-label">YOUR CREDIT SCORE</p>
                    <div class="metric-body">
                        <i class='bx bxs-star' style="font-size: 3rem; color: #fff;"></i>
                        <span class="metric-value"><?php echo $credit_score; ?></span>
                    </div>
                    <p class="metric-sub tooltip-trigger" data-tooltip="<?php echo $credit_tooltip; ?>" style="color: #fff; font-weight: bold;">
                        <?php echo ($credit_score > 5) ? 'GOOD STANDING' : 'BAD STANDING'; ?>
                    </p>
                </div>
            </div>

            <div class="metric-card" style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p class="metric-label">TOTAL FINES OWED</p>
                    <div class="metric-body">
                        <i class='bx bx-dollar-circle' style="font-size: 3rem; color: var(--main-color);"></i>
                        <span class="metric-value">₱<?php echo number_format($totalFinesOwed, 2); ?></span>
                    </div>
                    <p class="metric-sub">Outstanding penalties</p>
                </div>
                <?php if ($totalFinesOwed > 0): ?>
                    <button class="pay-btn" onclick="openReceiptModal()">PAY ALL</button>
                <?php endif; ?>
            </div>


            <div class="records-section">
                <div class="tabs-header" style="display: flex; gap: 20px; border-bottom: 2px solid #eee; margin-bottom: 20px; flex-wrap: wrap;">
                    <h3 id="tab-borrow" class="tab-item active" onclick="switchTab('borrow')" style="cursor: pointer; padding-bottom: 10px; border-bottom: 3px solid var(--main-color); margin-bottom: -2px; white-space: nowrap;">BORROW HISTORY</h3>
                    <h3 id="tab-current-fines" class="tab-item" onclick="switchTab('current-fines')" style="cursor: pointer; padding-bottom: 10px; color: #888; white-space: nowrap;">CURRENT FINES</h3>
                    <h3 id="tab-fines" class="tab-item" onclick="switchTab('fines')" style="cursor: pointer; padding-bottom: 10px; color: #888; white-space: nowrap;">FINES HISTORY</h3>
                </div>

                <!-- Borrow History Tab -->
                <div id="content-borrow" class="table-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Total Fine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $row): ?>
                                    <?php
                                        // Get stored fine or calculate live fine if still borrowed and overdue
                                        $displayFine = $row['fine_amount'] ?? 0;
                                        
                                        if ($row['status'] === 'borrowed' && !empty($row['due_date'])) {
                                            $now = time();
                                            $dueDate = strtotime($row['due_date']);
                                            if ($now > $dueDate) {
                                                $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
                                                if ($daysLate <= 3) $displayFine = $daysLate * 50;
                                                elseif ($daysLate <= 10) $displayFine = $daysLate * 100;
                                                else $displayFine = $daysLate * 150;
                                            } else {
                                                $displayFine = 0;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td><?php echo date("M d, Y", strtotime($row['borrow_date'])); ?></td>
                                        <td><?php echo $row['due_date'] ? date("M d, Y", strtotime($row['due_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'borrowed'): ?>
                                                <?php 
                                                    $isOverdue = (!empty($row['due_date']) && time() > strtotime($row['due_date']));
                                                ?>
                                                <?php if ($isOverdue): ?>
                                                    <span class="status-badge on-queue">Payment Required</span>
                                                <?php else: ?>
                                                    <button onclick="returnBook(<?php echo $row['borrow_id']; ?>)" class="return-action-btn">Return Now</button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php echo date("M d, Y", strtotime($row['return_date'])); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>₱<?php echo number_format($displayFine, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No borrowing history found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Current Fines Tab -->
                <div id="content-current-fines" class="table-container" style="display: none;">
                    <?php if (!empty($fineItems)): ?>
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Fine Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fineItems as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                                        <td style="color: #e74c3c; font-weight: 800;">₱<?php echo number_format($item['amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align:center; padding: 40px; color: #888;">No current outstanding fines. Keep up the good work!</p>
                    <?php endif; ?>
                </div>

                <!-- Fines History Tab -->
                <div id="content-fines" class="table-container" style="display: none;">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Penalty Details</th>
                                <th>Fine Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hasFines = false;
                            foreach ($records as $row): 
                                $fine = $row['fine_amount'] ?? 0;
                                $isOverdue = false;
                                
                                // Calculate live fine if still borrowed
                                if ($row['status'] === 'borrowed' && !empty($row['due_date'])) {
                                    $now = time();
                                    $dueDate = strtotime($row['due_date']);
                                    if ($now > $dueDate) {
                                        $isOverdue = true;
                                        $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
                                        if ($daysLate <= 3) $fine = $daysLate * 50;
                                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                                        else $fine = $daysLate * 150;
                                    }
                                }

                                if ($fine > 0): 
                                    $hasFines = true;
                                    $dueDateStr = $row['due_date'] ? date("M d, Y", strtotime($row['due_date'])) : 'N/A';
                                    $returnDateStr = ($row['status'] === 'returned') ? date("M d, Y", strtotime($row['return_date'])) : '<span style="color: #e74c3c; font-weight: bold;">STILL OUT</span>';
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                    <td><?php echo $dueDateStr; ?></td>
                                    <td><?php echo $returnDateStr; ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'borrowed' && $isOverdue): ?>
                                            <span class="status-badge on-queue">Overdue Penalty</span>
                                        <?php else: ?>
                                            <span class="status-badge unavailable">Late Return</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #e74c3c; font-weight: 800;">₱<?php echo number_format($fine, 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="5" style="text-align: right; font-size: 0.85rem; color: #666; padding-top: 0; padding-bottom: 10px;">Status: <?php echo $row['is_fine_paid'] ? '<span style="color: #28a745; font-weight: bold;">PAID</span>' : '<span style="color: #dc3545; font-weight: bold;">UNPAID</span>'; ?></td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$hasFines): ?>
                                <tr><td colspan="5" style="text-align:center; padding: 40px; color: #888;">No penalty history found. Keep up the good work!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservations Section -->
            <div class="records-section" style="margin-top: 30px;">
                <h3>MY RESERVATIONS</h3>
                <div class="table-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Reservation Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($resRecords)): ?>
                                <?php foreach ($resRecords as $res): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($res['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($res['author']); ?></td>
                                        <td><?php echo date("M d, Y", strtotime($res['reservation_date'])); ?></td>
                                        <td>
                                            <?php 
                                                $isFirst = ($res['res_id'] == $res['next_in_line_res_id']);
                                                $isAvailable = ($res['is_currently_borrowed'] == 0);
                                            ?>
                                            <?php if ($isAvailable && $isFirst): ?>
                                                <span class="status-badge available">Available for Pickup</span>
                                            <?php elseif ($isAvailable && !$isFirst): ?>
                                                <span class="status-badge on-queue">On Queue</span>
                                            <?php else: ?>
                                                <span class="status-badge reserved">Waitlisted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px; align-items: center;">
                                                <?php if ($isAvailable && $isFirst): ?>
                                                    <button onclick="processAction('borrow', <?php echo $res['book_id']; ?>)" class="borrow-btn" style="padding: 8px 12px; font-size: 0.8rem;"><i class='bx bx-book-reader'></i> Borrow Now</button>
                                                <?php endif; ?>
                                                <!-- Always show cancel button for reservations -->
                                                <button onclick="cancelReservation(<?php echo $res['res_id']; ?>)" class="remove-btn" title="Cancel Reservation">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center;">No active reservations found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal" style="display: none;">
        <div class="modal-content receipt-card">
            <div class="receipt-header">
                <img src="../images/LibroSys.png" alt="Logo" style="width: 120px; margin-bottom: 10px;">
                <h2>PAYMENT RECEIPT</h2>
                <p>Transaction ID: #<?php echo strtoupper(uniqid()); ?></p>
                <p>Date: <?php echo date("M d, Y h:i A"); ?></p>
            </div>
            <hr class="receipt-divider">
            <div class="receipt-body">
                <p><strong>Billed To:</strong> <?php echo htmlspecialchars($displayname); ?></p>
                <div class="receipt-items">
                    <?php foreach ($fineItems as $item): ?>
                        <div class="receipt-item">
                            <span><?php echo htmlspecialchars($item['title']); ?> (Fine)</span>
                            <span>₱<?php echo number_format($item['amount'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <hr class="receipt-divider">
            <div class="receipt-total">
                <span>TOTAL AMOUNT</span>
                <span>₱<?php echo number_format($totalFinesOwed, 2); ?></span>
            </div>
            <div class="modal-actions" style="margin-top: 30px;">
                <button class="cart-btn" onclick="closeReceiptModal()">Cancel</button>
                <button class="borrow-btn" onclick="confirmPayment()">Confirm Payment</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure the default tab is active on page load
            switchTab('borrow');
        });

        function switchTab(tab) {
            // Content toggles
            document.getElementById('content-borrow').style.display = (tab === 'borrow') ? 'block' : 'none';
            document.getElementById('content-current-fines').style.display = (tab === 'current-fines') ? 'block' : 'none';
            document.getElementById('content-fines').style.display = (tab === 'fines') ? 'block' : 'none';

            // Header styling
            const borrowHeader = document.getElementById('tab-borrow');
            const currentFinesHeader = document.getElementById('tab-current-fines');
            const finesHeader = document.getElementById('tab-fines');

            // Reset all headers
            borrowHeader.style.borderBottom = "none";
            borrowHeader.style.color = "#888";
            currentFinesHeader.style.borderBottom = "none";
            currentFinesHeader.style.color = "#888";
            finesHeader.style.borderBottom = "none";
            finesHeader.style.color = "#888";

            // Activate selected tab
            const activeHeader = document.getElementById('tab-' + tab);
            if (activeHeader) {
                activeHeader.style.borderBottom = "3px solid var(--main-color)";
                activeHeader.style.color = "#000";
            }
        }

        // Initial call to set the default tab on page load
        window.onload = function() {
            switchTab('borrow');
        }

        function openReceiptModal() {
            document.getElementById('receiptModal').style.display = 'flex';
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        function confirmPayment() {
            const formData = new FormData();
            formData.append('action', 'pay_fines');

            fetch('borrow_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.reload();
                }
            });
        }

        function processAction(actionType, bookId) { // Simplified for profile's "Borrow Now"
            if(!confirm("Are you sure you want to borrow this book?")) return;

            const formData = new FormData();
            formData.append('action', 'borrow'); // Always 'borrow' for this context
            formData.append('book_id', bookId);
            // The backend will automatically fulfill any existing reservation for this book by this user

            fetch('borrow_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.reload(); // Reload to update tables
                }
            });
        }

        function cancelReservation(resId) {
            if(!confirm("Are you sure you want to cancel this reservation?")) return;
            
            const formData = new FormData();
            formData.append('borrow_id', resId);
            formData.append('action', 'cancel_reservation');

            fetch('borrow_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json()).then(data => {
                alert(data.message);
                if(data.status === 'success') location.reload();
            });
        }

        function returnBook(borrowId) {
            if(!confirm("Are you sure you want to return this book?")) return;
            const formData = new FormData();
            formData.append('borrow_id', borrowId);
            fetch('return_handler.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(err => alert("An error occurred while returning the book. Please check your connection."));
        }

        function markAsRead(id) {
            const formData = new FormData();
            formData.append('notification_id', id);
            fetch('mark_read.php', { method: 'POST', body: formData })
            .then(() => document.getElementById('notif-' + id).remove());
        }
    </script>
</body>
</html>