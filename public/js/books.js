/**
 * LibroSys - Enhanced Books Management Script
 */

let currentPage = 1;
let perPage = 25;

document.addEventListener('DOMContentLoaded', function() {
    // Search & Filter
    const bookSearch = document.getElementById('bookSearch');
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const bookTypeFilter = document.getElementById('bookTypeFilter');
    const genreFilter = document.getElementById('genreFilter');

    function filterAndPaginate() {
        const searchTerm = (bookSearch ? bookSearch.value : '').toLowerCase();
        const statusVal = statusFilter ? statusFilter.value : 'all';
        const categoryVal = categoryFilter ? categoryFilter.value : 'all';
        const bookTypeVal = bookTypeFilter ? bookTypeFilter.value : 'all';
        const genreVal = genreFilter ? genreFilter.value : 'all';

        const rows = document.querySelectorAll('.book-row');
        let visible = [];

        rows.forEach(row => {
            const title = (row.getAttribute('data-title') || '').toLowerCase();
            const author = (row.getAttribute('data-author') || '').toLowerCase();
            const isbn = (row.getAttribute('data-isbn') || '').toLowerCase();
            const genre = (row.getAttribute('data-genre') || '').toLowerCase();
            const bookType = (row.getAttribute('data-book_type') || '').toLowerCase();
            const category = (row.getAttribute('data-category') || '').toLowerCase();
            const status = row.getAttribute('data-status') || 'available';

            const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm) || isbn.includes(searchTerm);
            const matchesStatus = statusVal === 'all' || status === statusVal;
            const matchesCategory = categoryVal === 'all' || category === categoryVal.toLowerCase();
            const matchesBookType = bookTypeVal === 'all' || bookType === bookTypeVal.toLowerCase();
            const matchesGenre = genreVal === 'all' || genre.indexOf(genreVal.toLowerCase()) !== -1;

            if (matchesSearch && matchesStatus && matchesCategory && matchesBookType && matchesGenre) {
                visible.push(row);
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update result count
        const resultCount = document.getElementById('resultCount');
        if (resultCount) resultCount.textContent = visible.length + ' book(s)';

        // Paginate visible rows
        paginateRows(visible);
    }

    function paginateRows(visibleRows) {
        perPage = parseInt(document.getElementById('perPageSelect')?.value || 25);
        const totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        visibleRows.forEach((row, i) => {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });

        // Update pagination controls
        const pageInfo = document.getElementById('pageInfo');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');

        if (pageInfo) pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        // Store total pages for button handlers
        window._totalPages = totalPages;
    }

    if (bookSearch) bookSearch.addEventListener('input', function() { currentPage = 1; filterAndPaginate(); });
    if (statusFilter) statusFilter.addEventListener('change', function() { currentPage = 1; filterAndPaginate(); });
    if (categoryFilter) categoryFilter.addEventListener('change', function() { currentPage = 1; filterAndPaginate(); });
    if (bookTypeFilter) bookTypeFilter.addEventListener('change', function() { currentPage = 1; filterAndPaginate(); });
    if (genreFilter) genreFilter.addEventListener('change', function() { currentPage = 1; filterAndPaginate(); });

    // Initial pagination
    filterAndPaginate();

    // Add keyboard shortcut for search
    if (bookSearch) {
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                bookSearch.focus();
            }
        });
    }
});

function changePage(delta) {
    window._totalPages = window._totalPages || 1;
    currentPage = Math.max(1, Math.min(window._totalPages, currentPage + delta));
    // Re-trigger filter to apply pagination
    const event = new Event('input');
    const searchInput = document.getElementById('bookSearch');
    if (searchInput) searchInput.dispatchEvent(event);
    // Fallback: directly paginate
    const rows = document.querySelectorAll('.book-row');
    const visible = Array.from(rows).filter(r => r.style.display !== 'none');
    const pp = parseInt(document.getElementById('perPageSelect')?.value || 25);
    const total = Math.max(1, Math.ceil(visible.length / pp));
    const start = (currentPage - 1) * pp;
    const end = start + pp;
    visible.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });
    document.getElementById('pageInfo').textContent = 'Page ' + currentPage + ' of ' + total;
    document.getElementById('prevPage').disabled = currentPage <= 1;
    document.getElementById('nextPage').disabled = currentPage >= total;
    window._totalPages = total;
}

