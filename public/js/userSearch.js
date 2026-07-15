document.getElementById('userSearch').addEventListener('input', function () {
    const filter = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.user-activity-table tbody tr');

    rows.forEach(row => {
        // Skip the "No user found" placeholder row if it exists
        if (row.querySelector('.no-data-cell')) return;

        // Get the search fields
        const username = row.cells[0]?.textContent.toLowerCase() || '';
        const email = row.cells[1]?.textContent.toLowerCase() || '';
        
        // Extract the User ID from the action button's data-id attribute
        const button = row.querySelector('.btn-view-details');
        const userId = button ? button.getAttribute('data-id').toLowerCase() : '';

        // Match against name, email, or user ID
        if (username.includes(filter) || email.includes(filter) || userId.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});