<?php
require_once 'db_connect.php';
require_once 'includes/menu.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Buscar dados do dashboard via API (será carregado via JavaScript)
?>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Olá, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
            <p>Aqui está seu resumo de atividades</p>
        </div>

        <div class="stats-grid" id="stats-container">
            <div class="stat-card loading">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3>Atividades Hoje</h3>
                    <p class="stat-value">-</p>
                </div>
            </div>

            <div class="stat-card loading">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Horas Hoje</h3>
                    <p class="stat-value">-</p>
                </div>
            </div>

            <div class="stat-card loading">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Horas na Semana</h3>
                    <p class="stat-value">-</p>
                </div>
            </div>

            <div class="stat-card loading">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Horas no Mês</h3>
                    <p class="stat-value">-</p>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Atividades por Dia</h3>
                <canvas id="activitiesChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Horas por Dia</h3>
                <canvas id="hoursChart"></canvas>
            </div>
        </div>

        <div class="recent-activities">
            <h3>Últimas Atividades</h3>
            <div class="activities-list" id="recent-activities">
                <div class="loading-spinner"></div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>