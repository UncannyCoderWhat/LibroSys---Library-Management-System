/**
 * LibroSys - Books Management Script
 */

document.addEventListener('DOMContentLoaded', function() {
    // Search and Filter Logic
    const bookSearch = document.getElementById('bookSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const bookGrid = document.getElementById('bookGrid');

    function filterBooks() {
        const searchTerm = bookSearch.value.toLowerCase();
        const categoryTerm = categoryFilter.value;
        const bookCards = bookGrid.querySelectorAll('.book-card');

        bookCards.forEach(card => {
            const title = card.getAttribute('data-title') || "";
            const author = card.getAttribute('data-author') || "";
            const category = card.getAttribute('data-category') || "";

            const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
            const matchesCategory = categoryTerm === 'all' || category === categoryTerm;

            card.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
        });
    }

    if (bookSearch) bookSearch.addEventListener('input', filterBooks);
    if (categoryFilter) categoryFilter.addEventListener('change', filterBooks);
});

// Modal Logic (Kept in global scope for the inline onclick handler)
function openEditModal(book) {
    document.getElementById('edit_book_id').value = book.id;
    document.getElementById('edit_title').value = book.title;
    document.getElementById('edit_author').value = book.author;
    document.getElementById('edit_isbn').value = book.isbn;
    document.getElementById('edit_current_cover').value = book.cover_path;
    document.getElementById('edit_is_exclusive').checked = book.is_exclusive == 1;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside of the modal content
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) closeEditModal();
};

function confirmDelete(bookId) {
    if (confirm("Are you sure you want to delete this book? This action will remove it from the active library collection.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'books.php';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'book_id';
        idInput.value = bookId;

        const deleteInput = document.createElement('input');
        deleteInput.type = 'hidden';
        deleteInput.name = 'delete_book';
        deleteInput.value = '1';

        form.appendChild(idInput);
        form.appendChild(deleteInput);
        document.body.appendChild(form);
        form.submit();
    }
}