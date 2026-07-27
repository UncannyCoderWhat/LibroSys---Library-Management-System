function openPremiumModal() {
    document.getElementById('premiumModal').style.display = 'flex';
}

function closePremiumModal() {
    document.getElementById('premiumModal').style.display = 'none';
}

// Fixed: Prevents window.onclick from breaking if scripts load early
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('click', function(event) {
        let modal = document.getElementById('premiumModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});