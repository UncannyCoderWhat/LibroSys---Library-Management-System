document.addEventListener('DOMContentLoaded', function() {
    const dp = document.querySelector('.dropdown');
    const dpwrapper = document.querySelector('.dpwrapper');
    const genres = document.querySelectorAll('.dpwrapper a');
    
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
        
        genres.forEach(genre => {
            genre.addEventListener('click', function(event) {
                event.preventDefault();
                
                genres.forEach(g => g.classList.remove('active'));
                this.classList.add('active');
                
                dpwrapper.classList.remove('active');
            });
        });
    }
});