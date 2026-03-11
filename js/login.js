document.addEventListener('DOMContentLoaded', function() {
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const messageContainer = document.getElementById('message-container');

    // Alternar entre tabs
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        clearMessages();
    });

    registerTab.addEventListener('click', () => {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
        clearMessages();
    });

    // Mostrar/ocultar senha
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Login
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('login-email').value.trim();
        const password = document.getElementById('login-password').value;

        if (!email || !password) {
            showMessage('Preencha todos os campos', 'error');
            return;
        }

        // Validação básica de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showMessage('E-mail inválido', 'error');
            return;
        }

        try {
            const response = await fetch('api/api_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Login realizado com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                showMessage(data.message || 'Erro ao fazer login', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de conexão com o servidor', 'error');
        }
    });

    // Cadastro
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('register-name').value.trim();
        const email = document.getElementById('register-email').value.trim();
        const password = document.getElementById('register-password').value;
        const confirmPassword = document.getElementById('register-confirm-password').value;

        // Validações
        if (!name || !email || !password || !confirmPassword) {
            showMessage('Preencha todos os campos', 'error');
            return;
        }

        if (name.length < 3) {
            showMessage('Nome deve ter pelo menos 3 caracteres', 'error');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showMessage('E-mail inválido', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showMessage('As senhas não coincidem', 'error');
            return;
        }

        if (password.length < 6) {
            showMessage('A senha deve ter pelo menos 6 caracteres', 'error');
            return;
        }

        try {
            const response = await fetch('api/api_register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name, email, password })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Cadastro realizado com sucesso! Faça login.', 'success');
                setTimeout(() => {
                    loginTab.click();
                    document.getElementById('login-email').value = email;
                }, 1500);
            } else {
                showMessage(data.message || 'Erro ao cadastrar', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de conexão com o servidor', 'error');
        }
    });

    function showMessage(text, type) {
        messageContainer.innerHTML = `<div class="message ${type}">${text}</div>`;
        // Rolar até a mensagem
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearMessages() {
        messageContainer.innerHTML = '';
    }

    // Limpar mensagens ao digitar
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', clearMessages);
    });
});