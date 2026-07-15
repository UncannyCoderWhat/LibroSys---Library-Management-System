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
    const logs = button.getAttribute('data-logs');

    document.getElementById('modalUserId').textContent = userId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('modalEmail').textContent = button.getAttribute('data-email');
    document.getElementById('modalCredits').textContent = button.getAttribute('data-credits');
    document.getElementById('modalFines').textContent = button.getAttribute('data-fines');
    document.getElementById('modalBorrowed').textContent = button.getAttribute('data-borrowed');

    const fineBtn = document.getElementById('viewFineHistoryBtn');
    if (fineBtn) {
        fineBtn.setAttribute('data-user-name', username);
        fineBtn.setAttribute('data-user-fines', logs);
    }

    document.getElementById('userModal').style.display = 'block';
}

function triggerFineModal(buttonElement, event) {
    if (event) {
        event.stopPropagation();
    }
    
    const userName = document.getElementById('modalUsername').textContent || 'User';
    const logRows = document.querySelectorAll('#modalLogsBody tr');
    const fineDetails = [];

    logRows.forEach(row => {
        if (row.cells.length < 9) return;

        const title = row.cells[0].textContent.trim();
        const dueDate = row.cells[5].textContent.trim();
        const returnDate = row.cells[6].textContent.trim();
        const daysLate = parseInt(row.cells[7].textContent.trim()) || 0;
        const fineText = row.cells[8].textContent.trim();
        
        const fineAmount = parseFloat(fineText.replace(/[^0-9.-]+/g, "")) || 0;

        // Check if return date is empty or just placeholders like "-", "---", or "N/A"
        const isNotReturned = !returnDate || /^[-_\s]*$/.test(returnDate) || returnDate.toUpperCase() === 'N/A' || returnDate.toUpperCase() === 'OVERDUE';
        const isReturned = !isNotReturned;

        if (fineAmount > 0 || returnDate === 'OVERDUE' || daysLate > 0) {
            const isLiveOverdue = returnDate.includes('OVERDUE') || (isNotReturned && daysLate > 0);

            fineDetails.push({
                title: title,
                due_date: (dueDate && !/^[-_\s]*$/.test(dueDate)) ? dueDate : null,
                return_date: isReturned ? returnDate : null,
                is_live_overdue: isLiveOverdue,
                is_fine_paid: isReturned, 
                calculated_fine: fineAmount
            });
        }
    });

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
            let dueDate = 'N/A';
            if (item.due_date) {
                const parsedDue = new Date(item.due_date);
                dueDate = isNaN(parsedDue.getTime()) 
                    ? item.due_date 
                    : parsedDue.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
            }

            let returnDate = item.is_live_overdue ? '<span style="color:red; font-weight:bold;">OVERDUE</span>' : '---';
            if (item.return_date) {
                const parsedReturn = new Date(item.return_date);
                returnDate = isNaN(parsedReturn.getTime()) 
                    ? item.return_date 
                    : parsedReturn.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
            }

            const statusLabel = item.is_live_overdue ? 'Live Penalty' : 'Late Return';
            const statusClass = item.is_live_overdue ? 'on-queue' : 'unavailable';
            const paymentStatus = item.is_fine_paid ? '<span style="color:green; font-weight:bold;">PAID</span>' : '<span style="color:red; font-weight:bold;">UNPAID</span>';
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

window.addEventListener('click', function(event) {
    const modal = document.getElementById('fineModal');
    if (event.target === modal) {
        closeFineModal();
    }
});