/**
 * LibroSys - StPageFlip Ebook Reader (Lazy Loading Optimized)
 * Creates a realistic 3D flipbook experience for ebooks using StPageFlip
 */

let pageFlip = null;
let pdfDoc = null;
let currentPage = 1;
let totalPdfPages = 0;
let renderTimeout = null;
let bookId = null;

// Track dynamically loaded pages
let pageDOMs = []; 
let pageStatus = []; // 0 = unloaded, 1 = loading, 2 = loaded

function initFlipbookReader(pdfUrl, startPage, bookIdParam) {
    console.log('initFlipbookReader called:', { pdfUrl, startPage, bookIdParam });
    bookId = bookIdParam;

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
            width: 450,         // Base width of ONE page
            height: 650,        // Base height of ONE page
            size: 'stretch',    // Allows the book to stretch and fit the container CSS
            minWidth: 300,
            maxWidth: 600,
            minHeight: 400,
            maxHeight: 900,
            showCover: true,    // Sets the first page as a single cover page on the right
            flippingTime: 800,
            usePortrait: false, // CRITICAL: Setting this to false enables the 2-page spread
            maxShadowOpacity: 0.5,
            mobileScrollSupport: true,
            drawShadow: true,
            swipeDistance: 30
        });

        pageFlip.on('flip', function(e) {
            const newPage = e.data + 1;
            currentPage = newPage;
            updateIndicators(currentPage);
            saveProgress(currentPage);
            
            // LAZY LOAD TRIGGER: Render nearby pages when flipping
            loadVisiblePages(currentPage);
        });

        pageFlip.on('changeState', function(e) {
            console.log('Page flip state:', e.data);
        });

        pageFlip.on('changeOrientation', function(e) {
            console.log('Orientation changed to:', e.data);
        });

        loadPdfAndSetProgress(pdfUrl, startPage);
    } catch (err) {
        console.error('Failed to initialize flipbook:', err);
        showError('Failed: ' + err.message);
    }
}

function showError(message) {
    const loadingEl = document.getElementById('flipbookLoading');
    if (loadingEl) loadingEl.innerHTML = `<p style="color:red">${message}</p>`;
}

async function loadPdfAndSetProgress(pdfUrl, startPage) {
    try {
        if (typeof pdfjsLib === 'undefined' || !pdfjsLib.getDocument) {
            throw new Error('PDF.js library not loaded');
        }

        const loadingEl = document.getElementById('flipbookLoading');
        pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
        totalPdfPages = pdfDoc.numPages;

        if (totalPdfPages === 0) throw new Error('PDF has no pages');

        currentPage = Math.min(Math.max(startPage, 1), totalPdfPages);
        
        // 1. Initialize arrays to track our pages
        pageDOMs = [];
        pageStatus = new Array(totalPdfPages + 1).fill(0); 
        
        // 2. Generate lightweight placeholder divs for EVERY page in the PDF
        const allPages = [];
        for (let i = 1; i <= totalPdfPages; i++) {
            const div = document.createElement('div');
            div.className = 'flipbook-page';
            div.style.cssText = 'width:100%;height:100%;display:flex;justify-content:center;align-items:center;background:#e5e5e5;padding:20px;box-sizing:border-box';
            div.innerHTML = `<div style="color:#888; font-weight: 600;">Loading Page ${i}...</div>`;
            
            pageDOMs[i] = div;
            allPages.push(div);
        }

        if (loadingEl) loadingEl.style.display = 'none';

        // 3. Load the empty placeholders into StPageFlip immediately
        pageFlip.loadFromHTML(allPages);
        
        // 4. Force render the specific pages the user is about to see
        await loadVisiblePages(currentPage);
        
        // 5. Turn to the saved page
        pageFlip.turnToPage(Math.max(0, currentPage - 1));
        updateIndicators(currentPage);
        saveProgress(currentPage);

    } catch (err) {
        console.error('Failed to load PDF:', err);
        showError('Failed to load PDF: ' + err.message);
    }
}

/**
 * Calculates which pages are visible or about to be visible,
 * and triggers their rendering process.
 */
async function loadVisiblePages(centerPage) {
    // We want to load the current page, the facing page, and a few buffer pages
    const pagesToLoad = [
        centerPage - 2, centerPage - 1, 
        centerPage, 
        centerPage + 1, centerPage + 2, centerPage + 3
    ];

    const renderPromises = [];
    
    for (let pageNum of pagesToLoad) {
        if (pageNum >= 1 && pageNum <= totalPdfPages) {
            renderPromises.push(renderPage(pageNum));
        }
    }
    
    // Process them concurrently
    await Promise.all(renderPromises);
}

