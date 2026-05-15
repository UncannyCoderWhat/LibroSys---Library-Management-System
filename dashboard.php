<?php
require_once 'dashboardController.php';
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

    <div class="sidebar">
        <div class="nav-top">
            <a href="dashboard.php">Dashboard</a>
            <a href="books.php">Books</a>
            <a href="borrowing.php">Borrowing</a>
            <a href="borrowed.php">Borrowed Books</a>
            <a href="users.php">Users</a>
            <a href="settings.php">Settings</a>
        </div>

        <div class="nav-bottom">
            <a href="login.php">Logout</a>
        </div>

    </div>

    <div class="topbar">
        <img src="images/LibroSys.png" alt="Logo">
    </div>


    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="dashboard-bar">
            <div class="left-title">
                <i class="fa-solid fa-bars"></i>
                <span>Dashboard</span>
            </div>
            <div class="right-profile">
                <span>Admin</span>
                <i class="fa-solid fa-circle-user"></i>
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
                                <tr>
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