<?php
// View template: expects $data injected by wrapper/entrypoint.
$exclusive_books = $data['exclusive_books'] ?? [];
$regular_books = $data['regular_books'] ?? [];
$borrowed_books = $data['borrowed_books'] ?? [];
$current_score = $data['current_score'] ?? 0;
$cartCount = $data['cartCount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Browse</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/css/clientstyle.css">
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
</script>
</head>
<body class="browse-page-body">
    <header>
        <div class="client-top-bar">
            <img src="/images/LibroSys.png" alt="Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="index.php?page=home"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=browse" class="active"><i class='bx bx-compass'></i>Browse</a>
                    <a href="index.php?page=cart" class="nav-cart-link">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo (int)$cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=profile"><i class='bx bx-user-circle'></i>Profile</a>
                    <label class="switch-container">
                    <input type="checkbox" id="theme-toggle" class="switch-input">
                    <div class="switch-track">
                        <div class="switch-thumb"></div>
                    </div>
                    <span class="switch-label">Dark Mode</span>
                   </label>
                </div>
            </nav>
        </div>
    </header>

    <main class="browse-container">
        <h1 class="page-title">Featured</h1>

        <!-- Search Bar Section -->
        <section class="search-section" style="margin-bottom: 2rem;">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class='bx bx-search'></i>
                    <input type="text" id="browseSearch" placeholder="Search for books or authors...">
                </div>
                <div class="filter-box" style="margin-left: 10px;">
                    <select id="genreFilter" style="padding: 12px 20px; border-radius: 50px; border: 1px solid #ddd; outline: none; font-weight: 600; cursor: pointer;">
                        <option value="all">All Genres</option>
                        <option value="fiction">Fiction</option>
                        <option value="non-fiction">Non-Fiction</option>
                        <option value="mystery">Mystery</option>
                        <option value="sci-fi">Sci-Fi</option>
                        <option value="fantasy">Fantasy</option>
                        <option value="romance">Romance</option>
                        <option value="horror">Horror</option>
                        <option value="history">History</option>
                        <option value="biography">Biography</option>
                        <option value="action">Action</option>
                    </select>
                </div>
            </div>
        </section>

        <?php if ($current_score > 5): ?>
        <section class="shelf-section">
            <h2 class="shelf-title">Exclusive</h2>
            <div class="shelf-wrapper">

                <button class="scroll-arrow left" id="scrollLeftBtn" aria-label="Scroll Left">
                    <i class='bx bx-chevron-left'></i>
                </button>

                <div class="book-grid shelf-grid" id="exclusiveGrid">
                    <?php foreach ($exclusive_books as $book): ?>
                        <div class="book-card"
                             onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)"
                             data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>"
                             data-author="<?php echo strtolower(htmlspecialchars($book['author'] ?? '')); ?>"
                             data-genre="<?php echo strtolower(htmlspecialchars($book['genre'] ?? '')); ?>">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? ''); ?>" alt="Cover" class="book-cover">
                            <div class="book-info">
                                <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                <p><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                                <p><?php echo htmlspecialchars($book['genre'] ?? ''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="scroll-arrow right" id="scrollRightBtn" aria-label="Scroll Right">
                    <i class='bx bx-chevron-right'></i>
                </button>
            </div>
        </section>
        <?php endif; ?>

        <section class="shelf-section">
            <h2 class="shelf-title">Regular</h2>
            <div class="book-grid-vertical shelf-grid">
                <?php foreach ($regular_books as $book): ?>
                    <div class="book-card"
                         onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)"
                         data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>"
                         data-author="<?php echo strtolower(htmlspecialchars($book['author'] ?? '')); ?>"
                         data-genre="<?php echo strtolower(htmlspecialchars($book['genre'] ?? '')); ?>">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? ''); ?>" alt="Cover" class="book-cover">
                        <div class="book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                            <p><?php echo htmlspecialchars($book['genre'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if (!empty($borrowed_books)): ?>
        <section class="shelf-section">
            <h2 class="shelf-title">Currently Out on Loan (Reservable)</h2>
            <div class="book-grid-vertical shelf-grid">
                <?php foreach ($borrowed_books as $book): ?>
                    <div class="book-card"
                         onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)"
                         data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>"
                         data-author="<?php echo strtolower(htmlspecialchars($book['author'] ?? '')); ?>"
                         data-genre="<?php echo strtolower(htmlspecialchars($book['genre'] ?? '')); ?>">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? ''); ?>" alt="Cover" class="book-cover" style="filter: grayscale(80%);">
                        <div class="book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <div id="noBooksFoundMessage" class="no-books-found-message" style="display: none;">
        <i class='bx bx-info-circle'></i> No books found matching your search criteria.
    </div>

    <!-- Borrow Modal -->
    <div id="borrowModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeBorrowModal()">&times;</span>
            <div class="modal-flex">
                <img id="modalCover" src="" alt="Cover">
                <div class="modal-details">
                    <h2 id="modalTitle"></h2>
                    <p id="modalAuthor"></p>
                    <hr>
                    <p><strong>Borrow Duration:</strong> 7 Days</p>
                    <p><strong>Condition:</strong> Must return on or before due date to maintain Credit Score.</p>
                    <div class="modal-actions">
                        <button class="cart-btn" onclick="processAction('add_to_cart')"><i class='bx bx-cart-add'></i> Add to Cart</button>
                        <button id="modalBorrowBtn" class="borrow-btn" onclick="processAction('borrow')"><i class='bx bx-book-reader'></i> Rent Now</button>
                        <button id="modalReserveBtn" class="reserve-btn" style="display:none;" onclick="processAction('reserve')"><i class='bx bx-calendar-check'></i> Reserve Book</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="selectedBookId">

    <script src="public/js/browse.js"></script>
</body>
</html>
