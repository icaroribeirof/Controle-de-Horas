<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $query = "SELECT id, nome, email, senha, tema_preferido FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($data['password'], $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tema'] = $usuario['tema_preferido'];
            
            echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}
?>