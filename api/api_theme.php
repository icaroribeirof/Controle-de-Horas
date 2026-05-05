<?php
require_once dirname(__DIR__) . '/db_connect.php';

header('Content-Type: application/json');

// Verificar se usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['theme'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tema não fornecido']);
        exit;
    }

    $theme = $input['theme'];
    
    // Validar tema
    if (!in_array($theme, ['claro', 'escuro'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tema inválido']);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE usuarios SET tema_preferido = :theme WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':theme', $theme);
        $stmt->bindParam(':id', $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tema atualizado', 'theme' => $theme]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tema']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
