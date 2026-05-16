<?php 
include "sidebar.php"; 
?>

<link rel="stylesheet" href="style.css">

<div class="main-content-container">
<div class="topbar">
        <img src="images/LibroSys.png" alt="Logo">
</div>

    <div class="section-header">
        <div class="header-left">
            <img src="images/lineMenu.png" class="menu-icon" alt="Menu">
            <h2>Ledger</h2>
        </div>

        <div class="header-right">
            <span>Admin</span>
            <img src="images/profile.png" class="profile-pic" alt="Admin Profile">
        </div>
    </div>

    <div class="ledger-top-cards">
        <div class="ledger-info-card">
            <div class="card-left">
                <img src="borrow.png" class="ledger-card-icon">

                <div class="left-text">
                   <h3>Currently Borrowed Books</h3>
                    <div class="borrow-count">208</div>
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
            <img src="ledger.png" class="ledger-card-icon">
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
                                
                                $statusClass  = (strtolower($row['status']) === 'borrowed') ? 'borrowed' : 'returned';
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
                            <!-- Ginawang 7 ang colspan para sakop ang lahat ng columns -->
                            <td colspan="7" class="no-data-cell">No recent borrow activity found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
