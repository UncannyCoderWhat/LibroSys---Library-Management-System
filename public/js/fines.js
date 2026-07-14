document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('userSearch');
    const userContainer = document.getElementById('userContainer');

    if (userSearch && userContainer) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = userContainer.querySelectorAll('.user-card');

            cards.forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const id = (card.getAttribute('data-id') || '').toLowerCase();
                const email = (card.getAttribute('data-email') || '').toLowerCase();

                const matches = name.includes(searchTerm) ||
                    id.includes(searchTerm) ||
                    email.includes(searchTerm);
                
                card.style.display = matches ? 'flex' : 'none';
            });
        });
    }
});

function openUserModal(button) {
    const userId = button.getAttribute('data-id');
    const username = button.getAttribute('data-username');
    const logs = button.getAttribute('data-logs'); // fine/circulation logs JSON

    // Populate modal fields
    document.getElementById('modalUserId').textContent = userId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('modalEmail').textContent = button.getAttribute('data-email');
    document.getElementById('modalCredits').textContent = button.getAttribute('data-credits');
    document.getElementById('modalFines').textContent = button.getAttribute('data-fines');
    document.getElementById('modalBorrowed').textContent = button.getAttribute('data-borrowed');

    // Attach data directly to the View Fine History button for triggerFineModal
    const fineBtn = document.getElementById('viewFineHistoryBtn');
    if (fineBtn) {
        fineBtn.setAttribute('data-user-name', username);
        fineBtn.setAttribute('data-user-fines', logs);
    }

    // Show User Modal
    document.getElementById('userModal').style.display = 'block';
}

function triggerFineModal(buttonElement) {
    console.log("Button clicked!");
    
    const userName = buttonElement.getAttribute('data-user-name') || 'User';
    let finesRaw = buttonElement.getAttribute('data-user-fines') || '[]';
    
    let fineDetails = [];
    try {
        // Decode HTML entities (e.g. &quot; -> ") before parsing
        const txt = document.createElement('textarea');
        txt.innerHTML = finesRaw;
        finesRaw = txt.value;

        fineDetails = JSON.parse(finesRaw);
    } catch (e) {
        console.error("Error parsing fine data:", e, finesRaw);
    }

    openFineModal(fineDetails, userName);
}

function openFineModal(details, userName) {
    const modalUserName = document.getElementById('modalUserName');
    const tbody = document.getElementById('fineTableBody');
    const fineModal = document.getElementById('fineModal');

    if (modalUserName) {
        modalUserName.innerText = (userName || '').toUpperCase();
    }

    if (!tbody) return;

    tbody.innerHTML = '';

    if (!details || details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #888;">No history records found for this user.</td></tr>';
    } else {
        const rows = details.map(item => {
            const dueDate = item.due_date
                ? new Date(item.due_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})
                : 'N/A';

            const returnDate = item.return_date
                ? new Date(item.return_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})
                : (item.is_live_overdue ? '<span style="color:red; font-weight:bold;">OVERDUE</span>' : '---');

            const statusLabel = item.is_live_overdue ? 'Live Penalty' : 'Late Return';
            const statusClass = item.is_live_overdue ? 'on-queue' : 'unavailable';
            const paymentStatus = item.is_fine_paid ? '<span style="color:green;">PAID</span>' : '<span style="color:red;">UNPAID</span>';
            const fineAmount = parseFloat(item.calculated_fine || 0).toLocaleString(undefined, {minimumFractionDigits: 2});

            return `
                <tr>
                    <td><strong>${item.title || 'N/A'}</strong></td>
                    <td>${dueDate}</td>
                    <td>${returnDate}</td>
                    <td>
                        <span class="status-badge ${statusClass}">${statusLabel}</span><br>
                        <small>Status: ${paymentStatus}</small>
                    </td>
                    <td style="color: red; font-weight: bold;">₱${fineAmount}</td>
                </tr>
            `;
        });

        tbody.innerHTML = rows.join('');
    }

    if (fineModal) {
        // Force flex display and bring modal to top layer
        fineModal.style.display = 'flex';
        fineModal.style.zIndex = '10000';
    }
}

function closeFineModal() {
    const fineModal = document.getElementById('fineModal');
    if (fineModal) {
        fineModal.style.display = 'none';
    }
}

// Close fine modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('fineModal');
    if (event.target === modal) {
        closeFineModal();
    }
});