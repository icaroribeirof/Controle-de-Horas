document.addEventListener('DOMContentLoaded', function() {
    // Preencher o filtro de data com o dia atual
    const filtroData = document.getElementById('filtro-data');
    const dataAtual = getCurrentDate();
    filtroData.value = dataAtual;
    
    // Carregar atividades com a data atual como filtro
    loadAtividades(1, { data: dataAtual });
    setupEventListeners();
    setupAutoFilters();
});

let currentPage = 1;
let totalPages = 1;
let currentFilters = {};
let currentSort = 'data_inicio';
let deleteId = null;

// Obter data atual no formato YYYY-MM-DD (corrigido para fuso horário local)
function getCurrentDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function setupEventListeners() {
    // Nova atividade
    document.getElementById('btn-nova-atividade').addEventListener('click', () => {
        openModal();
    });

    // Fechar modal
    document.querySelector('.modal-close').addEventListener('click', closeModal);
    document.getElementById('btn-cancelar').addEventListener('click', closeModal);

    // Clique fora do modal
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal();
        }
    });

    // Formulário
    document.getElementById('atividade-form').addEventListener('submit', handleSubmit);

    // Botão limpar filtros
    document.getElementById('btn-limpar-filtros').addEventListener('click', clearFilters);

    // Validação de horas
    document.getElementById('hora_inicio').addEventListener('change', validateHours);
    document.getElementById('hora_fim').addEventListener('change', validateHours);

    // Dropdown de ordenação
    document.getElementById('filtro-ordenacao').addEventListener('change', (e) => {
        currentSort = e.target.value;
        loadAtividades(1, currentFilters);
    });

    // Modal de confirmação
    document.getElementById('btn-confirmar-exclusao').addEventListener('click', confirmDelete);
    document.getElementById('btn-cancelar-exclusao').addEventListener('click', closeConfirmModal);
    document.querySelector('.confirm-modal-close').addEventListener('click', closeConfirmModal);
    
    // Fechar modal de confirmação clicando fora
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('confirm-modal')) {
            closeConfirmModal();
        }
    });
}

function setupAutoFilters() {
    // Filtros automáticos ao digitar
    const filterInputs = ['filtro-data', 'filtro-atividade', 'filtro-cliente'];
    
    filterInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            let timeout = null;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });
        }
    });
}

