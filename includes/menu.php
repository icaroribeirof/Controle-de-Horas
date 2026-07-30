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

$current_page = basename($_SERVER['PHP_SELF']);
// Pegar iniciais do usuário para o avatar
$nome_parts = explode(' ', $_SESSION['usuario_nome'] ?? 'User');
$iniciais = strtoupper(substr($nome_parts[0], 0, 1) . (isset($nome_parts[1]) ? substr($nome_parts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo htmlspecialchars($tema); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Horas</title>
    <link rel="icon" href="ico/relogio.png">
    <!-- Fontes e Ícones -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos Globais e do App Shell -->
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/menu.css">
    <!-- Estilo Específico da Página -->
    <?php if (isset($custom_css)): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($custom_css); ?>">
    <?php endif; ?>
</head>
<body>
    <div class="app-wrapper">
        <!-- Mobile Header -->
        <header class="mobile-header">
            <div class="mobile-logo">
                <i class="fas fa-clock"></i>
                <span>Controle de Horas</span>
            </div>
            <button class="menu-toggle" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </header>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-clock"></i>
                    <span>Controle de Horas</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Principal</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="atividades.php" class="nav-link <?php echo $current_page == 'atividades.php' ? 'active' : ''; ?>">
                            <i class="fas fa-layer-group"></i>
                            <span>Atividades</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo htmlspecialchars($iniciais); ?></div>
                    <div class="user-info">
                        <span class="user-name" title="<?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>">
                            <?php 
                                $primeiroNome = $nome_parts[0] ?? '';
                                echo htmlspecialchars(strlen($primeiroNome) > 12 ? substr($primeiroNome, 0, 10).'...' : $primeiroNome); 
                            ?>
                        </span>
                        <span class="user-role">Membro</span>
                    </div>
                </div>
                <div style="display: flex; gap: 2px;">
                    <button id="theme-toggle" class="theme-toggle" title="Alternar tema">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="logout.php" class="logout-btn" title="Sair">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="main-content">