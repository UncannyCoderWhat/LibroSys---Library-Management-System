document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('userSearch');
    const userContainer = document.getElementById('userContainer');

    userSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = userContainer.querySelectorAll('.user-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const id = card.getAttribute('data-id');
            const email = card.getAttribute('data-email');

            const matches = name.includes(searchTerm) ||
                id.includes(searchTerm) ||
                email.includes(searchTerm);
            card.style.display = matches ? 'flex' : 'none';
        });
    });
});

function openFineModal(details, userName) {
    document.getElementById('modalUserName').innerText = userName.toUpperCase();
    const tbody = document.getElementById('fineTableBody');
    tbody.innerHTML = '';

    if (details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #888;">No history records found for this user.</td></tr>';
    }

    details.forEach(item => {
        const dueDate = item.due_date
            ? new Date(item.due_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})
            : 'N/A';

        const returnDate = item.return_date
            ? new Date(item.return_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})
            : (item.is_live_overdue ? '<span style="color:red; font-weight:bold;">OVERDUE</span>' : '---');

        const statusLabel = item.is_live_overdue ? 'Live Penalty' : 'Late Return';
        const statusClass = item.is_live_overdue ? 'on-queue' : 'unavailable';

        const paymentStatus = item.is_fine_paid ? '<span style="color:green;">PAID</span>' : '<span style="color:red;">UNPAID</span>';

        const row = `
            <tr>
            <td><strong>${item.title}</strong></td>
            <td>${dueDate}</td>
            <td>${returnDate}</td>
            <td>
                <span class="status-badge ${statusClass}">${statusLabel}</span><br>
                <small>Status: ${paymentStatus}</small>
            </td>
                <td style="color: red; font-weight: bold;">₱${parseFloat(item.calculated_fine).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    document.getElementById('fineModal').style.display = 'flex';
}

function closeFineModal() {
    document.getElementById('fineModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('fineModal');
    if (event.target == modal) {
        closeFineModal();
    }
}