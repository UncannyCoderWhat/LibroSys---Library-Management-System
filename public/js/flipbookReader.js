/**
 * LibroSys - StPageFlip Ebook Reader (Full Render)
 * Creates a realistic 3D flipbook experience for ebooks using StPageFlip
 * Renders ALL PDF pages upfront for maximum reliability
 */

let pageFlip = null;
let pdfDoc = null;
let currentPage = 1;
let lastSavedPage = 1;
let totalPdfPages = 0;
let bookId = null;
let isInitializing = false;
let periodicSaveInterval = null;

function initFlipbookReader(pdfUrl, startPage, bookIdParam) {
    console.log('initFlipbookReader called:', { pdfUrl, startPage, bookIdParam });
    bookId = bookIdParam;

    // Local storage fallback check (if local progress is ahead of server)
    try {
        if (startPage > 1) {
            const localPage = parseInt(localStorage.getItem('reading_progress_' + bookId));
            if (localPage && localPage > startPage) {
                startPage = localPage;
            }
        }
    } catch (e) {}

    if (typeof St === 'undefined' || !St.PageFlip) {
        showError('StPageFlip library not loaded');
        return;
    }

    if (!pdfUrl || pdfUrl === '') {
        showError('No PDF file available.');
        return;
    }

    const container = document.getElementById('flipbookContainer');
    if (!container) {
        console.error('Flipbook container not found');
        return;
    }

    container.innerHTML = '';
    const loadingEl = document.getElementById('flipbookLoading');
    if (loadingEl) loadingEl.style.display = 'block';

    try {
        pageFlip = new St.PageFlip(container, {
            width: 450,
            height: 650,
            size: 'stretch',
            minWidth: 300,
            maxWidth: 600,
            minHeight: 400,
            maxHeight: 900,
            showCover: true,
            flippingTime: 800,
            usePortrait: false,
            maxShadowOpacity: 0.5,
            mobileScrollSupport: true,
            drawShadow: true,
            swipeDistance: 30
        });

        // Single flip event handler for page tracking
        pageFlip.on('flip', function(e) {
            if (isInitializing) {
                console.log('[DEBUG] Flip event SKIPPED during init: e.data=' + e.data);
                return;
            }
            
            // St.PageFlip with showCover=true:
            //   Index 0 = Cover (PDF page 1 alone on right)
            //   Index 1 = Spread with PDF pages 2-3 (left=2, right=3)
            //   Index 2 = Spread with PDF pages 4-5 (left=4, right=5)
            //   e.data = the spread index displayed after flip
            var newPage = e.data === 0 ? 1 : (e.data * 2);
            console.log('[DEBUG] Flip event: e.data=' + e.data + ' newPage=' + newPage);
            currentPage = newPage;
            lastSavedPage = newPage;
            updateIndicators(currentPage);
            saveProgress(currentPage);
        });

        pageFlip.on('changeOrientation', function(e) {
            console.log('[DEBUG] Orientation changed to:', e.data);
        });

        // Start periodic save timer
        startPeriodicSave();
        
        // Load PDF and initialize flipbook
        setTimeout(function() {
            loadPdfAndSetProgress(pdfUrl, startPage);
        }, 50);
    } catch (err) {
        console.error('Failed to initialize flipbook:', err);
        showError('Failed: ' + err.message);
    }
}

function showError(message) {
    const loadingEl = document.getElementById('flipbookLoading');
    if (loadingEl) loadingEl.innerHTML = `<p style="color:red">${message}</p>`;
}

/**
 * Renders ALL PDF pages upfront, then initializes the flipbook with all images.
 * This is the most reliable approach - no lazy loading, no DOM probing.
 */
async function loadPdfAndSetProgress(pdfUrl, startPage) {
    isInitializing = true;
    try {
        if (typeof pdfjsLib === 'undefined' || !pdfjsLib.getDocument) {
            throw new Error('PDF.js library not loaded');
        }

        const loadingEl = document.getElementById('flipbookLoading');
        if (loadingEl) loadingEl.textContent = 'Loading PDF document...';

        pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
        totalPdfPages = pdfDoc.numPages;

        if (totalPdfPages === 0) throw new Error('PDF has no pages');

        console.log('Using start page:', startPage);
        currentPage = Math.min(Math.max(startPage, 1), totalPdfPages);

        // Render ALL pages upfront
        if (loadingEl) loadingEl.textContent = 'Rendering ' + totalPdfPages + ' pages... (this may take a moment)';
        
        var imageUrls = await renderAllPages();

        if (loadingEl) loadingEl.style.display = 'none';

        // Load all images into St.PageFlip at once
        console.log('[DEBUG] Loading ' + imageUrls.length + ' images into flipbook');
        pageFlip.loadFromImages(imageUrls);

        // Turn to the saved page
        var targetSpread = Math.floor(currentPage / 2);
        console.log('[DEBUG] loadPdfAndSetProgress: currentPage=' + currentPage + ' targetSpread=' + targetSpread);
        pageFlip.turnToPage(targetSpread);
        
        // Update indicators
        updateIndicators(currentPage);

    } catch (err) {
        console.error('Failed to load PDF:', err);
        showError('Failed to load PDF: ' + err.message);
    } finally {
        isInitializing = false;
    }
}

