<?php
// View template: expects $data injected by wrapper/entrypoint.
$generated_user_id = $data['generated_user_id'] ?? null;
$cartCount = $data['cartCount'] ?? 0;

$exclusive_books = $data['exclusive_books'] ?? [];
$regular_books = $data['regular_books'] ?? [];
$new_releases = $data['new_releases'] ?? [];
$available_books = $data['available_books'] ?? [];
$borrowed_books = $data['borrowed_books'] ?? [];
$all_books = $data['all_books'] ?? [];
$current_score = $data['current_score'] ?? 0;
$genre_groups = $data['genre_groups'] ?? [];
$book_type_groups = $data['book_type_groups'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Home</title>
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

    <header>
        <div class="client-top-bar">
<img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <button class="upgrade-btn" onclick="openPremiumModal()">Upgrade premium</button>
                    <a href="index.php?page=home" class="active"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=library"><i class='bx bx-book'></i>Library</a>
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
        <!-- Success Notification -->
        <?php if ($generated_user_id): ?>
            <div class="ls-notification-banner">
                <span><i class='bx bx-check-circle'></i> Registration successful! Your unique Login ID is: <strong><?php echo htmlspecialchars($generated_user_id); ?></strong>. Please save this ID for future logins.</span>
            </div>
            <?php
                if (session_status() === PHP_SESSION_ACTIVE) {
                    unset($_SESSION['temp_user_id_for_display']);
                }
            ?>
        <?php endif; ?>

        <!-- Hero Section -->
        <section class="ls-hero">
            <div class="ls-hero-content">
                <div class="ls-hero-text">
                    <h1 class="ls-hero-title">Discover Your Next Chapter</h1>
                    <p class="ls-hero-subtitle">Your all-in-one digital library for browsing books</p>
                    <div class="ls-hero-actions">
                        <button class="ls-btn ls-btn-primary" onclick="window.location.href='index.php?page=browse'">
                            <i class='bx bx-book-open'></i> Start Browsing
                        </button>
                        <button class="ls-btn ls-btn-secondary" onclick="document.querySelector('.ls-shelf-section').scrollIntoView({behavior:'smooth'})">
                            <i class='bx bx-collection'></i> Explore Collection
                        </button>
                    </div>
                </div>
                <div class="ls-hero-visual">
                    <div class="ls-hero-card-stack">
                        <?php 
                        $heroBooks = array_slice($all_books, 0, 3);
                        foreach ($heroBooks as $index => $book): 
                        ?>
                        <div class="ls-hero-card" style="transform: rotate(<?php echo ($index - 1) * 8; ?>deg) translateY(<?php echo $index * 10; ?>px);">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Stats Strip -->
        <section class="ls-stats-strip">
            <div class="ls-stat-item">
                <span class="ls-stat-number"><?php echo count($all_books); ?></span>
                <span class="ls-stat-label">Total Books</span>
            </div>
            <div class="ls-stat-divider"></div>
            <div class="ls-stat-item">
                <span class="ls-stat-number"><?php echo count($exclusive_books); ?></span>
                <span class="ls-stat-label">Exclusive</span>
            </div>
            <div class="ls-stat-divider"></div>
            <div class="ls-stat-item">
                <span class="ls-stat-number"><?php echo count($available_books); ?></span>
                <span class="ls-stat-label">Available Now</span>
            </div>
            <div class="ls-stat-divider"></div>
            <div class="ls-stat-item">
                <span class="ls-stat-number"><?php echo count($new_releases); ?></span>
                <span class="ls-stat-label">New Arrivals</span>
            </div>
        </section>

        <!-- New Releases Row -->
        <?php if (!empty($new_releases)): ?>
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-star'></i> New Arrivals</h2>
                <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="ls-horizontal-scroll">
                <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="ls-scroll-track">
                    <?php foreach ($new_releases as $book): ?>
                    <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Exclusive</span>
                            <?php endif; ?>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                        </div>
                        <div class="ls-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
            </div>
        </section>
        <?php endif; ?>

        <!-- Exclusive Row -->
        <?php if (!empty($exclusive_books)): ?>
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-award'></i> Exclusive Collection</h2>
                <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="ls-horizontal-scroll">
                <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="ls-scroll-track">
                    <?php foreach ($exclusive_books as $book): ?>
                    <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <span class="ls-exclusive-badge">Exclusive</span>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                        </div>
                        <div class="ls-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
            </div>
        </section>
        <?php endif; ?>

        <!-- Regular Books Grid -->
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-library'></i> Library Collection</h2>
                <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <?php if (!empty($regular_books)): ?>
            <div class="ls-grid-4">
                <?php foreach (array_slice($regular_books, 0, 8) as $book): ?>
                <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                    <div class="ls-book-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                        <div class="ls-book-overlay">
                            <i class='bx bx-plus-circle'></i>
                        </div>
                        <?php if ($book['is_borrowed'] ?? false): ?>
                        <span class="ls-borrowed-badge">Borrowed</span>
                        <?php endif; ?>
                    </div>
                    <div class="ls-book-info">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        <span class="ls-genre-tag"><?php echo htmlspecialchars($book['genre'] ?? ''); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="ls-empty-state">
                <i class='bx bx-book-alt'></i>
                <p>No books available in the library yet.</p>
            </div>
            <?php endif; ?>
        </section>

        <!-- All Available Books Horizontal Scroll -->
        <?php if (!empty($available_books)): ?>
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-check-circle'></i> Available Now</h2>
                <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="ls-horizontal-scroll">
                <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="ls-scroll-track">
                    <?php foreach ($available_books as $book): ?>
                    <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Exclusive</span>
                            <?php endif; ?>
                        </div>
                        <div class="ls-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
            </div>
        </section>
        <?php endif; ?>

        <!-- Genre-Based Sections -->
        <?php if (!empty($genre_groups)): ?>
            <?php 
            // Define a set of known genre icons for visual variety
            $genreIcons = [
                'Fiction' => 'bx bxs-pen',
                'Non-Fiction' => 'bx bxs-book-content',
                'Mystery' => 'bx bxs-search',
                'Sci-Fi' => 'bx bxs-rocket',
                'Fantasy' => 'bx bxs-magic-wand',
                'Romance' => 'bx bxs-heart',
                'Horror' => 'bx bxs-skull',
                'History' => 'bx bxs-time',
                'Biography' => 'bx bxs-user-detail',
                'Thriller' => 'bx bxs-zap',
                'Adventure' => 'bx bxs-compass',
                'Drama' => 'bx bxs-mask',
                'Poetry' => 'bx bxs-quote-alt-left',
                'Comic' => 'bx bxs-book-alt',
            ];
            ?>
            <?php foreach ($genre_groups as $genreName => $genreBooks): ?>
                <?php if (count($genreBooks) > 0): ?>
                <section class="ls-shelf-section">
                    <div class="ls-section-header">
                        <h2 class="ls-section-title">
                            <i class="<?php echo $genreIcons[$genreName] ?? 'bx bxs-category'; ?>"></i>
                            <?php echo htmlspecialchars($genreName); ?>
                        </h2>
                        <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
                    </div>
                    <div class="ls-horizontal-scroll">
                        <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                        <div class="ls-scroll-track">
                            <?php foreach ($genreBooks as $book): ?>
                            <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                                <div class="ls-book-cover-wrap">
                                    <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                    <div class="ls-book-overlay">
                                        <i class='bx bx-plus-circle'></i>
                                    </div>
                                    <?php if (!empty($book['is_exclusive'])): ?>
                                    <span class="ls-exclusive-badge">Exclusive</span>
                                    <?php endif; ?>
                                    <?php if ($book['is_borrowed'] ?? false): ?>
                                    <span class="ls-borrowed-badge">Borrowed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ls-book-info">
                                    <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                    <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
                    </div>
                </section>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Book Type Sections -->
        <?php if (!empty($book_type_groups)): ?>
            <?php 
            $typeIcons = [
                'Novel' => 'bx bxs-book',
                'Manga' => 'bx bxs-book-content',
                'Light Novel' => 'bx bxs-book-alt',
                'Comic' => 'bx bxs-book-open',
                'Graphic Novel' => 'bx bxs-book-reader',
                'Textbook' => 'bx bxs-graduation',
                'Reference' => 'bx bxs-bookmark',
                'Other' => 'bx bxs-category',
            ];
            ?>
            <?php foreach ($book_type_groups as $typeName => $typeBooks): ?>
                <?php if (count($typeBooks) > 0): ?>
                <section class="ls-shelf-section">
                    <div class="ls-section-header">
                        <h2 class="ls-section-title">
                            <i class="<?php echo $typeIcons[$typeName] ?? 'bx bxs-category'; ?>"></i>
                            <?php echo htmlspecialchars($typeName); ?>
                        </h2>
                        <a href="index.php?page=browse" class="ls-view-all">View All <i class='bx bx-chevron-right'></i></a>
                    </div>
                    <div class="ls-horizontal-scroll">
                        <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                        <div class="ls-scroll-track">
                            <?php foreach ($typeBooks as $book): ?>
                            <div class="ls-book-card" onclick="openBorrowModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                                <div class="ls-book-cover-wrap">
                                    <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                    <div class="ls-book-overlay">
                                        <i class='bx bx-plus-circle'></i>
                                    </div>
                                    <?php if (!empty($book['is_exclusive'])): ?>
                                    <span class="ls-exclusive-badge">Exclusive</span>
                                    <?php endif; ?>
                                    <?php if ($book['is_borrowed'] ?? false): ?>
                                    <span class="ls-borrowed-badge">Borrowed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ls-book-info">
                                    <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                    <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
                    </div>
                </section>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
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

    <footer>
        <div class="footer-content">
            <p>© 2026 LibroSys. All rights reserved.</p>
            <div class="social-links">
                <a href="#"><i class="bx bxl-twitter"></i></a>
                <a href="#"><i class="bx bxl-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script src="<?php echo $base_url; ?>/public/js/upgradePremium.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/dropdown.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/browse.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/home.js"></script>
</body>
</html>
