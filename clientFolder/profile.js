/**
 * LibroSys - Profile Management Script
 */

function returnBook(borrowId) {
    if (!confirm('Are you sure you want to return this book?')) return;

    const formData = new FormData();
    formData.append('borrow_id', borrowId);

    fetch('return_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAsRead(notificationId) {
    const formData = new FormData();
    formData.append('notification_id', notificationId);

    fetch('mark_read.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const element = document.getElementById(`notif-${notificationId}`);
            if (element) element.style.display = 'none';
        }
    })
    .catch(error => console.error('Error:', error));
}