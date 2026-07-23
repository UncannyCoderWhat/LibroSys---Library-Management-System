<?php
// View template: expects $data injected by wrapper/entrypoint.
$readingBooks = $data['readingBooks'] ?? [];
$bookmarkedBooks = $data['bookmarkedBooks'] ?? [];
$borrowedBooks = $data['borrowedBooks'] ?? [];
$historyBooks = $data['historyBooks'] ?? [];
$cartCount = $data['cartCount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - My Library</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/clientstyle.css">
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>
<body>
<img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">
<?php
// DEBUG: Show query results
$debug_mode = true;
$debugInfo = $data['debugInfo'] ?? [];
if ($debug_mode):
?>
<div style="background: #1a1a2e; color: #e94560; padding: 12px 24px; text-align: center; font-size: 13px; border-bottom: 1px solid #e94560; position: relative; z-index: 10;">
    <strong>Debug:</strong>
    User ID: <?php echo (int)($debugInfo['user_id'] ?? 0); ?> |
    Total borrows: <?php echo (int)($debugInfo['total_borrows'] ?? 0); ?> |
    Reading DB: <?php echo (int)($debugInfo['reading_count_db'] ?? 0); ?> |
    Bookmarked DB: <?php echo (int)($debugInfo['bookmarked_count_db'] ?? 0); ?> |
    Reading view: <?php echo count($readingBooks); ?> |
    Bookmarked view: <?php echo count($bookmarkedBooks); ?> |
    Borrowed: <?php echo count($borrowedBooks); ?> |
    History: <?php echo count($historyBooks); ?>
</div>
<?php endif; ?>

    <header>
        <div class="client-top-bar">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <button class="upgrade-btn" onclick="openPremiumModal()">Upgrade premium</button>
                    <a href="index.php?page=home"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=library" class="active"><i class='bx bx-book'></i>Library</a>
                    <div class="dpContainer">
                        <button class="dropdown"><i class='bx bx-down-arrow'></i>Browse</button>
                        <div class="dpwrapper">
                            <ul>
                                <li><a href="#" >History</a></li>
                                <li><a href="#" >Fiction</a></li>
                                <li><a href="#" >Drama</a></li>
                                <li><a href="#" >Fantasy</a></li>
                                <li><a href="#" >Horror</a></li>
                                <li><a href="#" >Thriller</a></li>
                                <li><a href="#" >Romance</a></li>
                                <li><a href="#" >Teen Fiction</a></li>
                                <li><a href="#" >Mystery</a></li>
                                <li><a href="#" >Adventure</a></li>
                                <li><a href="#" >Action</a></li>
                                <li><a href="#" >Fanfiction</a></li>
                            </ul>
                        </div>
                    </div>
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

    <main class="ls-home-main">
        <!-- Library Header -->
        <section class="lib-header">
            <div class="lib-header-content">
                <h1 class="lib-header-title">My Library</h1>
                <p class="lib-header-subtitle">All your books in one place — pick up where you left off.</p>
            </div>
        </section>

        <!-- Library Tabs Navigation -->
        <div class="lib-tabs">
            <button class="lib-tab lib-tab-active" onclick="switchLibTab('reading', this)">
                <i class='bx bx-book-reader'></i> Reading
                <?php if (count($readingBooks) > 0): ?>
                <span class="lib-tab-badge"><?php echo count($readingBooks); ?></span>
                <?php endif; ?>
            </button>
            <button class="lib-tab" onclick="switchLibTab('bookmarked', this)">
                <i class='bx bx-bookmark'></i> Bookmarked
                <?php if (count($bookmarkedBooks) > 0): ?>
                <span class="lib-tab-badge"><?php echo count($bookmarkedBooks); ?></span>
                <?php endif; ?>
            </button>
            <button class="lib-tab" onclick="switchLibTab('borrowed', this)">
                <i class='bx bx-book-alt'></i> Borrowed
                <?php if (count($borrowedBooks) > 0): ?>
                <span class="lib-tab-badge"><?php echo count($borrowedBooks); ?></span>
                <?php endif; ?>
            </button>
            <button class="lib-tab" onclick="switchLibTab('history', this)">
                <i class='bx bx-time'></i> History
            </button>
        </div>

        <!-- Reading Tab -->
        <div id="lib-tab-reading" class="lib-tab-content lib-tab-active-content">
            <?php if (!empty($readingBooks)): ?>
            <div class="lib-grid">
                <?php foreach ($readingBooks as $book): ?>
                <div class="lib-book-card" onclick="window.location.href='index.php?page=read&id=<?php echo (int)$book['id']; ?>'">
                    <div class="lib-book-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="lib-book-cover" loading="lazy">
                        <div class="lib-book-overlay">
                            <i class='bx bx-book-open'></i>
                            <span>Continue Reading</span>
                        </div>
                        <?php if (!empty($book['is_exclusive'])): ?>
                        <span class="ls-exclusive-badge">Exclusive</span>
                        <?php endif; ?>
                    </div>
                    <div class="lib-book-info">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        <div class="lib-book-meta">
                            <span class="lib-status-reading"><i class='bx bx-book-reader'></i> Reading</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="lib-empty-state">
                <i class='bx bx-book-reader'></i>
                <h3>No books in your reading list</h3>
                <p>Start reading by clicking "Read Now" on any book.</p>
                <a href="index.php?page=browse" class="lib-empty-btn">Browse Books</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bookmarked Tab -->
        <div id="lib-tab-bookmarked" class="lib-tab-content">
            <?php if (!empty($bookmarkedBooks)): ?>
            <div class="lib-grid">
                <?php foreach ($bookmarkedBooks as $book): ?>
                <div class="lib-book-card" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                    <div class="lib-book-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="lib-book-cover" loading="lazy">
                        <div class="lib-book-overlay">
                            <i class='bx bx-book-reader'></i>
                            <span>Read Now</span>
                        </div>
                        <?php if (!empty($book['is_exclusive'])): ?>
                        <span class="ls-exclusive-badge">Exclusive</span>
                        <?php endif; ?>
                    </div>
                    <div class="lib-book-info">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        <div class="lib-book-meta">
                            <span class="lib-status-bookmarked"><i class='bx bx-bookmark'></i> Bookmarked</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="lib-empty-state">
                <i class='bx bx-bookmark'></i>
                <h3>No bookmarks yet</h3>
                <p>Bookmark books you want to read later.</p>
                <a href="index.php?page=browse" class="lib-empty-btn">Browse Books</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Borrowed Tab -->
        <div id="lib-tab-borrowed" class="lib-tab-content">
            <?php if (!empty($borrowedBooks)): ?>
            <div class="lib-grid">
                <?php foreach ($borrowedBooks as $book): 
                    $isOverdue = !empty($book['due_date']) && time() > strtotime($book['due_date']);
                ?>
                <div class="lib-book-card" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                    <div class="lib-book-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="lib-book-cover" loading="lazy">
                        <div class="lib-book-overlay">
                            <i class='bx bx-detail'></i>
                            <span>View Details</span>
                        </div>
                        <?php if (!empty($book['is_exclusive'])): ?>
                        <span class="ls-exclusive-badge">Exclusive</span>
                        <?php endif; ?>
                        <?php if ($isOverdue): ?>
                        <span class="lib-overdue-badge">Overdue</span>
                        <?php endif; ?>
                    </div>
                    <div class="lib-book-info">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        <div class="lib-book-meta">
                            <?php if ($isOverdue): ?>
                            <span class="lib-status-overdue"><i class='bx bx-time'></i> Overdue</span>
                            <?php else: ?>
                            <span class="lib-status-borrowed"><i class='bx bx-book-alt'></i> Borrowed</span>
                            <?php endif; ?>
                            <?php if (!empty($book['due_date'])): ?>
                            <span class="lib-due-date">Due: <?php echo date('M d', strtotime($book['due_date'])); ?></span>
                            <?php endif; ?>
                            <?php if (!$isOverdue && !empty($book['borrow_id'])): ?>
                            <div class="lib-book-actions" onclick="event.stopPropagation();">
                                <button onclick="returnBook(<?php echo (int)$book['borrow_id']; ?>)" class="return-action-btn">Return</button>
                                <button onclick="extendBorrowing(<?php echo (int)$book['borrow_id']; ?>)" class="extend-action-btn">Extend</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="lib-empty-state">
                <i class='bx bx-book-alt'></i>
                <h3>No borrowed books</h3>
                <p>Borrow books to read them offline.</p>
                <a href="index.php?page=browse" class="lib-empty-btn">Browse Books</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- History Tab -->
        <div id="lib-tab-history" class="lib-tab-content">
            <?php if (!empty($historyBooks)): ?>
            <div class="lib-history-list">
                <?php foreach ($historyBooks as $book): ?>
                <div class="lib-history-item" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                    <div class="lib-history-cover">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" loading="lazy">
                    </div>
                    <div class="lib-history-info">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        <div class="lib-history-meta">
                            <span><i class='bx bx-calendar'></i> Returned: <?php echo !empty($book['return_date']) ? date('M d, Y', strtotime($book['return_date'])) : 'N/A'; ?></span>
                            <?php if (!empty($book['fine_amount']) && (float)$book['fine_amount'] > 0): ?>
                            <span class="lib-fine-amount">Fine: ₱<?php echo number_format((float)$book['fine_amount'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class='bx bx-chevron-right lib-history-arrow'></i>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="lib-empty-state">
                <i class='bx bx-time'></i>
                <h3>No reading history</h3>
                <p>Your borrowing history will appear here.</p>
                <a href="index.php?page=browse" class="lib-empty-btn">Browse Books</a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Upgrade Premium Modal -->
    <div id="premiumModal" class="ls-modal-overlay">
        <div class="ls-modal-container">
            <span class="ls-modal-close" onclick="closePremiumModal()">&times;</span>
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="ls-modal-logo">
            <h3 class="ls-modal-title">Level-up your LibroSys Experience!</h3>
            <table class="ls-modal-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Regular</th>
                        <th>Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>(Perks to)</td>
                        <td>X</td>
                        <td>/</td>
                    </tr>
                </tbody>
            </table>
            <p class="ls-modal-trial">Start your 7 day free trial</p>
            <div class="ls-modal-prices">
                <button type="button" class="ls-price-card" onclick="window.location.href='index.php?page=home'">
                    <span class="ls-price-main">P100 /month</span>
                    <span class="ls-price-sub">1 MONTH</span>
                </button>
                <button type="button" class="ls-price-card" onclick="window.location.href='index.php?page=home'">
                    <span class="ls-price-main">P90 /month</span>
                    <span class="ls-price-sub">P1080 annually</span>
                    <span class="ls-price-sub">1 YEAR</span>
                </button>
            </div>
        </div>
    </div>

    <footer class="ls-footer">
        <div class="ls-footer-divider">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none">
                <path d="M0,30 C360,60 720,0 1080,30 C1260,45 1350,15 1440,30 L1440,60 L0,60 Z" fill="var(--surface-color)"></path>
            </svg>
        </div>
        <div class="ls-footer-inner">
            <div class="ls-footer-col ls-footer-brand">
                <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="ls-footer-logo">
                <p class="ls-footer-desc">Your all-in-one digital library for browsing books, discovering new stories, and managing your reading journey.</p>
                <div class="ls-footer-social">
                    <a href="#" class="ls-footer-social-icon" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
                    <a href="#" class="ls-footer-social-icon" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                    <a href="#" class="ls-footer-social-icon" aria-label="Facebook"><i class='bx bxl-facebook'></i></a>
                    <a href="#" class="ls-footer-social-icon" aria-label="YouTube"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
            <div class="ls-footer-col">
                <h4 class="ls-footer-col-title">Browse</h4>
                <ul class="ls-footer-links">
                    <li><a href="index.php?page=library">Library</a></li>
                    <li><a href="index.php?page=browse">New Arrivals</a></li>
                    <li><a href="index.php?page=browse">Exclusive</a></li>
                    <li><a href="index.php?page=browse">Categories</a></li>
                </ul>
            </div>
            <div class="ls-footer-col">
                <h4 class="ls-footer-col-title">Support</h4>
                <ul class="ls-footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Report Issue</a></li>
                </ul>
            </div>
            <div class="ls-footer-col ls-footer-newsletter">
                <h4 class="ls-footer-col-title">Stay Connected</h4>
                <p class="ls-footer-newsletter-desc">Get the latest updates on new arrivals and exclusive content.</p>
                <div class="ls-footer-newsletter-form">
                    <input type="email" placeholder="Enter your email" class="ls-footer-newsletter-input">
                    <button class="ls-footer-newsletter-btn"><i class='bx bx-send'></i></button>
                </div>
            </div>
        </div>
        <div class="ls-footer-bottom">
            <div class="ls-footer-bottom-inner">
                <p class="ls-footer-copyright">&copy; 2026 LibroSys. All rights reserved.</p>
                <div class="ls-footer-legal">
                    <a href="#">Terms of Service</a>
                    <span class="ls-footer-legal-dot">·</span>
                    <a href="#">Privacy Policy</a>
                    <span class="ls-footer-legal-dot">·</span>
                    <a href="#">Cookie Policy</a>
                </div>
                <button class="ls-footer-back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
                    <i class='bx bx-up-arrow-alt'></i>
                </button>
            </div>
        </div>
    </footer>

    <script src="<?php echo $base_url; ?>/public/js/upgradePremium.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/dropdown.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/theme.js"></script>
    <script>
    function switchLibTab(tabName, btn) {
        // Update tab buttons
        document.querySelectorAll('.lib-tab').forEach(t => t.classList.remove('lib-tab-active'));
        btn.classList.add('lib-tab-active');

        // Update tab content
        document.querySelectorAll('.lib-tab-content').forEach(tc => tc.classList.remove('lib-tab-active-content'));
        document.getElementById('lib-tab-' + tabName).classList.add('lib-tab-active-content');
    }

    function returnBook(borrowId) {
        if(!confirm("Are you sure you want to return this book?")) return;
        const formData = new FormData();
        formData.append('borrow_id', borrowId);
        fetch('index.php?page=ajax&action=return_handler', { method: 'POST', body: formData })
        .then(res => res.json()).then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(err => alert("An error occurred while returning the book. Please check your connection."));
    }

    function extendBorrowing(borrowId) {
        if(!confirm("Extend borrowing by 7 days? A ₱50 extension fee will be charged.")) return;
        const formData = new FormData();
        formData.append('borrow_id', borrowId);
        formData.append('action', 'extend_borrowing');
        fetch('index.php?page=ajax&action=borrow_handler', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                location.reload();
            }
        })
        .catch(err => alert("An error occurred while extending the borrow. Please check your connection."));
    }
    </script>
</body>
</html>