function resetPagination() {
    currentPage = 1;
    const event = new Event('input');
    const searchInput = document.getElementById('bookSearch');
    if (searchInput) searchInput.dispatchEvent(event);
}

// ==================== DROPDOWN ACTIONS ====================
function positionDropdown(menu, btn) {
    const rect = btn.getBoundingClientRect();
    const menuWidth = 180;
    const left = Math.max(8, Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8));
    menu.style.left = left + 'px';
    menu.style.top = (rect.bottom + 4) + 'px';
}

function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    if (!menu) return;
    const isOpen = menu.classList.contains('show');
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
        m.classList.remove('show');
        m._btn = null;
    });
    if (!isOpen) {
        positionDropdown(menu, btn);
        menu.classList.add('show');
        menu._btn = btn;
    }
}
// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-actions')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
            m._btn = null;
        });
    }
});
// Reposition dropdown on scroll and resize
function repositionOpenDropdowns() {
    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        if (menu._btn) {
            positionDropdown(menu, menu._btn);
        }
    });
}
window.addEventListener('scroll', repositionOpenDropdowns, true);
window.addEventListener('resize', repositionOpenDropdowns);

// Generic Modal Functions
function openModal(id) {
    // Close all open dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
        m.classList.remove('show');
        m._btn = null;
    });
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// Close modals on outside click
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(m => { if (event.target === m) m.style.display = 'none'; });
};

// Add Book Modal
function openAddModal() { openModal('addBookModal'); }

// ==================== GENRE CHIP SELECTOR ====================
// Initialize genre chip selection for add modal (script is at bottom of page, DOM is ready)
initGenreChips('add_genre_input', '#addBookModal .genre-chip');
// Initialize genre chip selection for edit modal
initGenreChips('edit_genre_input', '#editBookModal .genre-chip');

// Manga hint toggles
(function() {
    function isMangaType(val) {
        if (!val) return false;
        var v = val.toLowerCase();
        return v.indexOf('manga') !== -1 || v.indexOf('manhwa') !== -1 || v.indexOf('manhua') !== -1;
    }
    function bindToggle(selectId, hintId) {
        var select = document.getElementById(selectId);
        var hint = document.getElementById(hintId);
        if (!select || !hint) return;
        function update() {
            hint.style.display = isMangaType(select.value) ? 'block' : 'none';
        }
        update();
        select.addEventListener('change', update);
    }
    bindToggle('add_book_type', 'add_manga_hint');
    bindToggle('edit_book_type', 'edit_manga_hint');
})();

// Initialize genre chip selection for edit modal (called when edit modal opens to pre-select the current genre)
function initEditGenreChips(genreValue) {
    var chips = document.querySelectorAll('#edit_genre_chips .genre-chip');
    var input = document.getElementById('edit_genre_input');
    var selected = [];
    chips.forEach(function (chip) {
        chip.classList.remove('selected');
        var g = chip.getAttribute('data-genre');
        if (g && genreValue) {
            var genreList = genreValue.split(',').map(function(s) { return s.trim().toLowerCase(); });
            if (genreList.indexOf(g.toLowerCase()) !== -1) {
                chip.classList.add('selected');
                selected.push(g);
            }
        }
    });
    if (input) input.value = selected.join(', ');
}

function initGenreChips(inputId, selector) {
    var chips = document.querySelectorAll(selector);
    var input = document.getElementById(inputId);
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            this.classList.toggle('selected');
            var selected = [];
            chips.forEach(function (c) {
                if (c.classList.contains('selected')) {
                    selected.push(c.getAttribute('data-genre') || '');
                }
            });
            if (input) input.value = selected.join(', ');
        });
    });
}

