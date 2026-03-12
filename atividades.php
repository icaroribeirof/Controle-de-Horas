<?php
require_once 'db_connect.php';
require_once 'includes/menu.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
    <link rel="stylesheet" href="css/atividades.css">
    <link rel="icon" href="ico/relogio.png">
</head>
<body>
    <main class="atividades-container">
        <div class="atividades-header">
            <h1>Registro de Atividades</h1>
            <button class="btn-nova-atividade" id="btn-nova-atividade">
                <i class="fas fa-plus"></i>
                Nova Atividade
            </button>
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
                            <i class="fas fa-tag"></i>
                            Nome da Atividade
                        </label>
                        <input type="text" id="nome_atividade" required 
                               placeholder="Ex: Desenvolvimento de funcionalidade"
                               autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="nome_cliente">
                            <i class="fas fa-building"></i>
                            Nome do Cliente
                        </label>
                        <input type="text" id="nome_cliente" required 
                               placeholder="Ex: Empresa ABC"
                               autocomplete="off">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_execucao">
                                <i class="fas fa-calendar"></i>
                                Data
                            </label>
                            <input type="date" id="data_execucao" required>
                        </div>

                        <div class="form-group">
                            <label for="hora_inicio">
                                <i class="fas fa-play"></i>
                                Início
                            </label>
                            <input type="time" id="hora_inicio" required>
                        </div>

                        <div class="form-group">
                            <label for="hora_fim">
                                <i class="fas fa-stop"></i>
                                Fim
                            </label>
                            <input type="time" id="hora_fim" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancelar" id="btn-cancelar">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i>
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Confirmação de Exclusão -->
        <div class="confirm-modal" id="confirm-modal">
            <div class="confirm-modal-content">
                <div class="confirm-modal-header">
                    <h3>
                        <i class="fas fa-exclamation-triangle"></i>
                        Confirmar Exclusão
                    </h3>
                    <button class="confirm-modal-close">&times;</button>
                </div>
                <div class="confirm-modal-body">
                    <p>Tem certeza que deseja excluir esta atividade?</p>
                    <p class="atividade-info" id="atividade-info-exclusao">
                        Esta ação não poderá ser desfeita.
                    </p>
                </div>
                <div class="confirm-modal-footer">
                    <button class="btn-cancelar-exclusao" id="btn-cancelar-exclusao">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-confirmar-exclusao" id="btn-confirmar-exclusao">
                        <i class="fas fa-trash"></i>
                        Confirmar Exclusão
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <div class="filtros-grid">
                <div class="filtro-group">
                    <label for="filtro-data-inicio">
                        <i class="fas fa-calendar-alt"></i>
                        Data Início
                    </label>
                    <input type="date" id="filtro-data-inicio" placeholder="Data inicial">
                </div>
                <div class="filtro-group">
                    <label for="filtro-data-fim">
                        <i class="fas fa-calendar-check"></i>
                        Data Fim
                    </label>
                    <input type="date" id="filtro-data-fim" placeholder="Data final">
                </div>
                <div class="filtro-group">
                    <label for="filtro-cliente">
                        <i class="fas fa-building"></i>
                        Cliente
                    </label>
                    <input type="text" id="filtro-cliente" placeholder="Buscar cliente..." autocomplete="off">
                </div>
                <div class="filtro-group">
                    <button class="btn-limpar" id="btn-limpar-filtros">
                        <i class="fas fa-times"></i>
                        Limpar Filtros
                    </button>
                </div>
            </div>
            <div class="filtros-hint">
                <i class="fas fa-info-circle"></i>
                <span>Os filtros são aplicados automaticamente enquanto você digita</span>
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
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="atividades-tbody">
                    <tr>
                        <td colspan="7" class="loading-cell">
                            <div class="loading-spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="pagination" id="pagination"></div>
    </main>

    <script src="js/atividades.js"></script>
</body>
</html>