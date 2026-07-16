function openPremiumModal() {
            document.getElementById('premiumModal').style.display = 'flex';
        }

        function closePremiumModal() {
            document.getElementById('premiumModal').style.display = 'none';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('premiumModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }