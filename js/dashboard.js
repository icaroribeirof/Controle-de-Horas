document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    setupMobileMenu();
    
    // Escutar mudanças de tema disparadas pelo menu.php
    window.addEventListener('tema-alterado', function(e) {
        // Recarregar dados para atualizar cores dos gráficos se necessário
        // Mas sem recarregar a página inteira
        reloadChartsWithNewTheme();
    });
    
    // Também escutar mudanças no localStorage (para sincronizar entre abas)
    window.addEventListener('storage', function(e) {
        if (e.key === 'tema_preferido') {
            reloadChartsWithNewTheme();
        }
    });
});

async function loadDashboardData() {
    try {
        const response = await fetch('api/api_dashboard.php');
        const data = await response.json();

        if (data.success) {
            updateStats(data.stats);
            updateCharts(data.charts);
            updateRecentActivities(data.recent);
        } else {
            showError('Erro ao carregar dados');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro de conexão');
    }
}

function updateStats(stats) {
    const statsContainer = document.getElementById('stats-container');
    const statCards = statsContainer.querySelectorAll('.stat-card');
    
    statCards[0].querySelector('.stat-value').textContent = stats.activities_today || '0';
    statCards[1].querySelector('.stat-value').textContent = formatHours(stats.hours_today);
    statCards[2].querySelector('.stat-value').textContent = formatHours(stats.hours_week);
    statCards[3].querySelector('.stat-value').textContent = formatHours(stats.hours_month);
    
    statCards.forEach(card => card.classList.remove('loading'));
}

function formatHours(hours) {
    if (!hours && hours !== 0) return '0h';
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return m > 0 ? `${h}h ${m}m` : `${h}h`;
}

// Variáveis globais para os gráficos
let activitiesChart = null;
let hoursChart = null;

function updateCharts(chartData) {
    // Destruir gráficos existentes se houver
    if (activitiesChart) {
        activitiesChart.destroy();
    }
    if (hoursChart) {
        hoursChart.destroy();
    }

    // Obter as cores baseadas no tema atual
    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'escuro';
    const primaryColor = isDarkTheme ? '#4a6cf7' : '#152AB3';
    const bgColor = isDarkTheme ? 'rgba(74, 108, 247, 0.1)' : 'rgba(21, 42, 179, 0.1)';
    const gridColor = isDarkTheme ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
    const textColor = isDarkTheme ? '#e0e0e0' : '#666';

    // Gráfico de atividades (barras)
    const activitiesCtx = document.getElementById('activitiesChart').getContext('2d');
    activitiesChart = new Chart(activitiesCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Quantidade de Atividades',
                data: chartData.activities,
                backgroundColor: primaryColor,
                borderRadius: 5,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.raw} atividade${context.raw !== 1 ? 's' : ''}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value;
                        },
                        color: textColor
                    },
                    grid: {
                        color: function(context) {
                            return context.tick.value === 0 ? 'transparent' : gridColor;
                        }
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de horas (linha)
    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    hoursChart = new Chart(hoursCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Horas Trabalhadas',
                data: chartData.hours,
                borderColor: primaryColor,
                backgroundColor: bgColor,
                borderWidth: 3,
                pointBackgroundColor: primaryColor,
                pointBorderColor: isDarkTheme ? '#2d2d2d' : '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const hours = context.raw;
                            const h = Math.floor(hours);
                            const m = Math.round((hours - h) * 60);
                            return m > 0 ? `${h}h ${m}m` : `${h}h`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + 'h';
                        },
                        color: textColor
                    },
                    grid: {
                        color: function(context) {
                            return context.tick.value === 0 ? 'transparent' : gridColor;
                        }
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Função para recarregar os gráficos quando o tema mudar
async function reloadChartsWithNewTheme() {
    try {
        const response = await fetch('api/api_dashboard.php');
        const data = await response.json();
        
        if (data.success && data.charts) {
            updateCharts(data.charts);
        }
    } catch (error) {
        console.error('Erro ao recarregar gráficos:', error);
    }
}

function updateRecentActivities(activities) {
    const container = document.getElementById('recent-activities');
    
    if (!activities || activities.length === 0) {
        container.innerHTML = '<p class="no-activities">Nenhuma atividade recente</p>';
        return;
    }

    container.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-info">
                <h4>${escapeHtml(activity.nome_atividade)}</h4>
                <p>${escapeHtml(activity.nome_cliente)}</p>
            </div>
            <div class="activity-time">
                <div class="hours">${formatTimeRange(activity.hora_inicio, activity.hora_fim)}</div>
                <div class="date">${formatDate(activity.data_execucao)}</div>
            </div>
        </div>
    `).join('');
}

function formatTimeRange(start, end) {
    return `${start.substr(0,5)} - ${end.substr(0,5)}`;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function setupMobileMenu() {
    const toggle = document.getElementById('nav-toggle');
    const menu = document.querySelector('.nav-menu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    }
}

function showError(message) {
    console.error(message);
    // Criar notificação de erro visual
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}