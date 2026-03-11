<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Buscar tema do usuário
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT tema_preferido FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $tema = $usuario['tema_preferido'] ?? 'claro';
} catch (Exception $e) {
    $tema = 'claro';
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $tema; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Horas</title>
    <link rel="icon" type="image/x-icon" href="icon/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos da navbar */
        :root {
            --primary-color: #152AB3;
            --secondary-color: #f4f7f6;
            --text-dark: #333;
            --text-light: #666;
            --white: #fff;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        [data-theme="escuro"] {
            --primary-color: #4a6cf7;
            --secondary-color: #1a1a1a;
            --text-dark: #e0e0e0;
            --text-light: #a0a0a0;
            --white: #2d2d2d;
            --shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--secondary-color);
            color: var(--text-dark);
            transition: var(--transition);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .nav-logo i {
            font-size: 2rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-item {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .nav-link.logout:hover {
            background: #dc3545;
        }

        .theme-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-light);
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background: var(--secondary-color);
        }

        .theme-toggle .fa-sun {
            display: none;
        }

        [data-theme="escuro"] .theme-toggle .fa-sun {
            display: inline;
        }

        [data-theme="escuro"] .theme-toggle .fa-moon {
            display: none;
        }

        .nav-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        /* Notificações */
        .theme-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            box-shadow: var(--shadow);
            animation: slideInNotification 0.3s ease;
        }

        .theme-notification.error {
            background: #dc3545;
        }

        @keyframes slideInNotification {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutNotification {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .nav-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: var(--white);
                flex-direction: column;
                padding: 2rem;
                transition: var(--transition);
                box-shadow: var(--shadow);
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                width: 100%;
                justify-content: center;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-clock"></i>
                <span>Controle de Horas</span>
            </div>
            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="atividades.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'atividades.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tasks"></i>
                        <span>Atividades</span>
                    </a>
                </li>
                <li class="nav-item">
                    <button id="theme-toggle" class="theme-toggle" title="Alternar tema">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>
            <div class="nav-toggle" id="nav-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <script>
        // Menu mobile
        document.getElementById('nav-toggle')?.addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('active');
        });

        // Fechar menu ao clicar em um link (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('nav-menu')?.classList.remove('active');
            });
        });

        // Theme toggle com feedback visual
        document.getElementById('theme-toggle')?.addEventListener('click', async function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'claro';
            const newTheme = currentTheme === 'escuro' ? 'claro' : 'escuro';
            const button = this;
            
            // Desabilitar botão durante a requisição
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'wait';
            
            try {
                const response = await fetch('api/api_dashboard.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ theme: newTheme })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    html.setAttribute('data-theme', newTheme);
                    showThemeNotification(`Tema ${newTheme === 'escuro' ? 'escuro' : 'claro'} ativado`);
                } else {
                    console.error('Erro ao alterar tema:', data.message);
                    showThemeNotification('Erro ao alterar tema', 'error');
                }
            } catch (error) {
                console.error('Erro ao alterar tema:', error);
                showThemeNotification('Erro de conexão', 'error');
            } finally {
                // Reabilitar botão
                button.disabled = false;
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            }
        });

        // Função para mostrar notificação de tema
        function showThemeNotification(message, type = 'success') {
            // Remover notificação anterior se existir
            const oldNotification = document.querySelector('.theme-notification');
            if (oldNotification) {
                oldNotification.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = `theme-notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutNotification 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 2000);
        }

        // Prevenir que o menu fique aberto ao redimensionar para desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('nav-menu')?.classList.remove('active');
            }
        });
    </script>
<?php
?>