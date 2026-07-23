<?php
// View template: expects $data injected by wrapper/entrypoint.
$book = $data['book'] ?? [];
$content = $data['content'] ?? [];
$ebook = $data['ebook'] ?? null;
$userStatus = $data['userStatus'] ?? '';
$cartCount = $data['cartCount'] ?? 0;
$savedPage = isset($data['savedPage']) ? (int)$data['savedPage'] : 1;
$isManga = !empty($data['isManga']);
$mangaChapters = $data['mangaChapters'] ?? [];
$mangaPages = $data['mangaPages'] ?? [];
$currentChapter = $data['currentChapter'] ?? null;

$totalPages = count($content);
$firstPage = $totalPages > 0 ? $content[0] : null;
$hasPdf = !empty($ebook) && !empty($ebook['file_path']);
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
    html, body {
        height: 100%;
        overflow: hidden;
        background: #0f0f0f;
    }

    .read-container {
        width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .read-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        border-bottom: 1px solid var(--border-color);
        gap: 16px;
        flex-wrap: wrap;
        flex-shrink: 0;
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

    .read-progress-bar {
        width: 100%;
        height: 4px;
        background: var(--surface-color-secondary);
        border-radius: 2px;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
    }

    .read-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--main-color), var(--main-hover));
        border-radius: 2px;
        transition: width 0.3s ease;
        width: <?php echo $totalPages > 0 ? (1 / $totalPages) * 100 : 0; ?>%;
    }

    .read-content {
        background: var(--surface-color);
        flex: 1;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .read-page {
        display: none;
        animation: readFadeIn 0.35s ease;
        padding: 50px 60px;
        flex: 1;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    .read-page-active {
        display: block;
    }

    @keyframes readFadeIn {
        from { opacity: 0; transform: translateY(12px); }
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

    .read-pdf-viewer {
        width: 100%;
        background: var(--surface-color);
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        min-height: 0;
        position: relative;
    }

    .pdf-scroll-container {
        width: 100%;
        height: 100%;
        overflow-y: auto;
        overflow-x: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        background: #e5e5e5;
        scroll-behavior: smooth;
        box-sizing: border-box;
        overscroll-behavior: contain;
        scrollbar-width: auto;
        scrollbar-color: rgba(0,0,0,0.25) transparent;
    }

    .pdf-scroll-container::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .pdf-scroll-container::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 10px;
    }

    .pdf-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.25);
        border-radius: 10px;
        border: 2px solid transparent;
        background-clip: padding-box;
        min-height: 40px;
    }

    .pdf-scroll-container::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.4);
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .pdf-page-canvas {
        display: block;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 4px;
        flex-shrink: 0;
        animation: pdfFadeIn 0.35s ease;
    }

    @keyframes pdfFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .pdf-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 40px;
        text-align: center;
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .read-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        padding: 16px 0;
        flex-shrink: 0;
        background: var(--surface-color);
        border-top: 1px solid var(--border-color);
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

    .read-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        padding: 12px 0 20px;
        flex-shrink: 0;
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

    @media (max-width: 768px) {
        .read-container {
            padding: 0 12px;
        }
        .read-page {
            padding: 30px 24px;
        }
        .read-page-content {
            font-size: 1rem;
        }
        .read-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .pdf-scroll-container {
            padding: 10px;
        }
    }

    @media (max-width: 480px) {
        .read-page {
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
        .pdf-scroll-container {
            padding: 8px;
        }
    }

    /* ===== MANGA READER STYLES ===== */
    .manga-reader-container {
        width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #0f0f0f;
    }

    .manga-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: #1a1a1a;
        border-bottom: 1px solid #333;
        gap: 12px;
        flex-shrink: 0;
        z-index: 10;
    }

    .manga-top-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .manga-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        flex-shrink: 0;
    }

    .manga-back-btn:hover {
        background: var(--main-color);
        color: #000;
        border-color: var(--main-color);
    }

    .manga-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .manga-top-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .manga-select {
        background: #2a2a2a;
        color: #fff;
        border: 1px solid #444;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 0.85rem;
        cursor: pointer;
        max-width: 200px;
    }

    .manga-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .manga-icon-btn:hover, .manga-icon-btn.active {
        background: var(--main-color);
        color: #000;
        border-color: var(--main-color);
    }

    .manga-reader {
        flex: 1;
        overflow-y: auto;
        background: #0f0f0f;
        display: flex;
        flex-direction: column;
        align-items: center;
        scroll-behavior: smooth;
    }

    .manga-reader img {
        display: block;
        max-width: min(800px, 100%);
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .manga-reader.webtoon-mode {
        align-items: center;
    }

    .manga-reader.webtoon-mode img {
        max-width: 100%;
        max-height: none;
        margin-bottom: 0;
        object-fit: contain;
    }

    .manga-reader.page-mode {
        position: relative;
    }

    .manga-page-single {
        display: none;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 100%;
        background: #0f0f0f;
        overflow: hidden;
    }

    .manga-page-single.active {
        display: flex;
    }

    .manga-page-single img {
        display: block;
        max-width: 800px;
        max-height: calc(100vh - 140px);
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .manga-empty {
        color: #888;
        text-align: center;
        padding: 60px 20px;
    }

    .manga-bottom-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: #1a1a1a;
        border-top: 1px solid #333;
        gap: 10px;
        flex-shrink: 0;
        z-index: 10;
    }

    .manga-bottom-bar button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .manga-bottom-bar button:hover:not(:disabled) {
        background: var(--main-color);
        color: #000;
        border-color: var(--main-color);
    }

    .manga-bottom-bar button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .manga-page-indicator {
        font-size: 0.85rem;
        color: #ccc;
        font-weight: 600;
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>
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
        <?php if ($isManga): ?>
            <?php
            $currentChapter = $currentChapter ?? null;
            $mangaChapters = $mangaChapters ?? [];
            $mangaPages = $mangaPages ?? [];
            $currentChapterId = (int)($currentChapter['id'] ?? 0);
            $totalChapters = count($mangaChapters);
            $totalPages = count($mangaPages);
            $currentChapterIndex = 0;
            foreach ($mangaChapters as $i => $ch) {
                if ((int)$ch['id'] === $currentChapterId) {
                    $currentChapterIndex = $i;
                    break;
                }
            }
            $prevChapter = $currentChapterIndex > 0 ? $mangaChapters[$currentChapterIndex - 1] : null;
            $nextChapter = $currentChapterIndex < $totalChapters - 1 ? $mangaChapters[$currentChapterIndex + 1] : null;
            ?>
            <div class="manga-reader-container">
                <div class="manga-top-bar">
                    <div class="manga-top-left">
                        <a href="index.php?page=library" class="manga-back-btn">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <div class="manga-title"><?php echo htmlspecialchars($book['title'] ?? ''); ?></div>
                    </div>
                    <div class="manga-top-right">
                        <select class="manga-select" id="chapterSelect" onchange="changeChapter(this.value)">
                            <?php foreach ($mangaChapters as $i => $ch): ?>
                                <option value="<?php echo (int)$ch['id']; ?>" <?php echo (int)$ch['id'] === $currentChapterId ? 'selected' : ''; ?>>
                                    Ch. <?php echo htmlspecialchars($ch['chapter_number']); ?><?php echo !empty($ch['title']) ? ' - ' . htmlspecialchars($ch['title']) : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="manga-icon-btn active" id="btnPageMode" onclick="setMangaMode('page')" title="Page by page">
                            <i class='bx bx-book-bookmark'></i>
                        </button>
                        <button class="manga-icon-btn" id="btnWebtoonMode" onclick="setMangaMode('webtoon')" title="Webtoon / Vertical scroll">
                            <i class='bx bx-menu'></i>
                        </button>
                        <button class="manga-icon-btn" onclick="toggleFullscreen()" title="Fullscreen">
                            <i class='bx bx-fullscreen'></i>
                        </button>
                    </div>
                </div>

                <div class="manga-reader page-mode" id="mangaReader"
                    data-book-id="<?php echo (int)$book['id']; ?>"
                    data-chapter-id="<?php echo (int)$currentChapterId; ?>"
                    data-next-chapter-id="<?php echo $nextChapter ? (int)$nextChapter['id'] : 0; ?>">
                    <?php if (!empty($mangaPages)): ?>
                        <div class="manga-page-single active" id="mangaPageContainer">
                            <img src="<?php echo htmlspecialchars($mangaPages[0]['image_path']); ?>" alt="Page 1" id="mangaPageImg">
                        </div>
                    <?php else: ?>
                        <div class="manga-empty">No pages available for this chapter yet.</div>
                    <?php endif; ?>
                </div>

                <div class="manga-bottom-bar">
                    <button id="mangaPrevChapter" <?php echo $prevChapter ? '' : 'disabled'; ?> onclick="goChapter(<?php echo $prevChapter ? (int)$prevChapter['id'] : 0; ?>)">
                        <i class='bx bx-chevron-left'></i> Prev Chapter
                    </button>
                    <button id="mangaPrevPage" onclick="mangaPrevPage()">
                        <i class='bx bx-chevron-left'></i> Prev
                    </button>
                    <span class="manga-page-indicator" id="mangaPageInfo">Page 1 / <?php echo $totalPages; ?></span>
                    <button id="mangaNextPage" onclick="mangaNextPage()">
                        Next <i class='bx bx-chevron-right'></i>
                    </button>
                    <button id="mangaNextChapter" <?php echo $nextChapter ? '' : 'disabled'; ?> onclick="goChapter(<?php echo $nextChapter ? (int)$nextChapter['id'] : 0; ?>)">
                        Next Chapter <i class='bx bx-chevron-right'></i>
                    </button>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bookId = <?php echo (int)$book['id']; ?>;
                const chapterId = <?php echo $currentChapterId; ?>;
                const pages = <?php echo json_encode(array_column($mangaPages, 'image_path')); ?>;
                let currentPage = 0;
                let mode = 'page';

                function updateMangaUI() {
                    if (!pages.length) return;
                    const img = document.getElementById('mangaPageImg');
                    if (img) img.src = pages[currentPage];
                    document.getElementById('mangaPageInfo').textContent = 'Page ' + (currentPage + 1) + ' / ' + pages.length;
                    localStorage.setItem('manga_progress_' + bookId + '_' + chapterId, currentPage + 1);
                    fetch('index.php?page=ajax&action=save_reading_progress', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'book_id=' + encodeURIComponent(bookId) + '&page_number=' + encodeURIComponent(currentPage + 1)
                    }).catch(() => {});
                }

                window.mangaNextPage = function() {
                    if (currentPage < pages.length - 1) {
                        currentPage++;
                        updateMangaUI();
                    }
                };

                window.mangaPrevPage = function() {
                    if (currentPage > 0) {
                        currentPage--;
                        updateMangaUI();
                    }
                };

                window.changeChapter = function(newChapterId) {
                    window.location.href = 'index.php?page=read&id=' + bookId + '&chapter_id=' + newChapterId;
                };

                window.goChapter = function(chapterId) {
                    if (chapterId > 0) {
                        window.location.href = 'index.php?page=read&id=' + bookId + '&chapter_id=' + chapterId;
                    }
                };

                window.setMangaMode = function(newMode) {
                    mode = newMode;
                    document.getElementById('btnPageMode').classList.toggle('active', mode === 'page');
                    document.getElementById('btnWebtoonMode').classList.toggle('active', mode === 'webtoon');
                    const reader = document.getElementById('mangaReader');
                    const bottom = document.querySelector('.manga-bottom-bar');
                    if (mode === 'webtoon') {
                        reader.classList.remove('page-mode');
                        reader.classList.add('webtoon-mode');
                        bottom.style.display = 'none';
                        renderWebtoon();
                    } else {
                        reader.classList.remove('webtoon-mode');
                        reader.classList.add('page-mode');
                        bottom.style.display = 'flex';
                        loadPageMode();
                    }
                };

                function renderWebtoon() {
                    const reader = document.getElementById('mangaReader');
                    reader.innerHTML = '';
                    pages.forEach((src, idx) => {
                        const img = document.createElement('img');
                        img.src = src;
                        img.alt = 'Page ' + (idx + 1);
                        reader.appendChild(img);
                    });
                }

                function loadPageMode() {
                    const reader = document.getElementById('mangaReader');
                    reader.innerHTML = '';
                    if (pages.length === 0) {
                        reader.innerHTML = '<div class=\"manga-empty\">No pages available for this chapter yet.</div>';
                        return;
                    }
                    const div = document.createElement('div');
                    div.className = 'manga-page-single active';
                    div.id = 'mangaPageContainer';
                    const img = document.createElement('img');
                    img.src = pages[currentPage];
                    img.alt = 'Page ' + (currentPage + 1);
                    img.id = 'mangaPageImg';
                    div.appendChild(img);
                    reader.appendChild(div);
                    updateMangaUI();
                }

                window.toggleFullscreen = function() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        document.exitFullscreen().catch(() => {});
                    }
                };

                document.addEventListener('keydown', function(e) {
                    if (mode !== 'page') return;
                    if (e.key === 'ArrowRight') mangaNextPage();
                    else if (e.key === 'ArrowLeft') mangaPrevPage();
                });

                let touchStartX = 0;
                let touchEndX = 0;
                const reader = document.getElementById('mangaReader');
                reader.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                reader.addEventListener('touchend', function(e) {
                    if (mode !== 'page') return;
                    touchEndX = e.changedTouches[0].screenX;
                    const diff = touchStartX - touchEndX;
                    if (Math.abs(diff) > 50) {
                        if (diff > 0) mangaNextPage();
                        else mangaPrevPage();
                    }
                });

                updateMangaUI();
            });
            </script>
        <?php else: ?>
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
                <span class="read-page-indicator" id="pageIndicator">Page 1 of <?php echo $hasPdf ? 'PDF' : $totalPages; ?></span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="read-progress-bar">
            <div class="read-progress-fill" id="progressFill"></div>
        </div>

        <!-- Reading Content -->
        <div class="read-content" id="readContent">
            <?php if ($hasPdf): ?>
                <div class="read-pdf-viewer" id="pdfViewerContainer">
                    <div class="pdf-scroll-container" id="pdfScrollContainer">
                        <canvas id="pdfCanvas" class="pdf-page-canvas"></canvas>
                    </div>
                    <div class="pdf-loading" id="pdfLoading">Loading document...</div>
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
                        No content available for this book yet. Please upload a PDF in the admin panel.
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
            <span class="read-nav-info" id="pageInfo">Page 1 / <?php echo $hasPdf ? 'PDF' : $totalPages; ?></span>
            <button class="read-nav-btn" id="nextPageBtn" <?php echo $hasPdf ? '' : ($totalPages <= 1 ? 'disabled' : ''); ?>>
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
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');
        const pageIndicator = document.getElementById('pageIndicator');
        const progressFill = document.getElementById('progressFill');
        const readContent = document.getElementById('readContent');

        const pdfUrl = '<?php echo htmlspecialchars($ebook['file_path'] ?? ''); ?>';
        const isPdfMode = pdfUrl && typeof pdfjsLib !== 'undefined';
        const bookId = <?php echo (int)($book['id'] ?? 0); ?>;
        const initialPage = <?php echo (int)$savedPage; ?>;

        if (isPdfMode) {
            initPdfViewer(pdfUrl, initialPage);
        } else {
            initPlaceholderViewer(initialPage);
        }

        function updateButtons(current, total) {
            prevBtn.disabled = current <= 1;
            nextBtn.disabled = current >= total;
        }

        function initPlaceholderViewer(startPage) {
            const pages = document.querySelectorAll('.read-page');
            let currentPage = Math.min(Math.max(startPage, 1), pages.length) - 1;
            const totalPages = pages.length;

            function updatePage() {
                pages.forEach(p => p.classList.remove('read-page-active'));
                if (pages[currentPage]) {
                    pages[currentPage].classList.add('read-page-active');
                }

                const pageNum = currentPage + 1;
                pageInfo.textContent = 'Page ' + pageNum + ' / ' + totalPages;
                pageIndicator.textContent = 'Page ' + pageNum + ' of ' + totalPages;

                const progress = totalPages > 0 ? (pageNum / totalPages) * 100 : 0;
                progressFill.style.width = progress + '%';

                updateButtons(pageNum, totalPages);
                saveProgress(pageNum);
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

            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft' && currentPage > 0) {
                    prevBtn.click();
                } else if (e.key === 'ArrowRight' && currentPage < totalPages - 1) {
                    nextBtn.click();
                }
            });

            let touchStartX = 0;
            let touchEndX = 0;

            readContent.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            readContent.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, { passive: true });

            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0 && currentPage < totalPages - 1) {
                        nextBtn.click();
                    } else if (diff < 0 && currentPage > 0) {
                        prevBtn.click();
                    }
                }
            }

            updatePage();
        }

        async function initPdfViewer(url, startPage) {
            let pdfDoc = null;
            let currentPage = 1;
            let totalPages = 0;
            const canvas = document.getElementById('pdfCanvas');
            const ctx = canvas.getContext('2d');
            const loadingEl = document.getElementById('pdfLoading');
            const scrollContainer = document.getElementById('pdfScrollContainer');

            if (!canvas) return;

            try {
                pdfDoc = await pdfjsLib.getDocument(url).promise;
                totalPages = pdfDoc.numPages;
                currentPage = Math.min(Math.max(startPage, 1), totalPages);

                loadingEl.style.display = 'none';
                await renderPage(currentPage);
            } catch (err) {
                console.error('Failed to load PDF:', err);
                if (loadingEl) {
                    loadingEl.innerHTML = '<p style="color:red">Failed to load PDF. Please try again later.</p>';
                }
                return;
            }

            async function renderPage(pageNum) {
                if (!pdfDoc) return;

                try {
                    const page = await pdfDoc.getPage(pageNum);
                    const containerWidth = scrollContainer.clientWidth - 40;
                    const unscaledViewport = page.getViewport({ scale: 1 });
                    const scale = Math.max(0.6, containerWidth / unscaledViewport.width);
                    const viewport = page.getViewport({ scale });

                    const renderWidth = Math.max(1, Math.floor(viewport.width));
                    const renderHeight = Math.max(1, Math.floor(viewport.height));

                    canvas.width = renderWidth;
                    canvas.height = renderHeight;
                    canvas.style.width = renderWidth + 'px';
                    canvas.style.height = renderHeight + 'px';

                    await page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;

                    updateIndicators(pageNum);
                    scrollContainer.scrollTop = 0;
                } catch (err) {
                    console.error('Failed to render page:', err);
                }
            }

            function updateIndicators(pageNum) {
                pageInfo.textContent = 'Page ' + pageNum + ' / ' + totalPages;
                pageIndicator.textContent = 'Page ' + pageNum + ' of ' + totalPages;

                const progress = totalPages > 0 ? (pageNum / totalPages) * 100 : 0;
                progressFill.style.width = progress + '%';

                updateButtons(pageNum, totalPages);
                saveProgress(pageNum);
            }

            function goNext() {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage(currentPage);
                }
            }

            function goPrev() {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                }
            }

            prevBtn.addEventListener('click', goPrev);
            nextBtn.addEventListener('click', goNext);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') goPrev();
                else if (e.key === 'ArrowRight') goNext();
            });

            let touchStartX = 0;
            let touchEndX = 0;

            scrollContainer.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            scrollContainer.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) goNext();
                    else goPrev();
                }
            });

            let resizeTimeout = null;
            window.addEventListener('resize', function() {
                if (resizeTimeout) clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    if (pdfDoc && currentPage > 0) {
                        renderPage(currentPage);
                    }
                }, 250);
            });

            updateIndicators(currentPage);
        }

        let saveTimeout = null;
        function saveProgress(pageNum) {
            try {
                localStorage.setItem('reading_progress_' + bookId, pageNum);
            } catch (e) {
                // localStorage unavailable
            }

            if (saveTimeout) clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                fetch('index.php?page=ajax&action=save_reading_progress', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'book_id=' + encodeURIComponent(bookId) + '&page_number=' + encodeURIComponent(pageNum)
                }).catch(function(err) {
                    console.error('Failed to save reading progress:', err);
                });
            }, 800);
        }
    });
    </script>
<script src="<?php echo $base_url; ?>/public/js/upgradePremium.js"></script>
<script src="<?php echo $base_url; ?>/public/js/theme.js"></script>
</body>
</html>