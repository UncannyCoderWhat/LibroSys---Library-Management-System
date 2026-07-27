/**
 * LibroSys - Client Home Page JavaScript
 * Handles theme toggle, horizontal scroll, premium modal, and borrow modal.
 */

function scrollShelf(btn, amount) {
    const track = btn.parentElement.querySelector('.ls-scroll-track');
    if (track) {
        track.scrollBy({ left: amount, behavior: 'smooth' });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Theme toggle
    const themeToggle = document.getElementById("theme-toggle");
    if (themeToggle) {
        themeToggle.checked = (localStorage.getItem('theme') || 'dark') === 'dark';
        themeToggle.addEventListener("change", function () {
            const isDark = this.checked;
            const newTheme = isDark ? "dark" : "light";
            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
        });
    }
});
