<?php
session_start();
require_once '../dbForLogin/db.php';

// Check user credit score
$user_id = $_SESSION['user_id']; // This is the integer ID
$userStmt = $pdo->prepare("SELECT credit_score FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$userData = $userStmt->fetch();
$current_score = $userData['credit_score'] ?? 0;

// Fetch Exclusive Books
$exclusive_books = $pdo->query("
    SELECT b.*, 
    (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as is_borrowed 
    FROM books b 
    WHERE is_exclusive = 1 AND is_deleted = 0
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Regular Books
$regular_books = $pdo->query("
    SELECT b.*, 
    (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as is_borrowed 
    FROM books b 
    WHERE is_exclusive = 0 AND is_deleted = 0
")->fetchAll(PDO::FETCH_ASSOC);

$cartCount = isset($_SESSION['borrow_cart']) ? count($_SESSION['borrow_cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Browse</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="clientstyle.css">
</head>
<body class="browse-page-body">
    <header>
        <div class="client-top-bar">
            <img src="../images/LibroSys.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="home.php"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="browse.php" class="active"><i class='bx bx-compass'></i>Browse</a>
                    <a href="cart.php" class="nav-cart-link">
                        <i class='bx bx-cart'></i>Cart
                        <?php if($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php"><i class='bx bx-user-circle'></i>Profile</a>
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
                             data-title="<?php echo strtolower(htmlspecialchars($book['title'])); ?>" 
                             data-author="<?php echo strtolower(htmlspecialchars($book['author'])); ?>"
                             data-genre="<?php echo strtolower(htmlspecialchars($book['genre'])); ?>">
                            <img src="../<?php echo htmlspecialchars($book['cover_path']); ?>" alt="Cover" class="book-cover">
                            <div class="book-info">
                                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                <p><?php echo htmlspecialchars($book['author']); ?></p>
                                <p><?php echo htmlspecialchars($book['genre']); ?></p>
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
                         data-title="<?php echo strtolower(htmlspecialchars($book['title'])); ?>" 
                         data-author="<?php echo strtolower(htmlspecialchars($book['author'])); ?>"
                         data-genre="<?php echo strtolower(htmlspecialchars($book['genre'])); ?>">
                        <img src="../<?php echo htmlspecialchars($book['cover_path']); ?>" alt="Cover" class="book-cover">
                        <div class="book-info">
                            <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p><?php echo htmlspecialchars($book['author']); ?></p>
                            <p><?php echo htmlspecialchars($book['genre']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
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
                        <button id="modalReserveBtn" class="borrow-btn" style="display:none; background-color: #3498db;" onclick="processAction('reserve')">Reserve Book</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="selectedBookId">

    <script>
        function openBorrowModal(book) {
            document.getElementById('selectedBookId').value = book.id;
            document.getElementById('modalTitle').innerText = book.title;
            document.getElementById('modalAuthor').innerText = "By " + book.author;
            document.getElementById('modalCover').src = "../" + book.cover_path;

            if (book.is_borrowed > 0) {
                document.getElementById('modalBorrowBtn').style.display = 'none';
                document.getElementById('modalReserveBtn').style.display = 'block';
            } else {
                document.getElementById('modalBorrowBtn').style.display = 'block';
                document.getElementById('modalReserveBtn').style.display = 'none';
            }

            document.getElementById('borrowModal').style.display = 'block';
        }

        function closeBorrowModal() {
            document.getElementById('borrowModal').style.display = 'none';
        }

        function processAction(actionType) {
            const bookId = document.getElementById('selectedBookId').value;
            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('action', actionType);

            fetch('borrow_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') closeBorrowModal();
                if (actionType === 'borrow' && data.status === 'success') window.location.href = 'profile.php';
            });
        }

        // Search and Filter Logic
        const browseSearch = document.getElementById('browseSearch');
        const genreFilter = document.getElementById('genreFilter');
        
        function filterBrowse() {
                const searchTerm = this.value.toLowerCase();
                const selectedGenre = genreFilter.value.toLowerCase();
                const shelves = document.querySelectorAll('.shelf-section');
                const noBooksFoundMessage = document.getElementById('noBooksFoundMessage');
                let totalVisibleCards = 0;

                shelves.forEach(shelf => {
                    const cards = shelf.querySelectorAll('.book-card');
                    let hasVisibleCard = false;

                    cards.forEach(card => {
                        const title = card.getAttribute('data-title');
                        const author = card.getAttribute('data-author');
                        const genre = card.getAttribute('data-genre');

                        const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                        const matchesGenre = selectedGenre === 'all' || genre === selectedGenre;

                        if (matchesSearch && matchesGenre) {
                            card.style.display = 'flex';
                            hasVisibleCard = true;
                            totalVisibleCards++;
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    shelf.style.display = hasVisibleCard ? 'block' : 'none';
                });

                // Show/hide "No books found" message
                noBooksFoundMessage.style.display = (totalVisibleCards === 0) ? 'flex' : 'none';
        }

        if (browseSearch) browseSearch.addEventListener('input', filterBrowse);
        if (genreFilter) genreFilter.addEventListener('change', filterBrowse);

        const exclusiveGrid = document.getElementById('exclusiveGrid');
        const scrollLeftBtn = document.getElementById('scrollLeftBtn');
        const scrollRightBtn = document.getElementById('scrollRightBtn');

        if (exclusiveGrid && scrollRightBtn && scrollLeftBtn) {
            scrollRightBtn.addEventListener('click', () => {
                exclusiveGrid.scrollBy({ left: exclusiveGrid.clientWidth, behavior: 'smooth' });
            });

            scrollLeftBtn.addEventListener('click', () => {
                exclusiveGrid.scrollBy({ left: -exclusiveGrid.clientWidth, behavior: 'smooth' });
            });
        }
    </script>
</body>
</html>