<?php
require_once 'db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$custom_css = 'css/dashboard.css';
require_once 'includes/menu.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Olá, <?php echo htmlspecialchars(explode(' ', $_SESSION['usuario_nome'])[0]); ?>!</h1>
        <p class="page-subtitle">Aqui está seu resumo de atividades</p>
    </div>
</div>

<div class="page-content">
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
        <div class="chart-card card">
            <div class="chart-header">
                <h3>Atividades por Dia</h3>
            </div>
            <div class="chart-body">
                <canvas id="activitiesChart"></canvas>
            </div>
        </div>
        <div class="chart-card card">
            <div class="chart-header">
                <h3>Horas por Dia</h3>
            </div>
            <div class="chart-body">
                <canvas id="hoursChart"></canvas>
            </div>
        </div>
    </div>

    <div class="recent-activities card">
        <div class="recent-header">
            <h3>Últimas Atividades</h3>
        </div>
        <div class="activities-list" id="recent-activities">
            <div class="loading-spinner"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>

<?php require_once 'includes/footer.php'; ?>