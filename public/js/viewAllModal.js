// Single Book Modal Functions
function openBookModal(book) {
    document.getElementById('modalTitle').textContent = book.title;
    document.getElementById('modalAuthor').textContent = 'Author: ' + book.author;
    document.getElementById('modalCover').src = book.cover;
    
    let statusText = book.is_borrowed ? 'Status: Borrowed' : ('Status: ' + book.status);
    document.getElementById('modalStatus').textContent = statusText;
    
    document.getElementById('modalDetailLink').href = 'index.php?page=book_detail&id=' + book.id;
    
    document.getElementById('bookModal').style.display = 'flex';
}

function closeBookModal() {
    document.getElementById('bookModal').style.display = 'none';
}

// Special / View All Modal Functions
function openViewAllModal() {
    document.getElementById('viewAllModal').style.display = 'flex';
}

function closeViewAllModal() {
    document.getElementById('viewAllModal').style.display = 'none';
}

// New Arrivals Modal Functions
function openNewArrivalsModal() {
    document.getElementById('newArrivalsModal').style.display = 'flex';
}

function closeNewArrivalsModal() {
    document.getElementById('newArrivalsModal').style.display = 'none';
}

// Library Collection Modal Functions
function openLibraryModal() {
    document.getElementById('libraryModal').style.display = 'flex';
}

function closeLibraryModal() {
    document.getElementById('libraryModal').style.display = 'none';
}

// Available Now Modal Functions
function openAvailableModal() {
    document.getElementById('availableModal').style.display = 'flex';
}

function closeAvailableModal() {
    document.getElementById('availableModal').style.display = 'none';
}

// Dynamic Genre Modal Functions
function openGenreModal(genreSlug) {
    var modal = document.getElementById('genreModal_' + genreSlug);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeGenreModal(genreSlug) {
    var modal = document.getElementById('genreModal_' + genreSlug);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Dynamic Book Type Modal Functions
function openTypeModal(typeSlug) {
    var modal = document.getElementById('typeModal_' + typeSlug);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeTypeModal(typeSlug) {
    var modal = document.getElementById('typeModal_' + typeSlug);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modals when clicking outside content
window.onclick = function(event) {
    const bookModal = document.getElementById('bookModal');
    const viewAllModal = document.getElementById('viewAllModal');
    const newArrivalsModal = document.getElementById('newArrivalsModal');
    const libraryModal = document.getElementById('libraryModal');
    const availableModal = document.getElementById('availableModal');

    if (bookModal && event.target === bookModal) {
        closeBookModal();
    }
    if (viewAllModal && event.target === viewAllModal) {
        closeViewAllModal();
    }
    if (newArrivalsModal && event.target === newArrivalsModal) {
        closeNewArrivalsModal();
    }
    if (libraryModal && event.target === libraryModal) {
        closeLibraryModal();
    }
    if (availableModal && event.target === availableModal) {
        closeAvailableModal();
    }

    // Close any active dynamic genre modal if the backdrop area is clicked
    if (event.target.classList && event.target.classList.contains('genre-modal-instance')) {
        event.target.style.display = 'none';
    }

    // Close any active dynamic book type modal if the backdrop area is clicked
    if (event.target.classList && event.target.classList.contains('type-modal-instance')) {
        event.target.style.display = 'none';
    }
};