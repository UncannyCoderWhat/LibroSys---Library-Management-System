<?php
// View template for Admin Borrowed Books page
// Expects: $currentPage, $activeBorrows
$currentPage = 'borrowed';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Borrowed Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-container">
        <div class="section-header">
            <div class="header-left">
                <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="menu-icon" alt="Menu">
                <h2>Borrowed Books</h2>
            </div>
            <div class="header-right">
                <span>Admin</span>
                <img src="<?php echo $base_url; ?>/images/profile.png" class="profile-pic" alt="Admin Profile">
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
                <div id="borrowedContainer" class="borrowed-books-container">
                    <?php foreach ($activeBorrows as $borrow): ?>
                        <div class="book-card"
                             data-borrower="<?php echo strtolower(htmlspecialchars($borrow['borrower_name'] ?? '')); ?>"
                             data-id="<?php echo strtolower(htmlspecialchars($borrow['borrower_id'] ?? '')); ?>">
                            <img src="<?php echo $base_url; ?>/<?php echo htmlspecialchars($borrow['cover_path'] ?? ''); ?>" alt="Cover">

                            <div class="book-info">
                                <p><strong>Title:</strong> <?php echo htmlspecialchars($borrow['title'] ?? ''); ?></p>
                                <p><strong>Author:</strong> <?php echo htmlspecialchars($borrow['author'] ?? ''); ?></p>
                                <p><strong>Type:</strong> <?php echo !empty($borrow['is_exclusive']) ? 'Exclusive' : 'Regular'; ?></p>
                                <p><strong>Date Borrowed:</strong> <?php echo date("F d, Y", strtotime($borrow['borrow_date'] ?? 'now')); ?></p>
                                <p><strong>Time Borrowed:</strong> <?php echo date("h:i A", strtotime($borrow['borrow_date'] ?? 'now')); ?></p>
                                <p><strong>Borrower ID:</strong> <?php echo htmlspecialchars($borrow['borrower_id'] ?? ''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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

        if (!borrowerSearch || !borrowedContainer) return;

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
</body>
</html>
