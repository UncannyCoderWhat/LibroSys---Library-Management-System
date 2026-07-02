<?php
// View template: expects $currentPage, $currentlyBorrowedCount, $activities, $reservations, $totalFinesAccumulated
$currentPage = 'ledger';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Ledger</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-container">
        <div class="section-header">
            <div class="header-left">
                <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="menu-icon" alt="Menu">
                <h2>Ledger</h2>
            </div>

            <div class="header-right">
                <span>Admin</span>
                <img src="<?php echo $base_url; ?>/images/profile.png" class="profile-pic" alt="Admin Profile">
            </div>
        </div>

        <div class="ledger-top-cards">
            <div class="ledger-info-card">
                <div class="card-left" style="display: flex; align-items: center; gap: 18px;">
                    <i class="fa-solid fa-book-reader" style="font-size: 45px; color: black;"></i>

                    <div class="left-text">
                        <h3>Currently Borrowed Books</h3>
                        <div class="borrow-count"><?php echo htmlspecialchars($currentlyBorrowedCount ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <div class="ledger-info-card">
                <div class="card-left" style="display: flex; align-items: center; gap: 18px;">
                    <i class="fa-solid fa-file-invoice-dollar" style="font-size: 45px; color: black;"></i>

                    <div class="left-text">
                        <h3>Total Fines Accumulated</h3>
                        <div class="borrow-count">₱<?php echo number_format((float)($totalFinesAccumulated ?? 0), 2); ?></div>
                    </div>
                </div>
            </div>

            <div class="ledger-info-card">
                <div class="right-text">
                    <h3>Ledger <br></h3>
                    <h4>
                        1 - 3 days late :  ₱50/day <br>
                        4 - 10 days late :  ₱100/day <br>
                        11+ days late :  ₱150/day
                    </h4>
                </div>
                <i class="fa-solid fa-receipt" style="font-size: 45px; color: black;"></i>
            </div>
        </div>

        <section class="ledger-table-container">
            <div class="ledger-table">
                <table class="ledger-activity-table">
                    <thead>
                        <tr>
                            <th>Borrowed By</th>
                            <th>Book Borrowed</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Date Returned</th>
                            <th>Days Late</th>
                            <th>Total Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $row): ?>
                                <?php
                                    $borrowDate   = date("F d, Y", strtotime($row['borrow_date']));
                                    $dueDate      = !empty($row['due_date']) ? date("F d, Y", strtotime($row['due_date'])) : 'N/A';
                                    $returnDate   = !empty($row['return_date']) ? date("F d, Y", strtotime($row['return_date'])) : '---';
                                    $statusClass  = strtolower($row['status']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowDate); ?></td>
                                    <td><?php echo htmlspecialchars($dueDate); ?></td>
                                    <td><?php echo htmlspecialchars($returnDate); ?></td>
                                    <td><?php echo htmlspecialchars($row['days_late'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_fine'] ?? '₱0.00'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data-cell">No recent borrow activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ledger-table-container" style="margin-top: 50px;">
            <h2 class="section-title">ACTIVE RESERVATIONS (WAITLIST)</h2>
            <div class="ledger-table">
                <table class="ledger-activity-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Book Title</th>
                            <th>Reservation Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reservations)): ?>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($res['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($res['book_title']); ?></td>
                                    <td><?php echo date("F d, Y", strtotime($res['reservation_date'])); ?></td>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data-cell">No active reservations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
