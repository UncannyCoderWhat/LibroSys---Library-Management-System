<?php
// View template: expects $currentPage, $totalBooks, $availableBooks, $borrowedBooks, $exclusiveBooks, $currentlyBorrowedCount, $totalFinesAccumulated, $activities.
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
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
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

        <!-- Library Analytics Summary Metrics Grid -->
        <section class="metrics-grid">
            <div class="metric-card card-total">
                <i class="fa-solid fa-book-open card-icon"></i>
                <div class="card-info">
                    <span class="card-label">TOTAL BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($totalBooks ?? 0); ?></span>
                    <span class="card-subtext">All books in library</span>
                </div>
            </div>

            <div class="metric-card card-available">
                <i class="fa-solid fa-circle-check card-icon"></i>
                <div class="card-info">
                    <span class="card-label">AVAILABLE BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($availableBooks ?? 0); ?></span>
                    <span class="card-subtext">Books ready to borrow</span>
                </div>
            </div>

            <div class="metric-card card-borrowed">
                <i class="fa-solid fa-hand-holding-hand card-icon"></i>
                <div class="card-info">
                    <span class="card-label">BORROWED BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($borrowedBooks ?? 0); ?></span>
                    <span class="card-subtext">Currently Borrowed</span>
                </div>
            </div>

            <div class="metric-card card-exclusive">
                <i class="fa-solid fa-award card-icon"></i>
                <div class="card-info">
                    <span class="card-label">EXCLUSIVE BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($exclusiveBooks ?? 0); ?></span>
                    <span class="card-subtext">Special Collection</span>
                </div>
            </div>
        </section>

        <!-- NEW -->
        <section class="ledger-grid">
            <div class="ledger-top-cards">
                <div class="ledger-info-card">
                    <div class="card-left" style="display: flex; align-items: center; gap: 18px;">
                        <i class="fa-solid fa-book-reader" style="font-size: 45px; color: black;"></i>

                        <div class="left-text">
                            <span class="card-label">CURRENTLY BORROWED BOOKS</span>
                            <span class="card-value"><?php echo htmlspecialchars($currentlyBorrowedCount ?? 0); ?></span>
                        </div>
                    </div>
                </div>

                <div class="ledger-info-card">
                    <div class="card-left" style="display: flex; align-items: center; gap: 18px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="font-size: 45px; color: black;"></i>

                        <div class="left-text">
                            <span class="card-label">TOTAL FINES ACCUMULATED</span>
                            <span class="card-value">₱<?php echo number_format((float)($totalFinesAccumulated ?? 0), 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="ledger-info-card">
                    <div class="right-text">
                        <span class="fine-guide">FINE GUIDE <br></span>
                        <span class="card-label">
                            1 - 3 days late :  ₱50/day <br>
                            4 - 10 days late :  ₱100/day <br>
                            11+ days late :  ₱150/day
                        </span>
                    </div>
                    <i class="fa-solid fa-receipt" style="font-size: 45px; color: black;"></i>
                </div>
            </div>
        </section>

        <!-- Activity Ledger Section -->
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
                                    $formattedDate = date("F d, Y", strtotime($row['borrow_date']));
                                    $statusClass   = strtolower($row['status']);
                                ?>
                                <tr class="activity-row">
                                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $statusClass; ?>">
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
    </main>
</body>
</html>
