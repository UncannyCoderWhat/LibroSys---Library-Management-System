<?php
// View template: expects $data injected by wrapper/entrypoint.
$book = $data['book'] ?? [];
$content = $data['content'] ?? [];
$ebook = $data['ebook'] ?? null;
$userStatus = $data['userStatus'] ?? '';
$cartCount = $data['cartCount'] ?? 0;

$totalPages = count($content);
$firstPage = $totalPages > 0 ? $content[0] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading: <?php echo htmlspecialchars($book['title'] ?? 'Book'); ?> - LibroSys</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/clientstyle.css">
    <style>
    /* ===== READING PAGE SPECIFIC STYLES ===== */
    .read-container {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        padding: 0 24px;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    /* Reading Header / Top Bar */
    .read-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 0;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 30px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .read-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .read-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .read-back-btn:hover {
        background: var(--main-color);
        color: #000;
        border-color: var(--main-color);
    }

    .read-book-info {
        display: flex;
        flex-direction: column;
    }

    .read-book-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .read-book-author {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .read-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .read-page-indicator {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        background: var(--surface-color-secondary);
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
    }

    /* Reading Progress Bar */
    .read-progress-bar {
        width: 100%;
        height: 4px;
        background: var(--surface-color-secondary);
        border-radius: 2px;
        margin-bottom: 40px;
        overflow: hidden;
        position: relative;
    }

    .read-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--main-color), var(--main-hover));
        border-radius: 2px;
        transition: width 0.3s ease;
        width: <?php echo $totalPages > 0 ? (1 / $totalPages) * 100 : 0; ?>%;
    }

    /* Reading Content Area */
    .read-content {
        background: var(--surface-color);
        border-radius: 16px;
        padding: 50px 60px;
        border: 1px solid var(--border-color);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        min-height: 400px;
        position: relative;
    }

    .read-page {
        display: none;
        animation: readFadeIn 0.3s ease;
    }

    .read-page-active {
        display: block;
    }

    @keyframes readFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .read-page-number {
        position: absolute;
        top: 20px;
        right: 24px;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        background: var(--surface-color-secondary);
        padding: 4px 12px;
        border-radius: 12px;
    }

    .read-page-content {
        font-size: 1.1rem;
        line-height: 1.9;
        color: var(--text-primary);
        white-space: pre-wrap;
    }

    .read-page-content p {
        margin-bottom: 1.2em;
    }

    /* PDF Viewer */
    .read-pdf-viewer {
        width: 100%;
        background: var(--surface-color);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }

    .read-pdf-viewer iframe {
        display: block;
        width: 100%;
        height: 75vh;
    }

    /* Navigation Arrows */
    .read-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
        padding: 20px 0;
    }

    .read-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        border: 1px solid var(--border-color);
        border-radius: 50px;
        background: var(--surface-color);
        color: var(--text-primary);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .read-nav-btn:hover:not(:disabled) {
        background: var(--main-color);
        color: #000;
        border-color: var(--main-color);
        transform: translateY(-2px);
    }

    .read-nav-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .read-nav-btn i {
        font-size: 1.2rem;
    }

    .read-nav-info {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        min-width: 80px;
        text-align: center;
    }

    /* Reading Footer Actions */
    .read-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 20px;
        padding: 20px 0 40px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
    }

    .read-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 20px;
        border: 1px solid var(--border-color);
        border-radius: 50px;
        background: transparent;
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .read-action-btn:hover {
        border-color: var(--main-color);
        color: var(--main-color);
    }

    .read-action-btn i {
        font-size: 1.1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .read-content {
            padding: 30px 24px;
        }
        .read-container {
            padding: 0 12px;
        }
        .read-page-content {
            font-size: 1rem;
        }
        .read-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .read-content {
            padding: 24px 16px;
            min-height: 300px;
        }
        .read-page-content {
            font-size: 0.95rem;
            line-height: 1.7;
        }
        .read-nav-btn {
            padding: 10px 20px;
            font-size: 0.8rem;
        }
    }
    </style>
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>
<body>
<img src="<?php echo $base_url; ?>/images/library-background.png" alt="Library Background" class="bg-image">

    <main class="read-container">
        <!-- Reading Header -->
        <div class="read-header">
            <div class="read-header-left">
                <a href="index.php?page=library" class="read-back-btn">
                    <i class='bx bx-arrow-back'></i>
                </a>
                <div class="read-book-info">
                    <span class="read-book-title"><?php echo htmlspecialchars($book['title'] ?? ''); ?></span>
                    <span class="read-book-author"><?php echo htmlspecialchars($book['author_name'] ?: ($book['author'] ?? '')); ?></span>
                </div>
            </div>
            <div class="read-header-right">
                <span class="read-page-indicator" id="pageIndicator">Page 1 of <?php echo $totalPages; ?></span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="read-progress-bar">
            <div class="read-progress-fill" id="progressFill"></div>
        </div>

        <!-- Reading Content -->
        <div class="read-content">
            <?php if ($ebook && !empty($ebook['file_path'])): ?>
                <div class="read-pdf-viewer">
                    <iframe src="<?php echo htmlspecialchars($ebook['file_path']); ?>" width="100%" height="75vh" style="border:none;border-radius:12px;" allowfullscreen></iframe>
                </div>
            <?php elseif ($totalPages > 0): ?>
                <?php foreach ($content as $index => $page): ?>
                <div class="read-page <?php echo $index === 0 ? 'read-page-active' : ''; ?>" data-page="<?php echo $page['page_number']; ?>">
                    <span class="read-page-number">Page <?php echo $page['page_number']; ?></span>
                    <div class="read-page-content"><?php echo nl2br(htmlspecialchars($page['content'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="read-page read-page-active" data-page="1">
                <span class="read-page-number">Page 1</span>
                <div class="read-page-content">
                    <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 60px 0;">
                        No content available for this book yet.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation -->
        <div class="read-navigation">
            <button class="read-nav-btn" id="prevPageBtn" disabled>
                <i class='bx bx-chevron-left'></i> Previous
            </button>
            <span class="read-nav-info" id="pageInfo">Page 1 / <?php echo $totalPages; ?></span>
            <button class="read-nav-btn" id="nextPageBtn" <?php echo $totalPages <= 1 ? 'disabled' : ''; ?>>
                Next <i class='bx bx-chevron-right'></i>
            </button>
        </div>

        <!-- Footer Actions -->
        <div class="read-actions">
            <a href="index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>" class="read-action-btn">
                <i class='bx bx-detail'></i> Book Details
            </a>
            <button class="read-action-btn" onclick="window.location.href='index.php?page=library'">
                <i class='bx bx-library'></i> My Library
            </button>
            <?php if ($userStatus === 'bookmarked'): ?>
            <button class="read-action-btn" onclick="window.location.href='index.php?page=book_detail&id=<?php echo (int)$book['id']; ?>&action=read_now'">
                <i class='bx bx-book-reader'></i> Start Reading
            </button>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pages = document.querySelectorAll('.read-page');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');
        const pageIndicator = document.getElementById('pageIndicator');
        const progressFill = document.getElementById('progressFill');
        let currentPage = 0;
        const totalPages = pages.length;

        function updatePage() {
            // Hide all pages
            pages.forEach(p => p.classList.remove('read-page-active'));
            
            // Show current page
            if (pages[currentPage]) {
                pages[currentPage].classList.add('read-page-active');
            }

            // Update buttons
            prevBtn.disabled = currentPage === 0;
            nextBtn.disabled = currentPage === totalPages - 1;

            // Update indicators
            const pageNum = currentPage + 1;
            pageInfo.textContent = 'Page ' + pageNum + ' / ' + totalPages;
            pageIndicator.textContent = 'Page ' + pageNum + ' of ' + totalPages;

            // Update progress bar
            const progress = ((pageNum) / totalPages) * 100;
            progressFill.style.width = progress + '%';

            // Scroll to top of content smoothly
            document.querySelector('.read-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        prevBtn.addEventListener('click', function() {
            if (currentPage > 0) {
                currentPage--;
                updatePage();
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages - 1) {
                currentPage++;
                updatePage();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && !prevBtn.disabled) {
                prevBtn.click();
            } else if (e.key === 'ArrowRight' && !nextBtn.disabled) {
                nextBtn.click();
            }
        });

        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        document.querySelector('.read-content').addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        document.querySelector('.read-content').addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0 && !nextBtn.disabled) {
                    // Swipe left -> next page
                    nextBtn.click();
                } else if (diff < 0 && !prevBtn.disabled) {
                    // Swipe right -> previous page
                    prevBtn.click();
                }
            }
        }
    });
    </script>
    <script src="<?php echo $base_url; ?>/public/js/upgradePremium.js"></script>
    <script src="<?php echo $base_url; ?>/public/js/theme.js"></script>
</body>
</html>