async function loadAtividades(page = 1, filters = {}) {
    const tbody = document.getElementById('atividades-tbody');
    tbody.innerHTML = '保持<td colspan="7" class="loading-cell"><div class="loading-spinner"></div>';

    try {
        const queryParams = new URLSearchParams({
            page,
            ordenacao: currentSort,
            ...filters
        });

        const response = await fetch(`api/api_atividades.php?${queryParams}`);
        const data = await response.json();

        if (data.success) {
            renderAtividades(data.atividades);
            renderPagination(data.pagination);
            displayTotalHoras(data.total_duracao_minutos);
            currentPage = data.pagination.current_page;
            totalPages = data.pagination.total_pages;
        } else {
            showNotification('Erro ao carregar atividades', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro de conexão', 'error');
    }
}

function renderAtividades(atividades) {
    const tbody = document.getElementById('atividades-tbody');

    if (!atividades || atividades.length === 0) {
        tbody.innerHTML = '保持<td colspan="7" class="loading-cell">Nenhuma atividade encontrada';
        return;
    }

    tbody.innerHTML = atividades.map(atividade => `
        <tr>
            <td>${escapeHtml(atividade.nome_atividade)}</td>
            <td>${escapeHtml(atividade.nome_cliente)}</td>
            <td>${formatDate(atividade.data_execucao)}</td>
            <td>${atividade.hora_inicio.substr(0,5)}</td>
            <td>${atividade.hora_fim.substr(0,5)}</td>
            <td>${calculateDuration(atividade.hora_inicio, atividade.hora_fim)}</td>
            <td>
                <button class="acao-btn editar" onclick="editAtividade(${atividade.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="acao-btn excluir" onclick="openDeleteModal(${atividade.id})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
              </td>
         </tr>
    `).join('');
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    let html = '';

    if (pagination.total_pages > 1) {
        html += `<button class="page-btn" onclick="changePage(1)" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    <i class="fas fa-angle-double-left"></i>
                 </button>`;
        html += `<button class="page-btn" onclick="changePage(${pagination.current_page - 1})" 
                    ${pagination.current_page === 1 ? 'disabled' : ''}>
                    <i class="fas fa-angle-left"></i>
                 </button>`;

        for (let i = 1; i <= pagination.total_pages; i++) {
            if (
                i === 1 ||
                i === pagination.total_pages ||
                (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)
            ) {
                html += `<button class="page-btn ${i === pagination.current_page ? 'active' : ''}" 
                            onclick="changePage(${i})">${i}</button>`;
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                html += '<span class="page-dots">...</span>';
            }
        }

        html += `<button class="page-btn" onclick="changePage(${pagination.current_page + 1})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    <i class="fas fa-angle-right"></i>
                 </button>`;
        html += `<button class="page-btn" onclick="changePage(${pagination.total_pages})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    <i class="fas fa-angle-double-right"></i>
                 </button>`;
    }

    container.innerHTML = html;
}

function changePage(page) {
    if (page >= 1 && page <= totalPages && page !== currentPage) {
        loadAtividades(page, currentFilters);
    }
}

function openModal(atividade = null) {
    const modal = document.getElementById('atividade-modal');
    const form = document.getElementById('atividade-form');
    const title = document.getElementById('modal-title');

    // 🐛 FIX: SEMPRE limpar o ID primeiro para evitar que uma atividade substitua outra
    // Isso garante que um novo modal para criar atividade não tenha ID residual de edição anterior
    document.getElementById('atividade-id').value = '';

    if (atividade) {
        title.textContent = 'Editar Atividade';
        document.getElementById('atividade-id').value = atividade.id || '';
        document.getElementById('nome_atividade').value = atividade.nome_atividade || '';
        document.getElementById('nome_cliente').value = atividade.nome_cliente || '';
        document.getElementById('data_execucao').value = atividade.data_execucao || '';
        document.getElementById('hora_inicio').value = atividade.hora_inicio ? atividade.hora_inicio.substr(0,5) : '';
        document.getElementById('hora_fim').value = atividade.hora_fim ? atividade.hora_fim.substr(0,5) : '';
        document.getElementById('observacoes').value = atividade.observacoes || '';
    } else {
        title.textContent = 'Nova Atividade';
        form.reset();
        const today = getCurrentDate();
        document.getElementById('data_execucao').value = today;
    }

    modal.classList.add('show');
}

function closeModal() {
    document.getElementById('atividade-modal').classList.remove('show');
    document.getElementById('atividade-form').reset();
    // 🐛 FIX: Limpar ID ao fechar modal também
    document.getElementById('atividade-id').value = '';
}

async function handleSubmit(e) {
    e.preventDefault();

    if (!validateHours()) {
        return;
    }

    const formData = {
        id: document.getElementById('atividade-id').value,
        nome_atividade: document.getElementById('nome_atividade').value,
        nome_cliente: document.getElementById('nome_cliente').value,
        data_execucao: document.getElementById('data_execucao').value,
        hora_inicio: document.getElementById('hora_inicio').value,
        hora_fim: document.getElementById('hora_fim').value,
        observacoes: document.getElementById('observacoes').value
    };

    try {
        const response = await fetch('api/api_atividades.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Atividade salva com sucesso!', 'success');
            closeModal();
            loadAtividades(currentPage, currentFilters);
        } else {
            showNotification(data.message || 'Erro ao salvar atividade', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro de conexão', 'error');
    }
}

function validateHours() {
    const inicio = document.getElementById('hora_inicio').value;
    const fim = document.getElementById('hora_fim').value;

    if (inicio && fim && inicio >= fim) {
        showNotification('A hora de início deve ser menor que a hora de fim', 'error');
        return false;
    }
    return true;
}

async function editAtividade(id) {
    try {
        const response = await fetch(`api/api_atividades.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            openModal(data.atividade);
        } else {
            showNotification('Erro ao carregar atividade', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro de conexão', 'error');
    }
}

function openDeleteModal(id) {
    deleteId = id;
    document.getElementById('confirm-modal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.remove('show');
    deleteId = null;
}

async function confirmDelete() {
    if (!deleteId) return;

    try {
        const response = await fetch('api/api_atividades.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: deleteId })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Atividade excluída com sucesso!', 'success');
            closeConfirmModal();
            loadAtividades(currentPage, currentFilters);
        } else {
            showNotification(data.message || 'Erro ao excluir atividade', 'error');
            closeConfirmModal();
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro de conexão', 'error');
        closeConfirmModal();
    }
}

function applyFilters() {
    currentFilters = {
        data: document.getElementById('filtro-data').value,
        atividade: document.getElementById('filtro-atividade').value,
        cliente: document.getElementById('filtro-cliente').value
    };

    // Remover filtros vazios
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) {
            delete currentFilters[key];
        }
    });

    // Resetar para página 1 e recarregar com ordenação mantida
    loadAtividades(1, currentFilters);
}

function clearFilters() {
    const dataAtual = getCurrentDate();
    
    // Limpar campos de filtro (atividade e cliente)
    document.getElementById('filtro-atividade').value = '';
    document.getElementById('filtro-cliente').value = '';
    
    // Manter a data atual no campo de data
    document.getElementById('filtro-data').value = dataAtual;
    
    // Resetar ordenação para o padrão
    const ordenacaoSelect = document.getElementById('filtro-ordenacao');
    ordenacaoSelect.value = 'data_inicio';
    currentSort = 'data_inicio';
    
    // Definir filtros apenas com a data atual
    currentFilters = {
        data: dataAtual
    };
    
    // Recarregar atividades com a data atual
    loadAtividades(1, currentFilters);
}

function calculateDuration(start, end) {
    const startTime = new Date(`2000-01-01T${start}`);
    const endTime = new Date(`2000-01-01T${end}`);
    const diff = (endTime - startTime) / (1000 * 60);

    const hours = Math.floor(diff / 60);
    const minutes = diff % 60;

    return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
}

function displayTotalHoras(totalMinutos) {
    const tfoot = document.getElementById('atividades-tfoot');
    const totalHorasElement = document.getElementById('total-horas');
    
    if (totalMinutos && totalMinutos > 0) {
        const hours = Math.floor(totalMinutos / 60);
        const minutes = totalMinutos % 60;
        
        let totalText = '';
        if (hours > 0) {
            totalText = `${hours}h`;
            if (minutes > 0) {
                totalText += ` ${minutes}m`;
            }
        } else {
            totalText = `${minutes}m`;
        }
        
        totalHorasElement.textContent = totalText;
        tfoot.querySelector('tr').style.display = '';
    } else {
        tfoot.querySelector('tr').style.display = 'none';
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'success') {
    const oldNotification = document.querySelector('.notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
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

window.editAtividade = editAtividade;
window.openDeleteModal = openDeleteModal;
window.changePage = changePage;
