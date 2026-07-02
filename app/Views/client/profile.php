<?php
// app/Views/client/profile.php
// Expects $data array keys from ProfileController
$data = $data ?? [];

$displayname      = $data['displayname'] ?? '';
$username         = $data['username'] ?? '';
$member_id        = $data['member_id'] ?? '';
$credit_score     = $data['credit_score'] ?? 0;
$user_status      = $data['user_status'] ?? 'Regular';
$credit_tooltip   = $data['credit_tooltip'] ?? '';

$totalBorrowed    = $data['totalBorrowed'] ?? 0;
$totalReturned    = $data['totalReturned'] ?? 0;
$totalPending     = $data['totalPending'] ?? 0;

$records          = $data['records'] ?? [];
$resRecords       = $data['resRecords'] ?? [];
$notifications    = $data['notifications'] ?? [];

$cartCount        = (int)($data['cartCount'] ?? 0);

$totalFinesOwed   = (float)($data['totalFinesOwed'] ?? 0);
$fineItems        = $data['fineItems'] ?? [];

$browse_error     = $data['browse_error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Profile</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/css/clientstyle.css">


</head>
<body class="profile-page">
    <img src="/images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="/images/LibroSys.png" alt="Logo" class="logo">
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

    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="user-avatar-large">
                <img src="/images/profile.png" alt="User Avatar">
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
                <span class="score-display tooltip-trigger" data-tooltip="<?php echo htmlspecialchars($credit_tooltip); ?>" style="color: <?php echo ((int)$credit_score <= 5) ? '#e74c3c' : '#27ae60'; ?>;">
                    <strong><?php echo (int)$credit_score; ?> / 10</strong>
                </span>
            </div>

                <div class="sidebar-footer">
                <a href="index.php?page=settings"><i class='bx bx-cog'></i> Settings</a>
                <a href="index.php?page=logout"><i class='bx bx-log-out'></i> Logout</a>


            </div>
        </aside>

        <main class="profile-main">
            <?php if (!empty($browse_error)): ?>
                <div class="notification-banner" style="background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <span><i class='bx bx-error-circle' style="font-size: 1.2rem; vertical-align: middle;"></i> <?php echo htmlspecialchars($browse_error); ?></span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #721c24; font-size: 1.2rem;"><i class='bx bx-x-circle'></i></button>
                </div>
            <?php endif; ?>

            <?php foreach ($notifications as $notif): ?>
                <div class="notification-banner" id="notif-<?php echo (int)($notif['id'] ?? 0); ?>" style="background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 5px solid #ffc107; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class='bx bxs-bell-ring'></i> <?php echo htmlspecialchars($notif['message'] ?? ''); ?></span>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <small style="color: #666;"><?php echo !empty($notif['created_at']) ? date("M d", strtotime($notif['created_at'])) : ''; ?></small>
                        <button onclick="markAsRead(<?php echo (int)($notif['id'] ?? 0); ?>)" style="background: none; border: none; cursor: pointer; color: #856404; font-size: 1.2rem;"><i class='bx bx-x-circle'></i></button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="metrics-row">
                <div class="metric-card">
                    <p class="metric-label">TOTAL RETURNED BOOKS</p>
                    <div class="metric-body">
                        <img src="/images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo (int)$totalReturned; ?></span>
                    </div>
                    <p class="metric-sub">All returned books</p>
                </div>

                <div class="metric-card">
                    <p class="metric-label">TOTAL BORROWED BOOKS</p>
                    <div class="metric-body">
                        <img src="/images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo (int)$totalBorrowed; ?></span>
                    </div>
                    <p class="metric-sub">All borrowed books</p>
                </div>

                <div class="metric-card">
                    <p class="metric-label">TOTAL PENDING RETURN</p>
                    <div class="metric-body">
                        <img src="/images/book-icon.png" alt="Book">
                        <span class="metric-value"><?php echo (int)$totalPending; ?></span>
                    </div>
                    <p class="metric-sub">All unreturned books</p>
                </div>

                <div class="metric-card credit-card" style="background-color: <?php echo ((int)$credit_score <= 5) ? '#e74c3c' : 'var(--main-color)'; ?>;">
                    <p class="metric-label">YOUR CREDIT SCORE</p>
                    <div class="metric-body">
                        <i class='bx bxs-star' style="font-size: 3rem; color: #fff;"></i>
                        <span class="metric-value"><?php echo (int)$credit_score; ?></span>
                    </div>
                    <p class="metric-sub tooltip-trigger" data-tooltip="<?php echo htmlspecialchars($credit_tooltip); ?>" style="color: #fff; font-weight: bold;">
                        <?php echo ((int)$credit_score > 5) ? 'GOOD STANDING' : 'BAD STANDING'; ?>
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
                                        $displayFine = $row['fine_amount'] ?? 0;
                                        if (($row['status'] ?? null) === 'borrowed' && !empty($row['due_date'])) {
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
                                        <td><?php echo htmlspecialchars($row['title'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['author'] ?? ''); ?></td>
                                        <td><?php echo !empty($row['borrow_date']) ? date("M d, Y", strtotime($row['borrow_date'])) : ''; ?></td>
                                        <td><?php echo !empty($row['due_date']) ? date("M d, Y", strtotime($row['due_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <?php if (($row['status'] ?? null) === 'borrowed'): ?>
                                                <?php $isOverdue = (!empty($row['due_date']) && time() > strtotime($row['due_date'])); ?>
                                                <?php if ($isOverdue): ?>
                                                    <span class="status-badge on-queue">Payment Required</span>
                                                <?php else: ?>
                                                    <button onclick="returnBook(<?php echo (int)($row['borrow_id'] ?? 0); ?>)" class="return-action-btn">Return Now</button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php echo !empty($row['return_date']) ? date("M d, Y", strtotime($row['return_date'])) : ''; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>₱<?php echo number_format((float)$displayFine, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No borrowing history found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

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
                                        <td><strong><?php echo htmlspecialchars($item['title'] ?? ''); ?></strong></td>
                                        <td style="color: #e74c3c; font-weight: 800;">₱<?php echo number_format((float)($item['amount'] ?? 0), 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align:center; padding: 40px; color: #888;">No current outstanding fines. Keep up the good work!</p>
                    <?php endif; ?>
                </div>

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

                                if (($row['status'] ?? null) === 'borrowed' && !empty($row['due_date'])) {
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

                                if ((float)$fine > 0):
                                    $hasFines = true;
                                    $dueDateStr = !empty($row['due_date']) ? date("M d, Y", strtotime($row['due_date'])) : 'N/A';
                                    $returnDateStr = (($row['status'] ?? null) === 'returned' && !empty($row['return_date']))
                                        ? date("M d, Y", strtotime($row['return_date']))
                                        : '<span style="color: #e74c3c; font-weight: bold;">STILL OUT</span>';
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['title'] ?? ''); ?></strong></td>
                                    <td><?php echo $dueDateStr; ?></td>
                                    <td><?php echo $returnDateStr; ?></td>
                                    <td>
                                        <?php if (($row['status'] ?? null) === 'borrowed' && $isOverdue): ?>
                                            <span class="status-badge on-queue">Overdue Penalty</span>
                                        <?php else: ?>
                                            <span class="status-badge unavailable">Late Return</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #e74c3c; font-weight: 800;">₱<?php echo number_format((float)$fine, 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="5" style="text-align: right; font-size: 0.85rem; color: #666; padding-top: 0; padding-bottom: 10px;">
                                        Status:
                                        <?php
                                        $isPaid = !empty($row['is_fine_paid']);
                                        echo $isPaid
                                            ? '<span style="color: #28a745; font-weight: bold;">PAID</span>'
                                            : '<span style="color: #dc3545; font-weight: bold;">UNPAID</span>';
                                        ?>
                                    </td>
                                </tr>
                            <?php
                                endif;
                            endforeach;

                            if (!$hasFines):
                            ?>
                                <tr><td colspan="5" style="text-align:center; padding: 40px; color: #888;">No penalty history found. Keep up the good work!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

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
                                    <?php
                                        $isFirst = ((int)($res['res_id'] ?? 0) === (int)($res['next_in_line_res_id'] ?? 0));
                                        $isAvailable = ((int)($res['is_currently_borrowed'] ?? 0) === 0);
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($res['title'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($res['author'] ?? ''); ?></td>
                                        <td><?php echo !empty($res['reservation_date']) ? date("M d, Y", strtotime($res['reservation_date'])) : ''; ?></td>
                                        <td>
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
                                                    <button onclick="processAction('borrow', <?php echo (int)($res['book_id'] ?? 0); ?>)" class="borrow-btn" style="padding: 8px 12px; font-size: 0.8rem;"><i class='bx bx-book-reader'></i> Borrow Now</button>
                                                <?php endif; ?>
                                                <button onclick="cancelReservation(<?php echo (int)($res['res_id'] ?? 0); ?>)" class="remove-btn" title="Cancel Reservation">
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
                            <span><?php echo htmlspecialchars($item['title'] ?? ''); ?> (Fine)</span>
                            <span>₱<?php echo number_format((float)($item['amount'] ?? 0), 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <hr class="receipt-divider">
            <div class="receipt-total">
                <span>TOTAL AMOUNT</span>
                <span>₱<?php echo number_format((float)$totalFinesOwed, 2); ?></span>
            </div>
            <div class="modal-actions" style="margin-top: 30px;">
                <button class="cart-btn" onclick="closeReceiptModal()">Cancel</button>
                <button class="borrow-btn" onclick="confirmPayment()">Confirm Payment</button>
            </div>
        </div>
    </div>

    <script src="public/js/profile.js"></script>
</body>
</html>
