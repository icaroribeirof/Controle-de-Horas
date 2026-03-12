<?php
require_once 'db_connect.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Verificar se há tema salvo (caso o usuário já tenha acessado)
$tema = isset($_COOKIE['tema_preferido']) ? $_COOKIE['tema_preferido'] : 'claro';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $tema; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Controle de Horas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="ico/relogio.png">
</head>
<body>
    <div class="theme-toggle-container">
        <button id="theme-toggle-login" class="theme-toggle-login" title="Alternar tema">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-clock"></i>
                <h1>Controle de Horas</h1>
                <p>Gerencie seu tempo de forma eficiente</p>
            </div>
            
            <div class="tab-container">
                <button class="tab-btn active" id="login-tab">Login</button>
                <button class="tab-btn" id="register-tab">Cadastro</button>
            </div>

            <!-- Formulário de Login -->
            <form id="login-form" class="active">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="login-email" placeholder="E-mail" required autocomplete="email">
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="login-password" placeholder="Senha" required autocomplete="current-password">
                    <i class="fas fa-eye toggle-password"></i>
                </div>
                <button type="submit" class="btn-login">
                    <span>Entrar</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Formulário de Cadastro -->
            <form id="register-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="register-name" placeholder="Nome completo" required autocomplete="name">
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="register-email" placeholder="E-mail" required autocomplete="email">
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="register-password" placeholder="Senha" required autocomplete="new-password">
                    <i class="fas fa-eye toggle-password"></i>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="register-confirm-password" placeholder="Confirmar senha" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-register">
                    <span>Cadastrar</span>
                    <i class="fas fa-user-plus"></i>
                </button>
            </form>

            <div id="message-container"></div>
            
            <div class="login-footer">
                <span id="theme-footer-hint">Clique no <i class="fas fa-moon"></i> para alterar o tema</span>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
    <script>
        // Theme toggle para a página de login
        document.getElementById('theme-toggle-login')?.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'claro';
            const newTheme = currentTheme === 'escuro' ? 'claro' : 'escuro';
            
            // Salvar preferência em cookie por 30 dias
            document.cookie = `tema_preferido=${newTheme}; max-age=2592000; path=/`;
            
            html.setAttribute('data-theme', newTheme);
            
            // Mostrar notificação simples
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `<div class="message success">Tema ${newTheme === 'escuro' ? 'escuro' : 'claro'} ativado</div>`;
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 2000);
        });

        // Verificar tema ao carregar
        (function() {
            const savedTheme = document.cookie.split('; ').find(row => row.startsWith('tema_preferido='));
            if (savedTheme) {
                const theme = savedTheme.split('=')[1];
                document.documentElement.setAttribute('data-theme', theme);
            }
        })();
    </script>
</body>
</html>