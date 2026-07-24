const authWrapper = document.getElementById('authWrapper');
        const registerBtn = document.getElementById('registerBtn');
        const loginBtn = document.getElementById('loginBtn');

        registerBtn.addEventListener('click', () => {
            authWrapper.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            authWrapper.classList.remove('active');
        });