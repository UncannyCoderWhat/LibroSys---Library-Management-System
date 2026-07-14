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
    const genreFilter = document.getElementById('genreFilter');

    function filterAndPaginate() {
        const searchTerm = (bookSearch ? bookSearch.value : '').toLowerCase();
        const statusVal = statusFilter ? statusFilter.value : 'all';
        const categoryVal = categoryFilter ? categoryFilter.value : 'all';
        const genreVal = genreFilter ? genreFilter.value : 'all';

        const rows = document.querySelectorAll('.book-row');
        let visible = [];

        rows.forEach(row => {
            const title = (row.getAttribute('data-title') || '').toLowerCase();
            const author = (row.getAttribute('data-author') || '').toLowerCase();
            const isbn = (row.getAttribute('data-isbn') || '').toLowerCase();
            const genre = (row.getAttribute('data-genre') || '').toLowerCase();
            const category = (row.getAttribute('data-category') || '').toLowerCase();
            const status = row.getAttribute('data-status') || 'available';

            const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm) || isbn.includes(searchTerm);
            const matchesStatus = statusVal === 'all' || status === statusVal;
            const matchesCategory = categoryVal === 'all' || category === categoryVal.toLowerCase();
            const matchesGenre = genreVal === 'all' || genre === genreVal.toLowerCase();

            if (matchesSearch && matchesStatus && matchesCategory && matchesGenre) {
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
function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    if (!menu) return;
    const isOpen = menu.classList.contains('show');
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    if (!isOpen) {
        // Position the fixed dropdown relative to the button
        const rect = btn.getBoundingClientRect();
        menu.style.left = Math.max(8, rect.right - 180) + 'px';
        menu.style.top = (rect.bottom + 4) + 'px';
        menu.classList.add('show');
    }
}
// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-actions')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
});

// Generic Modal Functions
function openModal(id) { document.getElementById(id).style.display = 'block'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// Close modals on outside click
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(m => { if (event.target === m) m.style.display = 'none'; });
};

// Add Book Modal
function openAddModal() { openModal('addBookModal'); }

// Edit Book Modal
function openEditModal(book) {
    function val(id) { return document.getElementById(id); }
    function setVal(id, v) { if (val(id)) val(id).value = v ?? ''; }
    function setChecked(id, v) { var el = val(id); if (el) el.checked = !!v; }
    setVal('edit_book_id', book.id);
    setVal('edit_title', book.title);
    setVal('edit_author', book.author);
    setVal('edit_isbn', book.isbn);
    setVal('edit_genre', book.genre);
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
    setVal('edit_description', book.description);
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

// ==================== PUBLISHER MODALS ====================
function openPublisherAddModal() { openModal('addPublisherModal'); }

function openPublisherEditModal(pub) {
    document.getElementById('edit_publisher_id_val').value = pub.id;
    document.getElementById('edit_publisher_name').value = pub.name;
    document.getElementById('edit_publisher_address').value = pub.address || '';
    document.getElementById('edit_publisher_website').value = pub.website || '';
    openModal('editPublisherModal');
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
