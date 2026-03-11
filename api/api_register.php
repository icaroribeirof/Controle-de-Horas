<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    // Verificar se email já existe
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado']);
        exit;
    }

    // Criar novo usuário
    $senha_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $query = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nome', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':senha', $senha_hash);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}
?>