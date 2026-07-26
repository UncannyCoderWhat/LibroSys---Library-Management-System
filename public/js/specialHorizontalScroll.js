(function () {
    let targetScrollLeft = null;
    let currentTrack = null;

    document.addEventListener('click', function (e) {
        const button = e.target.closest('[onclick*="scrollShelf"]');
        if (!button) return;

        const scrollTrack = button.parentElement?.querySelector('.special-scroll-track');
        if (!scrollTrack) return;

        e.preventDefault();
        e.stopPropagation();

        const item = scrollTrack.firstElementChild;
        const itemWidth = item ? item.getBoundingClientRect().width : 0;
        const gap = parseFloat(window.getComputedStyle(scrollTrack).gap) || 0;

        const scrollDistance = itemWidth > 0 ? (itemWidth + gap) : scrollTrack.clientWidth;
        const direction = button.getAttribute('onclick')?.includes('-') ? -1 : 1;

        if (currentTrack !== scrollTrack) {
            currentTrack = scrollTrack;
            targetScrollLeft = scrollTrack.scrollLeft;
        }

        const maxScroll = scrollTrack.scrollWidth - scrollTrack.clientWidth;
        targetScrollLeft = Math.max(0, Math.min(maxScroll, targetScrollLeft + (scrollDistance * direction)));

        scrollTrack.scrollTo({
            left: targetScrollLeft,
            behavior: 'smooth'
        });
    }, true);

    document.addEventListener('scroll', function (e) {
        if (e.target === currentTrack) {
            if (Math.abs(currentTrack.scrollLeft - targetScrollLeft) < 1) {
                targetScrollLeft = currentTrack.scrollLeft;
            }
        }
    }, true);
})();