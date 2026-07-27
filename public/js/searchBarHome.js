document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector(".search-input");
    const searchForm = document.querySelector(".search-form");

    if (!searchInput) return;

    // Filter content live as user types
    searchInput.addEventListener("input", (e) => {
        const query = e.target.value.toLowerCase().trim();

        // 1. Filter card elements on the current page
        const cards = document.querySelectorAll(".card, .book-card, [class*='card']"); // adjust selector to match your book cards wrapper
        cards.forEach((card) => {
            const text = card.textContent.toLowerCase();
            if (text.includes(query)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    });

    // Handle Form Submit (Redirect or submit query)
    if (searchForm) {
        searchForm.addEventListener("submit", (e) => {
            const query = searchInput.value.trim();
            if (!query) {
                e.preventDefault();
            }
        });
    }
});