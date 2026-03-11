document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    setupThemeToggle();
    setupMobileMenu();
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

function updateCharts(chartData) {
    // Destruir gráficos existentes se houver
    Chart.helpers.each(Chart.instances, function(instance) {
        instance.destroy();
    });

    // Gráfico de atividades
    const activitiesCtx = document.getElementById('activitiesChart').getContext('2d');
    new Chart(activitiesCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Quantidade de Atividades',
                data: chartData.activities,
                backgroundColor: '#152AB3',
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
                        }
                    },
                    grid: {
                        color: function(context) {
                            return context.tick.value === 0 ? 'transparent' : 'rgba(0,0,0,0.1)';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de horas
    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    new Chart(hoursCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Horas Trabalhadas',
                data: chartData.hours,
                borderColor: '#152AB3',
                backgroundColor: 'rgba(21, 42, 179, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#152AB3',
                pointBorderColor: '#fff',
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
                        }
                    },
                    grid: {
                        color: function(context) {
                            return context.tick.value === 0 ? 'transparent' : 'rgba(0,0,0,0.1)';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
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

function setupThemeToggle() {
    const toggle = document.getElementById('theme-toggle');
    
    toggle.addEventListener('click', async () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'escuro' ? 'claro' : 'escuro';
        
        try {
            await fetch('api/api_dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theme: newTheme })
            });
            
            document.documentElement.setAttribute('data-theme', newTheme);
        } catch (error) {
            console.error('Erro ao alterar tema:', error);
        }
    });
}

function setupMobileMenu() {
    const toggle = document.getElementById('nav-toggle');
    const menu = document.querySelector('.nav-menu');
    
    toggle.addEventListener('click', () => {
        menu.classList.toggle('active');
    });
}

function showError(message) {
    // Implementar notificação de erro
    console.error(message);
}