// Edit Book Modal
function openEditModal(book) {
    function val(id) { return document.getElementById(id); }
    function setVal(id, v) { if (val(id)) val(id).value = v ?? ''; }
    function setChecked(id, v) { var el = val(id); if (el) el.checked = !!v; }
    setVal('edit_book_id', book.id);
    setVal('edit_title', book.title);
    setVal('edit_author', book.author);
    setVal('edit_isbn', book.isbn);
    setVal('edit_current_cover', book.cover_path);
    setVal('edit_publisher', book.publisher);
    setVal('edit_publication_year', book.publication_year);
    setVal('edit_language', book.language || 'English');
    setVal('edit_shelf_location', book.shelf_location);
    setVal('edit_copies', book.copies || 1);
    setVal('edit_status', book.status || 'available');
    setVal('edit_category_id', book.category_id);
    setVal('edit_author_id', book.author_id);
    setVal('edit_publisher_id', book.publisher_id);
    setChecked('edit_is_exclusive', book.is_exclusive == 1);
    setVal('edit_book_type', book.book_type || '');
    setVal('edit_description', book.description);
    // Initialize genre chips for edit modal
    initEditGenreChips(book.genre);
    openModal('editBookModal');
}

// Delete Book
function confirmDelete(bookId) {
    if (confirm('Are you sure you want to permanently delete this book?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?page=admin_books';
        form.style.display = 'none';
        const idInput = document.createElement('input');
        idInput.type = 'hidden'; idInput.name = 'book_id'; idInput.value = bookId;
        const delInput = document.createElement('input');
        delInput.type = 'hidden'; delInput.name = 'delete_book'; delInput.value = '1';
        form.appendChild(idInput); form.appendChild(delInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Generic Action (archive, restore, unavailable)
function confirmAction(bookId, action) {
    const labels = {
        'archive_book': 'archive (hide from active collection)',
        'restore_book': 'restore (make available again)',
        'unavailable_book': 'mark as unavailable'
    };
    if (confirm('Are you sure you want to ' + (labels[action] || action) + ' this book?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?page=admin_books';
        form.style.display = 'none';
        const idInput = document.createElement('input');
        idInput.type = 'hidden'; idInput.name = 'book_id'; idInput.value = bookId;
        const actInput = document.createElement('input');
        actInput.type = 'hidden'; actInput.name = action; actInput.value = '1';
        form.appendChild(idInput); form.appendChild(actInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// ==================== MANAGEMENT TABS ====================
function switchMgmtTab(tabId) {
    document.querySelectorAll('.mgmt-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.mgmt-tabs .tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tabId)?.classList.add('active');
    document.querySelectorAll('.mgmt-tabs .tab').forEach(t => {
        if (t.textContent.toLowerCase().includes(tabId)) t.classList.add('active');
    });
}

// ==================== CATEGORY MODALS ====================
function openCategoryEditModal(cat) {
    document.getElementById('edit_cat_id').value = cat.id;
    document.getElementById('edit_cat_name').value = cat.name;
    document.getElementById('edit_cat_description').value = cat.description || '';
    openModal('editCategoryModal');
}

// ==================== AUTHOR MODALS ====================
function openAuthorAddModal() { openModal('addAuthorModal'); }

function openAuthorEditModal(author) {
    document.getElementById('edit_author_id_val').value = author.id;
    document.getElementById('edit_author_name').value = author.name;
    document.getElementById('edit_author_bio').value = author.bio || '';
    document.getElementById('edit_author_birth_year').value = author.birth_year || '';
    openModal('editAuthorModal');
}

function openAuthorBooksModal(authorId) {
    openModal('authorBooksModal');
    document.getElementById('authorBooksModalContent').innerHTML = '<p style="text-align:center;padding:20px;">Loading...</p>';
    fetch('index.php?page=admin_books&ajax=get_author_books&author_id=' + authorId)
        .then(function(response) { return response.text(); })
        .then(function(html) {
            document.getElementById('authorBooksModalContent').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('authorBooksModalContent').innerHTML = '<p style="text-align:center;padding:20px;color:red;">Error loading author books.</p>';
        });
}

// ==================== PUBLISHER MODALS ====================
function openPublisherAddModal() { openModal('addPublisherModal'); }

function openPublisherEditModal(pub) {
    document.getElementById('edit_publisher_id_val').value = pub.id;
    document.getElementById('edit_publisher_name').value = pub.name;
    document.getElementById('edit_publisher_address').value = pub.address || '';
    document.getElementById('edit_publisher_website').value = pub.website || '';
    openModal('editPublisherModal');
}

function openPublisherBooksModal(publisherId) {
    openModal('publisherBooksModal');
    document.getElementById('publisherBooksModalContent').innerHTML = '<p style="text-align:center;padding:20px;">Loading...</p>';
    fetch('index.php?page=admin_books&ajax=get_publisher_books&publisher_id=' + publisherId)
        .then(function(response) { return response.text(); })
        .then(function(html) {
            document.getElementById('publisherBooksModalContent').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('publisherBooksModalContent').innerHTML = '<p style="text-align:center;padding:20px;color:red;">Error loading publisher books.</p>';
        });
}

// ==================== EBOOK MODAL ====================
function openEbookModal(bookId, bookTitle) {
    // Load via AJAX
    const container = document.getElementById('ebookModalContent');
    container.innerHTML = '<p style="text-align:center;padding:20px;">Loading eBook data...</p>';
    openModal('ebookModal');

    fetch('index.php?page=admin_books&ajax=get_ebooks&book_id=' + bookId)
        .then(r => r.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;padding:20px;color:red;">Failed to load eBook data.</p>';
        });
}

// ==================== COPIES MODAL ====================
function openCopiesModal(bookId, bookTitle) {
    const container = document.getElementById('copiesModalContent');
    container.innerHTML = '<p style="text-align:center;padding:20px;">Loading copies data...</p>';
    openModal('copiesModal');

    fetch('index.php?page=admin_books&ajax=get_copies&book_id=' + bookId)
        .then(r => r.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;padding:20px;color:red;">Failed to load copies data.</p>';
        });
}

// ==================== MANGA CHAPTERS MODAL ====================
function openMangaChaptersModal(bookId, bookTitle) {
    const container = document.getElementById('mangaChaptersModalContent');
    container.innerHTML = '<p style="text-align:center;padding:20px;">Loading chapters...</p>';
    openModal('mangaChaptersModal');
    window._mangaBookId = bookId;

    loadMangaChapters(bookId);
}

function loadMangaChapters(bookId) {
    const container = document.getElementById('mangaChaptersModalContent');
    fetch('index.php?page=admin_books&ajax=get_chapters&book_id=' + bookId)
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success' && !data.success) {
                container.innerHTML = '<p style="text-align:center;padding:20px;color:red;">Failed to load chapters.</p>';
                return;
            }
            renderMangaChapters(data.chapters || []);
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;padding:20px;color:red;">Failed to load chapters.</p>';
        });
}

function renderMangaChapters(chapters) {
    const container = document.getElementById('mangaChaptersModalContent');
    let html = '<h3 style="margin-bottom:12px;">Manga Chapters</h3>';

    if (!chapters.length) {
        html += '<p style="text-align:center;padding:15px;color:#888;">No chapters yet. Add your first chapter below.</p>';
    } else {
        html += '<div style="max-height:50vh;overflow-y:auto;margin-bottom:15px;">';
        chapters.forEach(function(ch) {
            const totalPages = ch.total_pages || 0;
            const statusBadge = ch.status === 'ready' ? '#d4edda' : (ch.status === 'error' ? '#f8d7da' : '#fff3cd');
            const statusText = ch.status === 'ready' ? 'Ready' : (ch.status === 'error' ? 'Error' : 'Draft');
            html += '<div class="chapter-item" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f7fafc;border-radius:8px;margin-bottom:8px;">';
            html += '<div style="flex:1;min-width:0;">';
            html += '<strong>Ch. ' + escapeHtml(ch.chapter_number) + '</strong>';
            if (ch.title) html += ' - ' + escapeHtml(ch.title);
            html += '<br><span style="font-size:11px;color:#888;">' + totalPages + ' pages</span>';
            html += '<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;background:' + statusBadge + ';margin-left:8px;">' + statusText + '</span>';
            html += '</div>';
            html += '<div style="display:flex;gap:6px;align-items:center;flex-shrink:0;margin-left:10px;">';
            html += '<button class="btn-sm btn-info" onclick="editChapter(' + ch.id + ', \'' + escapeHtml(ch.chapter_number) + '\', \'' + escapeHtml(ch.title || '') + '\')" style="padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#3498db;cursor:pointer;">Edit</button>';
            html += '<button class="btn-sm btn-primary" onclick="showUploadSection(' + ch.id + ')" style="padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#28a745;cursor:pointer;">Upload</button>';
            html += '<button class="btn-sm btn-danger" onclick="deleteChapter(' + ch.id + ')" style="padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#e74c3c;cursor:pointer;">Delete</button>';
            html += '</div></div>';
            html += '<div id="upload-section-' + ch.id + '" style="display:none;padding:10px 14px;background:#eef2f7;border-radius:8px;margin-bottom:8px;">';
            html += '<p style="font-size:12px;color:#555;margin-bottom:8px;">Upload individual images or a ZIP/CBZ archive for this chapter.</p>';
            html += '<form onsubmit="uploadChapterZip(event, ' + ch.id + ')" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">';
            html += '<input type="file" name="zip_file" accept=".zip,.cbz" required style="font-size:12px;flex:1;min-width:150px;">';
            html += '<button type="submit" class="btn-sm btn-primary" style="padding:6px 14px;font-size:12px;border:none;border-radius:4px;color:#fff;background:#28a745;cursor:pointer;">Upload ZIP</button>';
            html += '</form>';
            html += '<div style="margin-top:10px;border-top:1px solid #ddd;padding-top:10px;">';
            html += '<p style="font-size:12px;color:#555;margin-bottom:6px;">Or upload images individually:</p>';
            html += '<input type="file" id="single-page-' + ch.id + '" accept="image/*" style="font-size:12px;margin-bottom:6px;">';
            html += '<button onclick="uploadSinglePage(' + ch.id + ')" style="padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#3498db;cursor:pointer;">Upload Image</button>';
            html += '<span id="upload-status-' + ch.id + '" style="margin-left:8px;font-size:11px;color:#888;"></span>';
            html += '</div></div>';
        });
        html += '</div>';
    }

    html += '<div style="border-top:1px solid #ddd;padding-top:12px;">';
    html += '<h4 style="font-size:14px;margin-bottom:8px;">Add New Chapter</h4>';
    html += '<form onsubmit="addChapter(event)" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">';
    html += '<input type="text" name="chapter_number" placeholder="Chapter number (e.g. 1, 2, 3)" required style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;width:180px;">';
    html += '<input type="text" name="chapter_title" placeholder="Title (optional)" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;flex:1;min-width:120px;">';
    html += '<button type="submit" class="submit-btn" style="padding:8px 18px;font-size:13px;">Add Chapter</button>';
    html += '</form></div>';

    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addChapter(e) {
    e.preventDefault();
    const form = e.target;
    const chapterNumber = form.chapter_number.value.trim();
    const chapterTitle = form.chapter_title.value.trim();
    const bookId = window._mangaBookId;

    const formData = new FormData();
    formData.append('book_id', bookId);
    formData.append('chapter_number', chapterNumber);
    formData.append('chapter_title', chapterTitle);

    fetch('index.php?page=admin_books&ajax=add_chapter&book_id=' + bookId, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if ((data.status === 'success' || data.success === true)) {
            loadMangaChapters(bookId);
        } else {
            alert(data.message || 'Failed to add chapter.');
        }
    })
    .catch(() => alert('Error adding chapter.'));
}

function editChapter(chapterId, number, title) {
    const newNumber = prompt('Chapter number:', number);
    if (newNumber === null) return;
    const newTitle = prompt('Chapter title:', title);
    if (newTitle === null) return;

    const formData = new FormData();
    formData.append('chapter_id', chapterId);
    formData.append('chapter_number', newNumber.trim());
    formData.append('chapter_title', newTitle.trim());

    fetch('index.php?page=admin_books&ajax=update_chapter', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if ((data.status === 'success' || data.success === true)) {
            loadMangaChapters(window._mangaBookId);
        } else {
            alert(data.message || 'Failed to update chapter.');
        }
    })
    .catch(() => alert('Error updating chapter.'));
}

function deleteChapter(chapterId) {
    if (!confirm('Delete this chapter and all its pages?')) return;
    fetch('index.php?page=admin_books&ajax=delete_chapter&chapter_id=' + chapterId)
        .then(r => r.json())
        .then(data => {
            if ((data.status === 'success' || data.success === true)) {
                loadMangaChapters(window._mangaBookId);
            } else {
                alert(data.message || 'Failed to delete chapter.');
            }
        })
        .catch(() => alert('Error deleting chapter.'));
}

function showUploadSection(chapterId) {
    const section = document.getElementById('upload-section-' + chapterId);
    if (section) {
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }
}

function uploadChapterZip(e, chapterId) {
    e.preventDefault();
    const form = e.target;
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput.files.length) {
        alert('Please select a ZIP/CBZ file.');
        return;
    }

    const formData = new FormData();
    formData.append('book_id', window._mangaBookId);
    formData.append('chapter_id', chapterId);
    formData.append('zip_file', fileInput.files[0]);

    const statusEl = document.getElementById('upload-status-' + chapterId);
    if (statusEl) statusEl.textContent = 'Uploading...';

    fetch('index.php?page=admin_books&ajax=upload_chapter_zip&book_id=' + window._mangaBookId, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (statusEl) statusEl.textContent = data.message || '';
        if ((data.status === 'success' || data.success === true)) {
            loadMangaChapters(window._mangaBookId);
        } else {
            alert(data.message || 'Failed to upload archive.');
        }
    })
    .catch(() => {
        if (statusEl) statusEl.textContent = 'Upload failed.';
        alert('Error uploading archive.');
    });
}

function uploadSinglePage(chapterId) {
    const fileInput = document.getElementById('single-page-' + chapterId);
    if (!fileInput || !fileInput.files.length) {
        alert('Please select an image file.');
        return;
    }

    const formData = new FormData();
    formData.append('book_id', window._mangaBookId);
    formData.append('chapter_id', chapterId);
    formData.append('page_file', fileInput.files[0]);

    const statusEl = document.getElementById('upload-status-' + chapterId);
    if (statusEl) statusEl.textContent = 'Uploading...';

    fetch('index.php?page=admin_books&ajax=upload_chapter_page&book_id=' + window._mangaBookId, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (statusEl) statusEl.textContent = data.message || '';
        if ((data.status === 'success' || data.success === true)) {
            fileInput.value = '';
            loadMangaChapters(window._mangaBookId);
        } else {
            alert(data.message || 'Failed to upload image.');
        }
    })
    .catch(() => {
        if (statusEl) statusEl.textContent = 'Upload failed.';
        alert('Error uploading image.');
    });
}
