<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$usuario_id = $_SESSION['usuario_id'];

// GET - Buscar atividade específica por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $query = "SELECT id, nome_atividade, nome_cliente, data_execucao, 
                         DATE_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
                         DATE_FORMAT(hora_fim, '%H:%i') as hora_fim,
                         observacoes
                  FROM atividades 
                  WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'atividade' => $stmt->fetch(PDO::FETCH_ASSOC)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Atividade não encontrada']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar atividade: ' . $e->getMessage()]);
    }
    exit;
}

// POST - Criar/Atualizar atividade
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    // Validar campos obrigatórios
    $campos_obrigatorios = ['nome_atividade', 'nome_cliente', 'data_execucao', 'hora_inicio', 'hora_fim'];
    foreach ($campos_obrigatorios as $campo) {
        if (empty($data[$campo])) {
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
            exit;
        }
    }

    // Validar horas
    if ($data['hora_inicio'] >= $data['hora_fim']) {
        echo json_encode(['success' => false, 'message' => 'Hora de início deve ser menor que hora de fim']);
        exit;
    }

    // Validar data
    if (strtotime($data['data_execucao']) > strtotime(date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'Data não pode ser futura']);
        exit;
    }

    try {
        if (empty($data['id'])) {
            // Inserir nova atividade
            $query = "INSERT INTO atividades (usuario_id, nome_atividade, nome_cliente, data_execucao, hora_inicio, hora_fim, observacoes) 
                      VALUES (:usuario_id, :nome_atividade, :nome_cliente, :data_execucao, :hora_inicio, :hora_fim, :observacoes)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
        } else {
            // Verificar se a atividade pertence ao usuário
            $checkQuery = "SELECT id FROM atividades WHERE id = :id AND usuario_id = :usuario_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $data['id']);
            $checkStmt->bindParam(':usuario_id', $usuario_id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Atividade não encontrada']);
                exit;
            }
            
            // Atualizar atividade existente
            $query = "UPDATE atividades SET 
                      nome_atividade = :nome_atividade,
                      nome_cliente = :nome_cliente,
                      data_execucao = :data_execucao,
                      hora_inicio = :hora_inicio,
                      hora_fim = :hora_fim,
                      observacoes = :observacoes
                      WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':usuario_id', $usuario_id);
        }

        $stmt->bindParam(':nome_atividade', $data['nome_atividade']);
        $stmt->bindParam(':nome_cliente', $data['nome_cliente']);
        $stmt->bindParam(':data_execucao', $data['data_execucao']);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fim', $data['hora_fim']);
        $observacoes = isset($data['observacoes']) ? $data['observacoes'] : null;
        $stmt->bindParam(':observacoes', $observacoes);

        if ($stmt->execute()) {
            $mensagem = empty($data['id']) ? 'Atividade cadastrada com sucesso' : 'Atividade atualizada com sucesso';
            echo json_encode(['success' => true, 'message' => $mensagem]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar atividade']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE - Excluir atividade
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
        exit;
    }

    try {
        // Verificar se a atividade pertence ao usuário
        $checkQuery = "SELECT nome_atividade FROM atividades WHERE id = :id AND usuario_id = :usuario_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $data['id']);
        $checkStmt->bindParam(':usuario_id', $usuario_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Atividade não encontrada']);
            exit;
        }
        
        $query = "DELETE FROM atividades WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Atividade excluída com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir atividade']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
    }
    exit;
}

// GET - Listar atividades com paginação e filtros
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'data_inicio';
        
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $where = ["a.usuario_id = :usuario_id"];
        $params = [':usuario_id' => $usuario_id];

        // Aplicar filtros
        // Filtro de data (se não informado, mostra apenas o dia atual)
        if (!empty($_GET['data'])) {
            $where[] = "a.data_execucao = :data";
            $params[':data'] = $_GET['data'];
        } else {
            // Padrão: mostrar apenas atividades do dia atual
            $data_atual = date('Y-m-d');
            $where[] = "a.data_execucao = :data_atual";
            $params[':data_atual'] = $data_atual;
        }
        
        // Filtro de atividade
        if (!empty($_GET['atividade'])) {
            $where[] = "a.nome_atividade LIKE :atividade";
            $params[':atividade'] = '%' . $_GET['atividade'] . '%';
        }
        
        // Filtro de cliente
        if (!empty($_GET['cliente'])) {
            $where[] = "a.nome_cliente LIKE :cliente";
            $params[':cliente'] = '%' . $_GET['cliente'] . '%';
        }

        $where_clause = implode(' AND ', $where);

        // ORDENAÇÃO
        $order_clause = "";
        
        switch($ordenacao) {
            case 'atividade':
                $order_clause = "ORDER BY a.nome_atividade ASC";
                break;
            case 'cliente':
                $order_clause = "ORDER BY a.nome_cliente ASC";
                break;
            case 'data':
                $order_clause = "ORDER BY a.data_execucao ASC";
                break;
            case 'hora_inicio':
                $order_clause = "ORDER BY a.data_execucao ASC, a.hora_inicio ASC";
                break;
            case 'hora_fim':
                $order_clause = "ORDER BY a.data_execucao ASC, a.hora_fim DESC";
                break;
            case 'data_inicio':
            default:
                $order_clause = "ORDER BY a.data_execucao ASC, a.hora_inicio ASC";
                break;
        }

        // Contar total de registros
        $count_query = "SELECT COUNT(*) as total FROM atividades a WHERE $where_clause";
        $count_stmt = $db->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_records / $limit);

        // Calcular total de duração
        $total_query = "SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, a.hora_inicio, a.hora_fim)), 0) as total_minutos
                       FROM atividades a
                       WHERE $where_clause";
        $total_stmt = $db->prepare($total_query);
        foreach ($params as $key => $value) {
            $total_stmt->bindValue($key, $value);
        }
        $total_stmt->execute();
        $total_duracao = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_minutos'];

        // Buscar atividades
        $query = "SELECT a.id, a.nome_atividade, a.nome_cliente, a.data_execucao, 
                         DATE_FORMAT(a.hora_inicio, '%H:%i') as hora_inicio,
                         DATE_FORMAT(a.hora_fim, '%H:%i') as hora_fim,
                         TIMESTAMPDIFF(MINUTE, a.hora_inicio, a.hora_fim) as duracao_minutos
                  FROM atividades a
                  WHERE $where_clause 
                  $order_clause
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'atividades' => $atividades,
            'total_duracao_minutos' => (int)$total_duracao,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'per_page' => $limit
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao carregar atividades: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>