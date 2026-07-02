/**
 * LibroSys - Browse Page JavaScript
 * Handles borrow modal, search/filter, and horizontal scroll.
 */

function openBorrowModal(book) {
    document.getElementById('selectedBookId').value = book.id;
    document.getElementById('modalTitle').innerText = book.title;
    document.getElementById('modalAuthor').innerText = "By " + book.author;
    document.getElementById('modalCover').src = "../" + book.cover_path;

    if (book.is_borrowed > 0) {
        document.getElementById('modalBorrowBtn').style.display = 'none';
        document.querySelector('.cart-btn').style.display = 'none';
        document.getElementById('modalReserveBtn').style.display = 'block';
    } else {
        document.getElementById('modalBorrowBtn').style.display = 'block';
        document.querySelector('.cart-btn').style.display = 'block';
        document.getElementById('modalReserveBtn').style.display = 'none';
    }

    document.getElementById('borrowModal').style.display = 'block';
}

function closeBorrowModal() {
    document.getElementById('borrowModal').style.display = 'none';
}

function processAction(actionType) {
    const bookId = document.getElementById('selectedBookId').value;
    const formData = new FormData();
    formData.append('book_id', bookId);
    formData.append('action', actionType);

    fetch('index.php?page=ajax&action=borrow_handler', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') closeBorrowModal();
        if (actionType === 'borrow' && data.status === 'success') window.location.href = 'index.php?page=profile';
    });
}

// Search and Filter Logic
document.addEventListener('DOMContentLoaded', function () {
    const browseSearch = document.getElementById('browseSearch');
    const genreFilter = document.getElementById('genreFilter');
    const exclusiveGrid = document.getElementById('exclusiveGrid');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');

    function filterBrowse() {
        const searchTerm = browseSearch.value.toLowerCase();
        const selectedGenre = genreFilter.value.toLowerCase();
        const shelves = document.querySelectorAll('.shelf-section');
        const noBooksFoundMessage = document.getElementById('noBooksFoundMessage');
        let totalVisibleCards = 0;

        shelves.forEach(shelf => {
            const cards = shelf.querySelectorAll('.book-card');
            let hasVisibleCard = false;

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const author = card.getAttribute('data-author');
                const genre = card.getAttribute('data-genre');

                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesGenre = selectedGenre === 'all' || genre === selectedGenre;

                if (matchesSearch && matchesGenre) {
                    card.style.display = 'flex';
                    hasVisibleCard = true;
                    totalVisibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });

            shelf.style.display = hasVisibleCard ? 'block' : 'none';
        });

        noBooksFoundMessage.style.display = (totalVisibleCards === 0) ? 'flex' : 'none';
    }

    if (browseSearch) browseSearch.addEventListener('input', filterBrowse);
    if (genreFilter) genreFilter.addEventListener('change', filterBrowse);

    if (exclusiveGrid && scrollRightBtn && scrollLeftBtn) {
        scrollRightBtn.addEventListener('click', () => {
            exclusiveGrid.scrollBy({ left: exclusiveGrid.clientWidth, behavior: 'smooth' });
        });
        scrollLeftBtn.addEventListener('click', () => {
            exclusiveGrid.scrollBy({ left: -exclusiveGrid.clientWidth, behavior: 'smooth' });
        });
    }
});
