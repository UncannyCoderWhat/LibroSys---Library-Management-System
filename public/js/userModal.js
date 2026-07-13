function openUserModal(button) {
    document.getElementById('modalUserId').textContent = button.dataset.id;
    document.getElementById('modalUsername').textContent = button.dataset.username;
    document.getElementById('modalEmail').textContent = button.dataset.email;
    document.getElementById('modalCredits').textContent = button.dataset.credits;
    document.getElementById('modalFines').textContent = button.dataset.fines;
    document.getElementById('modalBorrowed').textContent = button.dataset.borrowed;
    document.getElementById('modalDeleteTargetId').value = button.dataset.id;

    const logs = JSON.parse(button.dataset.logs || '[]');
    const tbody = document.getElementById('modalLogsBody');
    tbody.innerHTML = '';

    if (logs.length > 0) {
        logs.forEach(log => {
            const row = `<tr>
            <td>${log.title || '-'}</td>
            <td>${log.author || '-'}</td>
            <td>${log.type || '-'}</td>
            <td>${log.time_borrowed || '-'}</td>
            <td>${log.date_borrowed || '-'}</td>
            <td>${log.due_date || '-'}</td>
            <td>${log.date_returned || '-'}</td>
            <td>${log.days_late || '0'}</td>
            <td>₱${log.total_fine || '0.00'}</td>
            </tr>`;
            tbody.innerHTML += row;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="9">No circulation logs found.</td></tr>';
    }

    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}