/**
 * Renders ALL pages of the PDF and returns an array of data URLs.
 * Pages are rendered sequentially to avoid overwhelming the browser.
 */
async function renderAllPages() {
    var imageUrls = [];
    
    for (var pageNum = 1; pageNum <= totalPdfPages; pageNum++) {
        try {
            var page = await pdfDoc.getPage(pageNum);
            // Higher scale = higher resolution images = sharper display
            // scale 3.0 gives ~3x the pixel density of scale 1.5
            var viewport = page.getViewport({ scale: 3.0 });

            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({ canvasContext: ctx, viewport: viewport }).promise;
            // Higher JPEG quality preserves more detail
            var imgData = canvas.toDataURL('image/jpeg', 0.95);
            imageUrls.push(imgData);
            
            // Free up memory
            canvas = null;
            page = null;
        } catch (err) {
            console.error('Failed to render page', pageNum, ':', err);
            // Use white placeholder for failed pages
            imageUrls.push('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%221%22 height=%221%22%3E%3Crect fill=%22%23ffffff%22 width=%221%22 height=%221%22/%3E%3C/svg%3E');
        }
    }
    
    return imageUrls;
}

function updateIndicators(pageNum) {
    const pageInfo = document.getElementById('flipbookPageInfo');
    if (pageInfo) pageInfo.textContent = 'Page ' + pageNum + ' / ' + totalPdfPages;

    const progressFill = document.getElementById('flipbookProgressFill');
    if (progressFill) progressFill.style.width = (pageNum / totalPdfPages) * 100 + '%';

    const prevBtn = document.getElementById('flipbookPrevBtn');
    const nextBtn = document.getElementById('flipbookNextBtn');
    if (prevBtn) prevBtn.disabled = pageNum <= 1;
    if (nextBtn) nextBtn.disabled = pageNum >= totalPdfPages;
}

function saveProgress(pageNum) {
    if (!pageNum || pageNum < 1 || !bookId) return;

    // Client-side instant save (reliable - always works)
    try { 
        localStorage.setItem('reading_progress_' + bookId, pageNum); 
    } catch (e) {}

    // Save to server - use sendBeacon + fetch keepalive for maximum reliability
    lastSavedPage = pageNum;
    
    var body = 'book_id=' + encodeURIComponent(bookId) + 
               '&chapter_id=0' + 
               '&page_number=' + encodeURIComponent(pageNum);
    
    // Method 1: fetch with keepalive (ensures request survives page navigation)
    try {
        fetch('index.php?page=ajax&action=save_reading_progress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: body,
            keepalive: true
        }).catch(function() {});
    } catch (e) {}
    
    // Method 2: sendBeacon (purpose-built for page unload saves)
    try {
        var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
        navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
    } catch(e) {}
}

function goNext() {
    if (!pdfDoc || !pageFlip || currentPage >= totalPdfPages) return;
    
    // Save current page before flipping (in case flip event doesn't fire)
    saveProgress(currentPage);
    
    pageFlip.turnToNextPage();
}

function goPrev() {
    if (!pdfDoc || !pageFlip || currentPage <= 1) return;
    
    // Save current page before flipping (in case flip event doesn't fire)
    saveProgress(currentPage);
    
    pageFlip.turnToPrevPage();
}

function turnToPage(pageNum) {
    if (!pdfDoc || !pageFlip || pageNum < 1 || pageNum > totalPdfPages) return;
    pageFlip.turnToPage(pageNum - 1);
}

function destroyFlipbook() {
    if (pageFlip) { pageFlip.destroy(); pageFlip = null; }
    pdfDoc = null;
    pageDOMs = [];
    pageStatus = [];
}

// Periodic save every 2 seconds as ultra-aggressive safety net
function startPeriodicSave() {
    if (periodicSaveInterval) clearInterval(periodicSaveInterval);
    periodicSaveInterval = setInterval(function() {
        if (bookId && currentPage > 0) {
            // Always save current page during periodic check (belt AND suspenders)
            saveProgress(currentPage);
        }
    }, 2000);
}

