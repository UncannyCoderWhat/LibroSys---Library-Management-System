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

    <header>
        <div class="client-top-bar">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" onclick="window.scrollTo({top:0,behavior:'smooth'})" alt="LibroSys Logo" class="logo">
            
            <div class="search-container">
                <form action="index.php" method="GET" class="search-form">
                    <input type="hidden" name="page" value="library">
                    <i class='bx bx-search search-icon'></i>
                    <input type="text" name="search" placeholder="Search books, authors..." class="search-input">
                </form>
            </div>

            <nav class="navigation">
                <div class="nav-links">
                    <a href="index.php?page=home" class="active"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=library"><i class='bx bx-book'></i>Library</a>
                    <a href="index.php?page=profile"><i class='bx bx-user-circle'></i>Profile</a>
                    <div class="switch-container">
                        <span class="switch-label"></span>
                        <label class="main-toggle">
                            <input type="checkbox" id="theme-toggle" class="main-checkbox">
                            <div class="main-track"></div>
                            <div class="main-knob"></div>
                        </label>
                    </div>
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
                        <button class="ls-btn ls-btn-primary" onclick="document.querySelector('.ls-stats-strip').scrollIntoView({behavior:'smooth'})">
                            <i class='bx bx-book-open'></i> Explore Collection
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
                <span class="ls-stat-label">Special</span>
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

        <!-- Special Row -->
        <?php if (!empty($exclusive_books)): ?>
        <section class="special-shelf-section">
            <div class="special-section-header">
                <h2 class="special-section-title"><i class='bx bx-trophy'></i> Special</h2>
                <!-- View All button opens the modal -->
                <a href="javascript:void(0)" class="special-view-all" onclick="openViewAllModal()">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="special-horizontal-scroll">
                <button class="special-scroll-arrow special-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="special-scroll-track">
                    <?php foreach ($exclusive_books as $book): ?>
                    <!-- Card clicks navigate directly to book_detail page -->
                    <div class="special-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)($book['id'] ?? 0); ?>'" 
                        data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="special-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="special-book-cover" loading="lazy">
                            <div class="special-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <span class="ls-exclusive-badge">Special</span>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                            <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                            <span class="ls-unavailable-badge">Not Available</span>
                            <?php endif; ?>
                        </div>
                        <div class="special-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="special-scroll-arrow special-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
            </div>
        </section>

        <!-- View All Modal displaying all books in a grid -->
        <div id="viewAllModal" class="book-modal">
            <div class="book-modal-content view-all-modal-content">
                <div class="view-all-header">
                    <h3>Special Collection</h3>
                    <span class="book-modal-close" onclick="closeViewAllModal()">&times;</span>
                </div>
                <div class="view-all-grid">
                    <?php foreach ($exclusive_books as $book): ?>
                    <div class="special-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)($book['id'] ?? 0); ?>'">
                        <div class="special-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="special-book-cover" loading="lazy">
                            <span class="ls-exclusive-badge">Special</span>
                        </div>
                        <div class="special-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- New Releases Row -->
        <?php if (!empty($new_releases)): ?>
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-star'></i> New Arrivals</h2>
                <!-- Connected View All to open the New Arrivals modal -->
                <a href="javascript:void(0)" class="ls-view-all" onclick="openNewArrivalsModal()">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="ls-horizontal-scroll">
                <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="ls-scroll-track">
                    <?php foreach ($new_releases as $book): ?>
                    <!-- Card clicks navigate directly to book_detail page -->
                    <div class="ls-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'" 
                        data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Special</span>
                            <?php endif; ?>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                            <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                            <span class="ls-unavailable-badge">Not Available</span>
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

        <!-- View All New Arrivals Modal -->
        <div id="newArrivalsModal" class="book-modal">
            <div class="book-modal-content view-all-modal-content">
                <div class="view-all-header">
                    <h3>New Arrivals</h3>
                    <span class="book-modal-close" onclick="closeNewArrivalsModal()">&times;</span>
                </div>
                <div class="view-all-grid">
                    <?php foreach ($new_releases as $book): ?>
                    <div class="ls-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Special</span>
                            <?php endif; ?>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                            <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                            <span class="ls-unavailable-badge">Not Available</span>
                            <?php endif; ?>
                        </div>
                        <div class="ls-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Regular Books Grid -->
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-library'></i> Library Collection</h2>
                <!-- Connected View All to open the Library Collection modal -->
                <a href="javascript:void(0)" class="ls-view-all" onclick="openLibraryModal()">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <?php if (!empty($regular_books)): ?>
            <div class="ls-grid-4">
                <?php foreach (array_slice($regular_books, 0, 8) as $book): ?>
                <!-- Card clicks navigate directly to book_detail page -->
                <div class="ls-book-card" 
                    onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'" 
                    data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                    <div class="ls-book-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                        <div class="ls-book-overlay">
                            <i class='bx bx-plus-circle'></i>
                        </div>
                        <?php if ($book['is_borrowed'] ?? false): ?>
                        <span class="ls-borrowed-badge">Borrowed</span>
                        <?php endif; ?>
                        <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                        <span class="ls-unavailable-badge">Not Available</span>
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

        <!-- View All Library Collection Modal (placed outside section for clean layout overlay) -->
        <?php if (!empty($regular_books)): ?>
        <div id="libraryModal" class="book-modal">
            <div class="book-modal-content view-all-modal-content">
                <div class="view-all-header">
                    <h3>Library Collection</h3>
                    <span class="book-modal-close" onclick="closeLibraryModal()">&times;</span>
                </div>
                <div class="view-all-grid">
                    <?php foreach ($regular_books as $book): ?>
                    <div class="ls-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <?php if ($book['is_borrowed'] ?? false): ?>
                            <span class="ls-borrowed-badge">Borrowed</span>
                            <?php endif; ?>
                            <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                            <span class="ls-unavailable-badge">Not Available</span>
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
            </div>
        </div>
        <?php endif; ?>

        <!-- All Available Books Horizontal Scroll -->
        <?php if (!empty($available_books)): ?>
        <section class="ls-shelf-section">
            <div class="ls-section-header">
                <h2 class="ls-section-title"><i class='bx bx-check-circle'></i> Available Now</h2>
                <!-- Connected View All to open the Available Books modal -->
                <a href="javascript:void(0)" class="ls-view-all" onclick="openAvailableModal()">View All <i class='bx bx-chevron-right'></i></a>
            </div>
            <div class="ls-horizontal-scroll">
                <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                <div class="ls-scroll-track">
                    <?php foreach ($available_books as $book): ?>
                    <!-- Card clicks navigate directly to book_detail page -->
                    <div class="ls-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'" 
                        data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <div class="ls-book-overlay">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Special</span>
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

        <!-- View All Available Now Modal -->
        <div id="availableModal" class="book-modal">
            <div class="book-modal-content view-all-modal-content">
                <div class="view-all-header">
                    <h3>Available Now</h3>
                    <span class="book-modal-close" onclick="closeAvailableModal()">&times;</span>
                </div>
                <div class="view-all-grid">
                    <?php foreach ($available_books as $book): ?>
                    <div class="ls-book-card" 
                        onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                        <div class="ls-book-cover-wrap">
                            <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                            <?php if (!empty($book['is_exclusive'])): ?>
                            <span class="ls-exclusive-badge">Special</span>
                            <?php endif; ?>
                        </div>
                        <div class="ls-book-info">
                            <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
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
                <?php $genreSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $genreName); ?>
                <section class="ls-shelf-section">
                    <div class="ls-section-header">
                        <h2 class="ls-section-title">
                            <i class="<?php echo $genreIcons[$genreName] ?? 'bx bxs-category'; ?>"></i>
                            <?php echo htmlspecialchars($genreName); ?>
                        </h2>
                        <!-- Open specific genre modal -->
                        <a href="javascript:void(0)" class="ls-view-all" onclick="openGenreModal('<?php echo htmlspecialchars($genreSlug, ENT_QUOTES); ?>')">View All <i class='bx bx-chevron-right'></i></a>
                    </div>
                    <div class="ls-horizontal-scroll">
                        <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                        <div class="ls-scroll-track">
                            <?php foreach ($genreBooks as $book): ?>
                            <div class="ls-book-card" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                                <div class="ls-book-cover-wrap">
                                    <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                    <div class="ls-book-overlay">
                                        <i class='bx bx-plus-circle'></i>
                                    </div>
                                    <?php if (!empty($book['is_exclusive'])): ?>
                                    <span class="ls-exclusive-badge">Special</span>
                                    <?php endif; ?>
                                    <?php if ($book['is_borrowed'] ?? false): ?>
                                    <span class="ls-borrowed-badge">Borrowed</span>
                                    <?php endif; ?>
                                    <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                                    <span class="ls-unavailable-badge">Not Available</span>
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

                <!-- View All Genre Modal -->
                <div id="genreModal_<?php echo $genreSlug; ?>" class="book-modal genre-modal-instance">
                    <div class="book-modal-content view-all-modal-content">
                        <div class="view-all-header">
                            <h3><?php echo htmlspecialchars($genreName); ?></h3>
                            <span class="book-modal-close" onclick="closeGenreModal('<?php echo htmlspecialchars($genreSlug, ENT_QUOTES); ?>')">&times;</span>
                        </div>
                        <div class="view-all-grid">
                            <?php foreach ($genreBooks as $book): ?>
                            <div class="ls-book-card" 
                                onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                                <div class="ls-book-cover-wrap">
                                    <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                    <?php if (!empty($book['is_exclusive'])): ?>
                                    <span class="ls-exclusive-badge">Special</span>
                                    <?php endif; ?>
                                    <?php if ($book['is_borrowed'] ?? false): ?>
                                    <span class="ls-borrowed-badge">Borrowed</span>
                                    <?php endif; ?>
                                    <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                                    <span class="ls-unavailable-badge">Not Available</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ls-book-info">
                                    <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                    <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Book Type Sections (Customizable Visual Blocks) -->
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

            // Customizable background images for each book type (use empty string for no image)
            // Add your image paths here - e.g. 'Manga' => 'images/manga-bg.png'
            $typeBackgrounds = [
                'Manga' => 'images/manga-photo.jpg',
                'Novel' => '',
                'Light Novel' => '',
                'Comic' => '',
                'Graphic Novel' => '',
                'Textbook' => 'images/textbook.jpg',
                'Reference' => '',
                'Other' => '',
            ];
            ?>
            <?php foreach ($book_type_groups as $typeName => $typeBooks): ?>
                <?php if (count($typeBooks) > 0): 
                    $typeKey = preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($typeName));
                    $typeSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $typeName);
                    $bgImage = $typeBackgrounds[$typeName] ?? '';
                    $bgStyle = !empty($bgImage) ? ' style="background-image: url(' . htmlspecialchars($base_url . '/' . $bgImage) . ');"' : '';
                ?>
                <section class="ls-shelf-section ls-shelf-type ls-shelf-type-<?php echo htmlspecialchars($typeKey); ?>"<?php echo $bgStyle; ?>>
                    <?php if (!empty($bgImage)): ?>
                    <div class="ls-type-bg-overlay"></div>
                    <?php endif; ?>
                    <div class="ls-type-section-content">
                        <div class="ls-section-header">
                            <h2 class="ls-section-title ls-section-title-forLight">
                                <i class="<?php echo $typeIcons[$typeName] ?? 'bx bxs-category'; ?>"></i>
                                <?php echo htmlspecialchars($typeName); ?>
                            </h2>
                            <!-- Connected View All to open type-specific modal -->
                            <a href="javascript:void(0)" class="ls-view-all" onclick="openTypeModal('<?php echo htmlspecialchars($typeSlug, ENT_QUOTES); ?>')">View All <i class='bx bx-chevron-right'></i></a>
                        </div>
                        <div class="ls-horizontal-scroll">
                            <button class="ls-scroll-arrow ls-scroll-left" onclick="scrollShelf(this, -300)"><i class='bx bx-chevron-left'></i></button>
                            <div class="ls-scroll-track">
                                <?php foreach ($typeBooks as $book): ?>
                                <div class="ls-book-card" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'" data-title="<?php echo strtolower(htmlspecialchars($book['title'] ?? '')); ?>">
                                    <div class="ls-book-cover-wrap">
                                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                        <div class="ls-book-overlay">
                                            <i class='bx bx-plus-circle'></i>
                                        </div>
                                        <?php if (!empty($book['is_exclusive'])): ?>
                                        <span class="ls-exclusive-badge">Special</span>
                                        <?php endif; ?>
                                        <?php if ($book['is_borrowed'] ?? false): ?>
                                        <span class="ls-borrowed-badge">Borrowed</span>
                                        <?php endif; ?>
                                        <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                                        <span class="ls-unavailable-badge">Not Available</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ls-book-info ls-book-info-forLight">
                                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                        <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="ls-scroll-arrow ls-scroll-right" onclick="scrollShelf(this, 300)"><i class='bx bx-chevron-right'></i></button>
                        </div>
                    </div>
                </section>

                <!-- View All Book Type Modal -->
                <div id="typeModal_<?php echo $typeSlug; ?>" class="book-modal type-modal-instance">
                    <div class="book-modal-content view-all-modal-content">
                        <div class="view-all-header">
                            <h3><?php echo htmlspecialchars($typeName); ?></h3>
                            <span class="book-modal-close" onclick="closeTypeModal('<?php echo htmlspecialchars($typeSlug, ENT_QUOTES); ?>')">&times;</span>
                        </div>
                        <div class="view-all-grid">
                            <?php foreach ($typeBooks as $book): ?>
                            <div class="ls-book-card" 
                                onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>'">
                                <div class="ls-book-cover-wrap">
                                    <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="Cover" class="ls-book-cover" loading="lazy">
                                    <?php if (!empty($book['is_exclusive'])): ?>
                                    <span class="ls-exclusive-badge">Special</span>
                                    <?php endif; ?>
                                    <?php if ($book['is_borrowed'] ?? false): ?>
                                    <span class="ls-borrowed-badge">Borrowed</span>
                                    <?php endif; ?>
                                    <?php if (($book['status'] ?? 'available') === 'unavailable'): ?>
                                    <span class="ls-unavailable-badge">Not Available</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ls-book-info">
                                    <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                                    <p><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>



    <footer class="ls-footer">
        <!-- Decorative top divider with layered wave animation -->
        <div class="ls-footer-divider">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="ls-footer-wave">
                <path d="M0,30 C360,60 720,0 1080,30 C1260,45 1350,15 1440,30 L1440,60 L0,60 Z" fill="var(--surface-color)"></path>
            </svg>
            <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="ls-footer-wave-secondary">
                <path d="M0,20 C240,40 480,0 720,20 C960,40 1200,0 1440,20 L1440,40 L0,40 Z" fill="var(--main-color)" opacity="0.08"></path>
            </svg>
        </div>

        <div class="ls-footer-inner">
            <!-- Brand Column -->
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

            <!-- Browse Links -->
            <div class="ls-footer-col">
                <h4 class="ls-footer-col-title">Browse</h4>
                <ul class="ls-footer-links">
                    <li><a href="index.php?page=library"><i class='bx bx-book'></i> Library</a></li>
                    <li><a href="index.php?page=browse"><i class='bx bx-star'></i> New Arrivals</a></li>
                    <li><a href="index.php?page=browse"><i class='bx bx-award'></i> Special</a></li>
                    <li><a href="index.php?page=browse"><i class='bx bx-category'></i> Categories</a></li>
                </ul>
            </div>

            <!-- Support Links -->
            <div class="ls-footer-col">
                <h4 class="ls-footer-col-title">Support</h4>
                <ul class="ls-footer-links">
                    <li><a href="#"><i class='bx bx-help-circle'></i> Help Center</a></li>
                    <li><a href="#"><i class='bx bx-question-mark'></i> FAQ</a></li>
                    <li><a href="#"><i class='bx bx-envelope'></i> Contact Us</a></li>
                    <li><a href="#"><i class='bx bx-flag'></i> Report Issue</a></li>
                </ul>
            </div>

            <!-- My Account Links -->
            <div class="ls-footer-col">
                <h4 class="ls-footer-col-title">Account</h4>
                <ul class="ls-footer-links">
                    <li><a href="index.php?page=profile"><i class='bx bx-user-circle'></i> My Profile</a></li>
                    <li><a href="index.php?page=library"><i class='bx bx-book-reader'></i> My Library</a></li>
                    <li><a href="index.php?page=settings"><i class='bx bx-cog'></i> Settings</a></li>
                    <li><a href="index.php?page=logout"><i class='bx bx-log-out'></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
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
                <button class="ls-footer-back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top">
                    <i class='bx bx-up-arrow-alt'></i>
                </button>
            </div>
        </div>
    </footer>

    <script src="<?php echo $base_url; ?>/public/js/dropdown.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/browse.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/clientBG.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/specialHorizontalScroll.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/horizontalScroll.js"></script>  
    <script src="<?php echo $base_url; ?>/public/js/specialBookAnimation.js"></script>  
    <script src="<?php echo $base_url; ?>/public/js/viewAllModal.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/searchBarHome.js"></script>
</body>
</html>
