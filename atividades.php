<?php
require_once 'db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Buscar data atual para o filtro padrão
$data_atual = date('Y-m-d');
$custom_css = 'css/atividades.css';
require_once 'includes/menu.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Registro de Atividades</h1>
        <p class="page-subtitle">Gerencie e acompanhe o tempo gasto em cada tarefa</p>
    </div>
    <button class="btn btn-primary" id="btn-nova-atividade">
        <i class="fas fa-plus"></i>
        <span>Nova Atividade</span>
    </button>
</div>

<div class="page-content">
    <!-- Filtros -->
    <div class="filtros-container">
        <div class="filtros-grid">
            <div class="filtro-group">
                <label for="filtro-data">
                    <i class="fas fa-calendar-alt"></i> Data
                </label>
                <input type="date" id="filtro-data" class="form-input" placeholder="Selecione uma data">
            </div>
            <div class="filtro-group">
                <label for="filtro-atividade">
                    <i class="fas fa-tag"></i> Atividade
                </label>
                <input type="text" id="filtro-atividade" class="form-input" placeholder="Buscar atividade..." autocomplete="off">
            </div>
            <div class="filtro-group">
                <label for="filtro-cliente">
                    <i class="fas fa-building"></i> Cliente
                </label>
                <input type="text" id="filtro-cliente" class="form-input" placeholder="Buscar cliente..." autocomplete="off">
            </div>
            <div class="filtro-group">
                <label for="filtro-ordenacao">
                    <i class="fas fa-sort"></i> Ordenar por
                </label>
                <select id="filtro-ordenacao" class="form-input">
                    <option value="data_inicio">Data e Hora Início</option>
                    <option value="atividade">Atividade</option>
                    <option value="cliente">Cliente</option>
                    <option value="data">Data</option>
                    <option value="hora_inicio">Hora Início</option>
                    <option value="hora_fim">Hora Fim</option>
                </select>
            </div>
            <div class="filtro-group actions" style="display: flex; align-items: flex-end;">
                <button class="btn btn-secondary w-full" id="btn-limpar-filtros" style="height: 38px;">
                    <i class="fas fa-times"></i>
                    <span>Limpar</span>
                </button>
            </div>
        </div>
        <div class="filtros-hint">
            <i class="fas fa-info-circle"></i>
            <span>Mostrando atividades do dia atual. Utilize os filtros para visualizar outros períodos</span>
        </div>
    </div>

    <!-- Tabela de Atividades -->
    <div class="tabela-container">
        <table class="atividades-table">
            <thead>
                <tr>
                    <th>Atividade</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Duração</th>
                    <th style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody id="atividades-tbody">
                <tr>
                    <td colspan="7" class="loading-cell">
                        <div class="spinner"></div>
                    </td>
                </tr>
            </tbody>
            <tfoot id="atividades-tfoot">
                <tr style="display: none;">
                    <td colspan="5" style="text-align: right;" class="total-label">Total de Horas</td>
                    <td id="total-horas" class="total-value">0h</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Paginação -->
    <div class="pagination" id="pagination"></div>
</div>

<!-- Modal de Cadastro/Edição -->
<div class="modal" id="atividade-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Nova Atividade</h2>
            <button class="modal-close">&times;</button>
        </div>
        <form id="atividade-form">
            <input type="hidden" id="atividade-id">
            
            <div class="form-group">
                <label for="nome_atividade">
                    <i class="fas fa-tag"></i> Nome da Atividade
                </label>
                <input type="text" id="nome_atividade" class="form-input" required 
                       placeholder="Ex: Desenvolvimento de funcionalidade" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="nome_cliente">
                    <i class="fas fa-building"></i> Nome do Cliente
                </label>
                <input type="text" id="nome_cliente" class="form-input" required 
                       placeholder="Ex: Empresa ABC" autocomplete="off">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="data_execucao">
                        <i class="fas fa-calendar"></i> Data
                    </label>
                    <input type="date" id="data_execucao" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="hora_inicio">
                        <i class="fas fa-play"></i> Início
                    </label>
                    <input type="time" id="hora_inicio" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="hora_fim">
                        <i class="fas fa-stop"></i> Fim
                    </label>
                    <input type="time" id="hora_fim" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label for="observacoes">
                    <i class="fas fa-sticky-note"></i> Observações
                </label>
                <textarea id="observacoes" class="form-input" 
                          placeholder="Adicione anotações sobre a atividade (opcional)"
                          rows="3" autocomplete="off"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span>Salvar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="confirm-modal" id="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <button class="confirm-modal-close">&times;</button>
        </div>
        <div class="confirm-modal-body">
            <p>Tem certeza que deseja excluir esta atividade?</p>
            <p class="atividade-info" id="atividade-info-exclusao">
                Esta ação não poderá ser desfeita.
            </p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" id="btn-cancelar-exclusao">Cancelar</button>
            <button class="btn btn-danger" id="btn-confirmar-exclusao">
                <i class="fas fa-trash"></i>
                <span>Excluir</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Visualização (somente leitura) -->
<div class="view-modal" id="view-modal">
    <div class="view-modal-content">
        <div class="view-modal-header">
            <div class="view-modal-title">
                <i class="fas fa-eye"></i>
                <h2>Detalhes da Atividade</h2>
            </div>
            <button class="view-modal-close">&times;</button>
        </div>
        <div class="view-modal-body">
            <div class="view-info-grid">
                <div class="view-info-card highlight">
                    <span class="view-info-label"><i class="fas fa-tag"></i> Atividade</span>
                    <span class="view-info-value" id="view-nome-atividade">—</span>
                </div>
                <div class="view-info-card">
                    <span class="view-info-label"><i class="fas fa-building"></i> Cliente</span>
                    <span class="view-info-value" id="view-nome-cliente">—</span>
                </div>
            </div>

            <div class="view-time-row">
                <div class="view-time-card">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="view-time-label">Data</span>
                    <span class="view-time-value" id="view-data">—</span>
                </div>
                <div class="view-time-card">
                    <i class="fas fa-play-circle"></i>
                    <span class="view-time-label">Início</span>
                    <span class="view-time-value" id="view-hora-inicio">—</span>
                </div>
                <div class="view-time-card">
                    <i class="fas fa-stop-circle"></i>
                    <span class="view-time-label">Fim</span>
                    <span class="view-time-value" id="view-hora-fim">—</span>
                </div>
                <div class="view-time-card duration">
                    <i class="fas fa-clock"></i>
                    <span class="view-time-label">Duração</span>
                    <span class="view-time-value" id="view-duracao">—</span>
                </div>
            </div>

            <div class="view-obs-wrap" id="view-obs-wrap">
                <span class="view-info-label"><i class="fas fa-sticky-note"></i> Observações</span>
                <p class="view-obs-text" id="view-observacoes"></p>
            </div>
        </div>
        <div class="view-modal-footer">
            <button class="btn btn-secondary" id="btn-fechar-view">Fechar</button>
        </div>
    </div>
</div>

<script src="js/atividades.js"></script>
<?php require_once 'includes/footer.php'; ?>