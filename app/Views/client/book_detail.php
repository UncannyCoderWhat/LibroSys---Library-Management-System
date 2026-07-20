<?php
// app/Views/client/book_detail.php
$book = $data['book'] ?? [];
$userStatus = $data['userStatus'] ?? null;
$ebook = $data['ebook'] ?? null;
$cartCount = $data['cartCount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title'] ?? 'Book Details'); ?> - LibroSys</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/clientstyle.css">
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>
<body class="book-detail-page">
    <img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">

    <header>
        <div class="client-top-bar">
            <img src="<?php echo $base_url; ?>/images/librosys_client.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <button class="upgrade-btn" onclick="openPremiumModal()">Upgrade premium</button>
                    <a href="index.php?page=home"><i class='bx bx-home-alt'></i>Home</a>
                    <a href="index.php?page=library"><i class='bx bx-book'></i>Library</a>
                    <div class="dpContainer">
                        <button class="dropdown"><i class='bx bx-down-arrow'></i>Browse</button>
                        <div class="dpwrapper">
                            <ul>
                                <li><a href="#">History</a></li>
                                <li><a href="#">Fiction</a></li>
                                <li><a href="#">Drama</a></li>
                                <li><a href="#">Fantasy</a></li>
                                <li><a href="#">Horror</a></li>
                                <li><a href="#">Thriller</a></li>
                                <li><a href="#">Romance</a></li>
                                <li><a href="#">Teen Fiction</a></li>
                                <li><a href="#">Mystery</a></li>
                                <li><a href="#">Adventure</a></li>
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Fanfiction</a></li>
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

    <main class="bd-main">
        <!-- Back Navigation -->
        <div class="bd-back-nav">
            <a href="javascript:history.back()" class="bd-back-link">
                <i class='bx bx-arrow-back'></i> Back
            </a>
        </div>

        <!-- Book Detail Hero (Wattpad Inspired) -->
        <div class="bd-hero">
            <div class="bd-hero-bg">
                <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="" class="bd-hero-bg-img" loading="lazy">
                <div class="bd-hero-bg-overlay"></div>
            </div>

            <div class="bd-hero-content">
                <div class="bd-cover-section">
                    <div class="bd-cover-wrap">
                        <img src="<?php echo htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png'); ?>" alt="<?php echo htmlspecialchars($book['title'] ?? ''); ?>" class="bd-cover" loading="lazy">
                        <?php if (!empty($book['is_exclusive'])): ?>
                        <span class="bd-exclusive-tag">Exclusive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bd-info-section">
                    <h1 class="bd-title"><?php echo htmlspecialchars($book['title'] ?? ''); ?></h1>
                    
                    <div class="bd-meta">
                        <span class="bd-author">
                            <i class='bx bx-user'></i> <?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? 'Unknown')); ?>
                        </span>
                        <?php if (!empty($book['genre'])): ?>
                        <span class="bd-meta-divider">|</span>
                        <span class="bd-genre"><?php echo htmlspecialchars($book['genre']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($book['publication_year'])): ?>
                        <span class="bd-meta-divider">|</span>
                        <span class="bd-year"><?php echo htmlspecialchars($book['publication_year']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bd-actions">
                        <?php if ($userStatus === 'reading'): ?>
                        <button class="bd-btn bd-btn-reading" disabled>
                            <i class='bx bx-book-reader'></i> Reading
                        </button>
                        <?php elseif ($userStatus === 'bookmarked'): ?>
                        <button class="bd-btn bd-btn-primary" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>&action=read_now'">
                            <i class='bx bx-book-reader'></i> Read Now
                        </button>
                        <button class="bd-btn bd-btn-bookmarked" disabled>
                            <i class='bx bx-bookmark'></i> Bookmarked
                        </button>
                        <?php else: ?>
                        <button class="bd-btn bd-btn-primary" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>&action=read_now'">
                            <i class='bx bx-book-reader'></i> Read Now
                        </button>
                        <button class="bd-btn bd-btn-secondary" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>&action=bookmark'">
                            <i class='bx bx-bookmark'></i> Bookmark
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($ebook && !empty($ebook['file_path'])): ?>
                    <div class="bd-ebook-badge">
                        <i class='bx bx-file-pdf'></i> eBook Available
                    </div>
                    <?php endif; ?>

                    <!-- Stats Row -->
                    <div class="bd-stats">
                        <div class="bd-stat-item">
                            <span class="bd-stat-value"><?php echo (int)$book['available_copies']; ?></span>
                            <span class="bd-stat-label">Available</span>
                        </div>
                        <div class="bd-stat-divider"></div>
                        <div class="bd-stat-item">
                            <span class="bd-stat-value"><?php echo (int)($book['copies'] ?? 1); ?></span>
                            <span class="bd-stat-label">Total Copies</span>
                        </div>
                        <div class="bd-stat-divider"></div>
                        <div class="bd-stat-item">
                            <span class="bd-stat-value"><?php echo strtoupper(htmlspecialchars($book['book_type'] ?? 'N/A')); ?></span>
                            <span class="bd-stat-label">Type</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Details Content -->
        <div class="bd-content">
            <div class="bd-content-grid">
                <!-- Left Column: Description -->
                <div class="bd-description-section">
                    <h2 class="bd-section-title">About this Book</h2>
                    <div class="bd-description">
                        <?php if (!empty($book['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                        <?php else: ?>
                            <p class="bd-no-description">No description available for this book yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Book Details -->
                <div class="bd-details-section">
                    <h2 class="bd-section-title">Book Details</h2>
                    <div class="bd-details-list">
                        <?php if (!empty($book['isbn'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">ISBN</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['isbn']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($book['publisher'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Publisher</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['publisher']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($book['publication_year'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Published</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['publication_year']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($book['language'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Language</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['language']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($book['genre'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Genre</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['genre']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Status</span>
                            <span class="bd-detail-value bd-status-<?php echo ($book['available_copies'] > 0) ? 'available' : 'unavailable'; ?>">
                                <?php echo ($book['available_copies'] > 0) ? 'Available' : 'Currently Unavailable'; ?>
                            </span>
                        </div>
                        <?php if (!empty($book['shelf_location'])): ?>
                        <div class="bd-detail-item">
                            <span class="bd-detail-label">Shelf Location</span>
                            <span class="bd-detail-value"><?php echo htmlspecialchars($book['shelf_location']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
</body>
</html>
