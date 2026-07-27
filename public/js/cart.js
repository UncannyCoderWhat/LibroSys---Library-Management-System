/**
 * LibroSys - Cart Page JavaScript
 * Handles remove from cart and checkout actions.
 */

function removeFromCart(bookId) {
    const formData = new FormData();
    formData.append('book_id', bookId);
    formData.append('action', 'remove_from_cart');
    fetch('index.php?page=ajax&action=borrow_handler', { method: 'POST', body: formData })
    .then(res => res.json()).then(data => { if(data.status === 'success') window.location.reload(); });
}

function processAction(action) {
    const formData = new FormData();
    formData.append('action', action);
    fetch('index.php?page=ajax&action=borrow_handler', { method: 'POST', body: formData })
    .then(res => res.json()).then(data => { alert(data.message); if(data.status === 'success') window.location.href = 'index.php?page=profile'; });
}
