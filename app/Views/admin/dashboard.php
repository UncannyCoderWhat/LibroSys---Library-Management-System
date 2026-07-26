<?php
// View template: expects $currentPage, $totalBooks, $availableBooks, $borrowedBooks, $exclusiveBooks, $currentlyBorrowedCount, $totalFinesAccumulated, $activties, $reservations.
$currentPage = 'dashboard';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Dashboard</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <!-- FontAwesome Icons Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="content-workspace">
        <!-- Sub-Topbar Navigation -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Dashboard</span>
                </div>
                <div class="right-profile">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="<?php echo $base_url; ?>/images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Metrics Grid -->
        <section class="metrics-grid">
            <!-- Total Books -->
            <div class="metric-card card-total">
                <i class="fa-solid fa-book-open card-icon"></i>
                <div class="card-info">
                    <span class="card-label">TOTAL BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($totalBooks ?? 0); ?></span>
                    <span class="card-subtext">All books in library</span>
                </div>
            </div>

            <!-- Available Books -->
            <div class="metric-card card-available">
                <i class="fa-solid fa-circle-check card-icon"></i>
                <div class="card-info">
                    <span class="card-label">AVAILABLE BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($availableBooks ?? 0); ?></span>
                    <span class="card-subtext">Books ready to borrow</span>
                </div>
            </div>

            <!-- Borrowed Books -->
            <div class="metric-card card-borrowed">
                <i class="fa-solid fa-hand-holding-hand card-icon"></i>
                <div class="card-info">
                    <span class="card-label">BORROWED BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($borrowedBooks ?? 0); ?></span>
                    <span class="card-subtext">Currently Borrowed</span>
                </div>
            </div>

            <!-- Exclusive Books -->
            <div class="metric-card card-exclusive">
                <i class="fa-solid fa-award card-icon"></i>
                <div class="card-info">
                    <span class="card-label">SPECIAL BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($exclusiveBooks ?? 0); ?></span>
                    <span class="card-subtext">Special Collection</span>
                </div>
            </div>

            <!-- Merged: Currently Borrowed -->
            <div class="metric-card card-borrowed">
                <i class="fa-solid fa-book-reader card-icon"></i>
                <div class="card-info">
                    <span class="card-label">CURRENTLY BORROWED</span>
                    <span class="card-value"><?php echo htmlspecialchars($currentlyBorrowedCount ?? 0); ?></span>
                    <span class="card-subtext">Books out with members</span>
                </div>
            </div>

            <!-- Merged: Total Fines -->
            <div class="metric-card card-exclusive">
                <i class="fa-solid fa-file-invoice-dollar card-icon"></i>
                <div class="card-info">
                    <span class="card-label">TOTAL FINES ACCUMULATED</span>
                    <span class="card-value">₱<?php echo number_format((float)($totalFinesAccumulated ?? 0), 2); ?></span>
                    <span class="card-subtext">Unpaid library fees</span>
                </div>
            </div>

            <!-- Merged: Fine Rate Guide -->
            <div class="metric-card card-borrowed">
                <i class="fa-solid fa-receipt card-icon"></i>
                <div class="card-info">
                    <span class="card-label">FINE RATE GUIDE</span>
                    <span class="fine-guide-text">
                        1–3 days late: <strong>₱50/day</strong><br>
                        4–10 days late: <strong>₱100/day</strong><br>
                        11+ days late: <strong>₱150/day</strong>
                    </span>
                </div>
            </div>
        </section>

        <!-- RECENT BORROW ACTIVITY -->
        <section class="activity-section">
            <h2 class="section-title">RECENT BORROW ACTIVITY</h2>
            <div class="table-wrapper">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrowed By</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $row): ?>
                                <?php 
                                    $statusClass = strtolower($row['status'] ?? '');
                                    if (!in_array($statusClass, ['borrowed', 'returned'])) {
                                        continue;
                                    }
                                    $formattedDate = date("F d, Y", strtotime($row['borrow_date']));
                                ?>
                                <tr class="activity-row">
                                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>">
                                            <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data-cell">No recent borrow activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- RECENT READING AND BOOKMARK ACTIVITY -->
        <section class="activity-section">
            <h2 class="section-title">RECENT READING AND BOOKMARK ACTIVITY</h2>
            <div class="table-wrapper">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrowed By</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $row): ?>
                                <?php 
                                    $statusClass = strtolower($row['status'] ?? '');
                                    if (!in_array($statusClass, ['bookmarked', 'reading'])) {
                                        continue;
                                    }
                                    $formattedDate = date("F d, Y", strtotime($row['borrow_date']));
                                ?>
                                <tr class="activity-row">
                                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>">
                                            <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data-cell">No recent activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FIXED: Active Reservation Section -->
        <section class="activity-section">
            <h2 class="section-title">ACTIVE RESERVATIONS (WAITLIST)</h2>
            <div class="table-wrapper">
                <table class="activity-table">
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
                                            $hasCopies = ((int)($res['total_copies'] ?? 0) > 0);
                                            $isAvailable = ($hasCopies && (int)($res['is_currently_borrowed'] ?? 0) === 0);
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
    </main>
</body>
</html>