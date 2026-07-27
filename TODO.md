# Fix: PDF/Ebook Reading Progress Not Saving

## Root Cause Analysis
- **Problem**: PDF/ebook reading progress was not saving when users navigated away (return button, refresh, tab close)
- **Contrast**: Manga/comics reading progress worked perfectly

## Three Contributing Issues Found & Fixed:

### Fix 1: Database Duplicate Records (BACKEND FIX)
- **File**: `app/Controllers/Client/AjaxController.php`
- **What**: Changed DELETE query from `WHERE chapter_id = ?` to delete ALL old progress for user/book
- **Why**: Old saves stored `chapter_id = NULL`, new saves use `chapter_id = 0`. The old query only deleted matching chapter_id, leaving stale/null records that could be returned first by `ORDER BY updated_at DESC`
- **Impact**: The most critical fix - ensures only one clean progress record exists per user/book

### Fix 2: Missing Event Listeners (FRONTEND FIX)
- **File**: `public/js/flipbookReader.js`
- **What**: Added `window.addEventListener('beforeunload', sendProgressOnLeave)` and `window.addEventListener('pagehide', sendProgressOnLeave)` that were accidentally removed during edits
- **Why**: Without these listeners, no save was triggered on page refresh, tab close, or navigation

### Fix 3: Link Click Interception (FRONTEND FIX)
- **File**: `public/js/flipbookReader.js`
- **What**: Click handler intercepts all `<a>` tags (prevents default, saves via synchronous XHR, then navigates) AND calls `forceSyncSave()` on `<button>` clicks before their onclick handlers fire
- **Why**: Buttons with `onclick="window.location.href=..."` bypass `<a>` tag interception
- **Save Methods** (in order):
  1. Synchronous XHR (same as manga approach - blocks until save completes)
  2. localStorage (survives browser crash)
  3. fetch with keepalive (async fallback if sync XHR blocked)
  4. sendBeacon (last resort fallback)
  5. Image GET beacon (completely independent transport)

### Fix 4: Periodic Save Safety Net
- **File**: `public/js/flipbookReader.js`
- **What**: Every 2 seconds, saves current page to server
- **Why**: Catches any edge cases where flip event or unload handler fails

### Fix 5: Full Render Instead of Lazy Loading (FIXES BLANK PAGES)
- **File**: `public/js/flipbookReader.js`
- **What**: Completely rewrote the page rendering approach
  - **Old approach**: Render only visible/nearby pages, then try to dynamically update St.PageFlip's internal DOM elements after initialization — this failed because St.PageFlip v2.0.7 uses canvas internally and its `getPageElement()` API didn't exist or didn't work reliably
  - **New approach**: Render ALL PDF pages upfront in `renderAllPages()`, build a complete array of data URLs, then pass them all to `pageFlip.loadFromImages()` at once
- **Why**: Lazy loading into St.PageFlip after init is unreliable due to the library's internal canvas rendering pipeline. Rendering everything upfront guarantees all pages have content
- **Removed**:
  - `renderAndUpdateNearbyPages()` - no longer needed
  - `renderPageAndUpdateFlipbook()` - no longer needed
  - `loadVisiblePages()` - no longer needed
  - `renderPageForInit()` - no longer needed
  - `pageDOMs[]` and `pageStatus[]` tracking arrays
  - Duplicate `flip` event handler that triggered lazy loading
- **Added**:
  - `renderAllPages()` - renders all PDF pages sequentially into data URLs
  - Loading status messages ("Loading PDF document...", "Rendering X pages...")

## Files Modified:
- [x] `app/Controllers/Client/AjaxController.php` - DELETE all old progress records (fix duplicate row issue)
- [x] `public/js/flipbookReader.js` - Multiple fixes (sync XHR, event listeners, click interception, button handling, periodic save, full render)

## Testing:
1. Open a PDF book, navigate to page 5
2. Click "Back to Details" button
3. Re-open the book - should resume from page 5
4. Repeat test with browser refresh, tab close, and library link click