/**
 * Converts a specific PDF page into an image and injects it into the placeholder DOM.
 */
async function renderPage(pageNum) {
    // Skip if page is already loaded (2) or currently loading (1)
    if (pageStatus[pageNum] !== 0) return;
    
    pageStatus[pageNum] = 1; // Mark as loading to prevent duplicate requests

    try {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1.5 });

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        await page.render({ canvasContext: ctx, viewport: viewport }).promise;
        const imgData = canvas.toDataURL('image/jpeg', 0.92);

        const img = document.createElement('img');
        img.src = imgData;
        img.alt = 'Page ' + pageNum;
        img.style.cssText = 'max-width:100%;max-height:calc(100% - 40px);object-fit:contain;box-shadow:0 4px 12px rgba(0,0,0,0.15);border-radius:4px';

        // Inject the generated image into the specific page's placeholder div
        const div = pageDOMs[pageNum];
        div.innerHTML = ''; 
        div.appendChild(img);
        
        pageStatus[pageNum] = 2; // Mark as fully loaded
    } catch (err) {
        console.error('Failed to render page', pageNum, ':', err);
        pageDOMs[pageNum].innerHTML = `<div style="color:#d32f2f;font-size:1rem;text-align:center;">Failed to load<br>Page ${pageNum}</div>`;
        pageStatus[pageNum] = 0; // Reset status so it can be retried later
    }
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
    try { localStorage.setItem('reading_progress_' + bookId, pageNum); } catch (e) {}

    const body = 'book_id=' + encodeURIComponent(bookId) + '&page_number=' + encodeURIComponent(pageNum);

    try {
        if (navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
            navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
        } else {
            fetch('index.php?page=ajax&action=save_reading_progress', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body,
                keepalive: true
            }).catch(function(err) { console.error('Failed to save progress:', err); });
        }
    } catch (err) {
        console.error('Failed to save progress:', err);
    }
}

function goNext() {
    if (!pdfDoc || currentPage >= totalPdfPages) return;
    currentPage++;
    if (pageFlip) pageFlip.turnToNextPage();
    updateIndicators(currentPage);
    saveProgress(currentPage);
}

function goPrev() {
    if (!pdfDoc || currentPage <= 1) return;
    currentPage--;
    if (pageFlip) pageFlip.turnToPrevPage();
    updateIndicators(currentPage);
    saveProgress(currentPage);
}

function turnToPage(pageNum) {
    if (!pdfDoc || pageNum < 1 || pageNum > totalPdfPages) return;
    currentPage = pageNum;
    if (pageFlip) pageFlip.turnToPage(pageNum - 1);
    updateIndicators(currentPage);
    saveProgress(currentPage);
}

function destroyFlipbook() {
    if (pageFlip) { pageFlip.destroy(); pageFlip = null; }
    pdfDoc = null;
    pageDOMs = [];
    pageStatus = [];
}

// Save progress when leaving the page
function scheduleLeaveSave() {
    if (!pdfDoc) return;

    const pageNum = currentPage > 0 ? currentPage : (parseInt(localStorage.getItem('reading_progress_' + bookId)) || 1);
    if (!bookId || pageNum <= 0) return;

    try {
        const body = 'book_id=' + encodeURIComponent(bookId) + '&page_number=' + encodeURIComponent(pageNum);
        if (navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
            navigator.sendBeacon('index.php?page=ajax&action=save_reading_progress', blob);
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php?page=ajax&action=save_reading_progress', false);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(body);
        }
    } catch (e) {}
}

window.addEventListener('beforeunload', scheduleLeaveSave);
window.addEventListener('pagehide', scheduleLeaveSave);

// --- Zoom Functionality ---
let currentZoom = 0.9; // Starts slightly zoomed out to prevent initial cut-off

window.addEventListener('DOMContentLoaded', () => {
    // Apply the initial default zoom once the DOM is ready
    applyBookZoom(); 
});

window.zoomInBook = function() {
    currentZoom += 0.1;
    applyBookZoom();
};

window.zoomOutBook = function() {
    if (currentZoom > 0.4) { // Prevents zooming out so far it disappears
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