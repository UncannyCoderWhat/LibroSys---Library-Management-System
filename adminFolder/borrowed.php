<?php 
$currentPage = 'borrowed';
include "sidebar.php"; 
require_once '../dbForLogin/db.php';
require_once 'dashboardController.php';

$controller = new DashboardController($pdo);
$activeBorrows = $controller->getActiveBorrows();
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">

<div class="main-content-container">
<div class="topbar">
        <img src="../images/LibroSys.png" alt="Logo">
</div>

<div class="section-header">
    <div class="header-left">
        <img src="../images/lineMenu.png" class="menu-icon" alt="Menu">
        <h2>Borrowed Books</h2>
    </div>
    <div class="header-right">
        <span>Admin</span>
        <img src="../images/profile.png" class="profile-pic" alt="Admin Profile">
    </div>
</div>

    <!-- Search Bar Section -->
    <section class="activity-section" style="margin-top: 25px;">
        <div class="search-filter-container">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="borrowerSearch" placeholder="Search by borrower name or ID...">
            </div>
        </div>
    </section>

    <div class="borrowed-books-container">
        <?php if (!empty($activeBorrows)): ?>
            <?php foreach ($activeBorrows as $borrow): ?>
                <div class="book-card" 
                     data-borrower="<?php echo strtolower(htmlspecialchars($borrow['borrower_name'])); ?>" 
                     data-id="<?php echo strtolower(htmlspecialchars($borrow['borrower_id'])); ?>">
                    
                    <img src="../<?php echo htmlspecialchars($borrow['cover_path']); ?>" alt="Cover">

                    <div class="book-info">
                        <p><strong>Title:</strong> <?php echo htmlspecialchars($borrow['title']); ?></p>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($borrow['author']); ?></p>
                        <p><strong>Type:</strong> <?php echo $borrow['is_exclusive'] ? 'Exclusive' : 'Regular'; ?></p>
                        <p><strong>Date Borrowed:</strong> <?php echo date("F d, Y", strtotime($borrow['borrow_date'])); ?></p>
                        <p><strong>Time Borrowed:</strong> <?php echo date("h:i A", strtotime($borrow['borrow_date'])); ?></p>
                        <p><strong>Borrower ID:</strong> <?php echo htmlspecialchars($borrow['borrower_id']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data-message" style="width: 100%; text-align: center; margin-top: 50px; color: #7f8c8d;">
                <i class="fa-solid fa-book-bookmark" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                <p>No books are currently borrowed.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const borrowerSearch = document.getElementById('borrowerSearch');
    const borrowedContainer = document.getElementById('borrowedContainer');

    borrowerSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = borrowedContainer.querySelectorAll('.book-card');

        cards.forEach(card => {
            const borrowerName = card.getAttribute('data-borrower');
            const borrowerId = card.getAttribute('data-id');

            const matches = borrowerName.includes(searchTerm) || borrowerId.includes(searchTerm);
            card.style.display = matches ? 'flex' : 'none';
        });
    });
});
</script>
