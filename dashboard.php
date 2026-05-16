<?php
// Start session to protect the admin page (assuming you use sessions)
session_start();
include "sidebar.php" ;

// Include the database connection and the controller
require_once 'dbForLogin/db.php';
require_once 'dashboardController.php';

try {
    // Create controller instance
    $dashboardController = new DashboardController($pdo);

    // Get metrics data
    $metrics = $dashboardController->getDashboardMetrics();
    $totalBooks = $metrics['totalBooks'];
    $availableBooks = $metrics['availableBooks'];
    $borrowedBooks = $metrics['borrowedBooks'];
    $exclusiveBooks = $metrics['exclusiveBooks'];

    // Get recent activities
    $activities = $dashboardController->getRecentActivities(10);

} catch (Exception $e) {
    error_log("Dashboard controller error: " . $e->getMessage());
    // Set default values on error so the page doesn't break
    $totalBooks = $availableBooks = $borrowedBooks = $exclusiveBooks = 0;
    $activities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Dashboard</span>
                </div>
                <div class="right-profile">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="images/profile.png" alt="Admin Image">
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
                    <span class="card-value"><?php echo htmlspecialchars($totalBooks); ?></span>
                    <span class="card-subtext">All books in library</span>
                </div>
            </div>

            <div class="metric-card card-available">
                <i class="fa-solid fa-circle-check card-icon"></i>
                <div class="card-info">
                    <span class="card-label">AVAILABLE BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($availableBooks); ?></span>
                    <span class="card-subtext">Books ready to borrow</span>
                </div>
            </div>

            <div class="metric-card card-borrowed">
                <i class="fa-solid fa-hand-holding-hand card-icon"></i>
                <div class="card-info">
                    <span class="card-label">BORROWED BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($borrowedBooks); ?></span>
                    <span class="card-subtext">Currently Borrowed</span>
                </div>
            </div>

            <div class="metric-card card-exclusive">
                <i class="fa-solid fa-award card-icon"></i>
                <div class="card-info">
                    <span class="card-label">EXCLUSIVE BOOKS</span>
                    <span class="card-value"><?php echo htmlspecialchars($exclusiveBooks); ?></span>
                    <span class="card-subtext">Special Collection</span>
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
                                    $statusClass   = (strtolower($row['status']) === 'borrowed') ? 'borrowed' : 'returned';
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