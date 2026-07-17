document.addEventListener('DOMContentLoaded', function() {
    const dp = document.querySelector('.dropdown');
    const dpwrapper = document.querySelector('.dpwrapper');

    if (dp && dpwrapper) {
        dp.addEventListener('click', function(event) {
            event.stopPropagation(); 
            dpwrapper.classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            if (!dpwrapper.contains(event.target) && event.target !== dp) {
                dpwrapper.classList.remove('active');
            }
        });
    }
});