// TRIPLE-REDUNDANT save on page unload: synchronous XHR + async fallbacks
function sendProgressOnLeave() {
    if (!bookId || currentPage < 1) return;
    
    // Method 1: localStorage (100% reliable, survives everything)
    try { 
        localStorage.setItem('reading_progress_' + bookId, currentPage); 
    } catch (e) {}
    
    var body = 'book_id=' + encodeURIComponent(bookId) + 
               '&chapter_id=0&page_number=' + encodeURIComponent(currentPage);
    
    // Method 1: Synchronous XHR - BLOCKS until save completes (same as manga approach)
    try {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?page=ajax&action=save_reading_progress', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(body);
        return; // If sync XHR succeeded, we're done here
    } catch(e) {
        // Fall through to async methods if sync XHR is blocked by browser
    }
    
    // Method 2: sendBeacon with Blob (async backup if sync XHR blocked)
    try {
        var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
        navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
    } catch(e) {}
    
    // Method 3: Image beacon via GET (completely different transport)
    try {
        var img = new Image();
        img.src = 'index.php?page=ajax&action=save_reading_progress&' + body;
    } catch(e) {}
    
    // Method 4: fetch with keepalive
    try {
        fetch('index.php?page=ajax&action=save_reading_progress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
            keepalive: true
        }).catch(function() {});
    } catch(e) {}
}

// Register event listeners for page leave (refresh, close, navigate away)
window.addEventListener('beforeunload', sendProgressOnLeave);
window.addEventListener('pagehide', sendProgressOnLeave);

// Intercept ALL link clicks AND button onclick navigations
// This catches both <a> tags and <button onclick="window.location.href=...">
document.addEventListener('click', function(e) {
    if (!bookId || currentPage < 1) return;
    
    var link = e.target.closest('a');
    var isNavigation = false;
    var href = '';
    
    if (link) {
        href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            isNavigation = true;
            e.preventDefault();
        }
    }
    
    // Handle <button onclick="window.location.href='...'"> pattern
    // We can't prevent default on onclick, but we can save BEFORE it fires
    if (!isNavigation && (e.target.tagName === 'BUTTON' || e.target.closest('button'))) {
        // Save progress immediately before the onclick handler fires
        forceSyncSave();
        // Note: we can't prevent the onclick from executing, but the synchronous
        // save above will complete before the onclick handler starts
    }
    
    if (isNavigation) {
        // Save to localStorage (guaranteed to work)
        try { 
            localStorage.setItem('reading_progress_' + bookId, currentPage); 
        } catch (e) {}
        
        var body = 'book_id=' + encodeURIComponent(bookId) + 
                   '&chapter_id=0&page_number=' + encodeURIComponent(currentPage);
        
        // Synchronous XHR - blocks until save completes (manga approach)
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php?page=ajax&action=save_reading_progress', false);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(body);
        } catch(e) {
            // Async fallbacks if sync blocked
            try {
                var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
                navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
            } catch(e2) {}
            try {
                fetch('index.php?page=ajax&action=save_reading_progress', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body,
                    keepalive: true
                }).catch(function() {});
            } catch(e2) {}
        }
        
        // Navigate after save has completed
        window.location.href = href;
    }
}, true);

// Force sync save - used for button onclick interceptions
// This runs BEFORE the onclick handler executes, saving progress
function forceSyncSave() {
    if (!bookId || currentPage < 1) return;
    
    try { 
        localStorage.setItem('reading_progress_' + bookId, currentPage); 
    } catch (e) {}
    
    // Synchronous XHR (manga approach - blocks until save completes)
    try {
        var body = 'book_id=' + encodeURIComponent(bookId) + 
                   '&chapter_id=0&page_number=' + encodeURIComponent(currentPage);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?page=ajax&action=save_reading_progress', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(body);
    } catch(e) {
        // Async fallbacks if sync blocked
        try {
            var body = 'book_id=' + encodeURIComponent(bookId) + 
                       '&chapter_id=0&page_number=' + encodeURIComponent(currentPage);
            var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
            navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
        } catch(e2) {}
    }
}

// --- Zoom Functionality ---
let currentZoom = 0.9; 

window.addEventListener('DOMContentLoaded', () => {
    applyBookZoom(); 
});

window.zoomInBook = function() {
    currentZoom += 0.1;
    applyBookZoom();
};

window.zoomOutBook = function() {
    if (currentZoom > 0.4) { 
        currentZoom -= 0.1;
        applyBookZoom();
    }
};

function applyBookZoom() {
    const flipbook = document.getElementById('flipbookContainer');
    if (flipbook) {
        flipbook.style.transform = `scale(${currentZoom})`;
    }
}