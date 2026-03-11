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

// POST - Alterar tema
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['theme'])) {
        try {
            // Validar se o tema é válido
            $tema = $data['theme'] === 'escuro' ? 'escuro' : 'claro';
            
            $query = "UPDATE usuarios SET tema_preferido = :tema WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tema', $tema);
            $stmt->bindParam(':id', $usuario_id);
            
            if ($stmt->execute()) {
                $_SESSION['tema'] = $tema;
                echo json_encode(['success' => true, 'theme' => $tema]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tema']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tema não especificado']);
    }
    exit;
}

// GET - Buscar dados do dashboard
try {
    $stats = [];
    
    // Atividades hoje
    $query = "SELECT COUNT(*) as total FROM atividades 
              WHERE usuario_id = :id AND data_execucao = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $stats['activities_today'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Horas hoje
    $query = "SELECT SUM(TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fim)) as minutos 
              FROM atividades 
              WHERE usuario_id = :id AND data_execucao = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $minutos = $stmt->fetch(PDO::FETCH_ASSOC)['minutos'];
    $stats['hours_today'] = $minutos ? round($minutos / 60, 2) : 0;
    
    // Horas na semana
    $query = "SELECT SUM(TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fim)) as minutos 
              FROM atividades 
              WHERE usuario_id = :id 
              AND YEARWEEK(data_execucao, 1) = YEARWEEK(CURDATE(), 1)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $minutos = $stmt->fetch(PDO::FETCH_ASSOC)['minutos'];
    $stats['hours_week'] = $minutos ? round($minutos / 60, 2) : 0;
    
    // Horas no mês
    $query = "SELECT SUM(TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fim)) as minutos 
              FROM atividades 
              WHERE usuario_id = :id 
              AND MONTH(data_execucao) = MONTH(CURDATE()) 
              AND YEAR(data_execucao) = YEAR(CURDATE())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $minutos = $stmt->fetch(PDO::FETCH_ASSOC)['minutos'];
    $stats['hours_month'] = $minutos ? round($minutos / 60, 2) : 0;
    
    // Dados para gráficos (últimos 7 dias)
    $chartData = [
        'labels' => [],
        'activities' => [],
        'hours' => []
    ];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chartData['labels'][] = date('d/m', strtotime($date));
        
        // Atividades do dia
        $query = "SELECT COUNT(*) as total FROM atividades 
                  WHERE usuario_id = :id AND data_execucao = :data";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $usuario_id);
        $stmt->bindParam(':data', $date);
        $stmt->execute();
        $chartData['activities'][] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Horas do dia
        $query = "SELECT SUM(TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fim)) as minutos 
                  FROM atividades 
                  WHERE usuario_id = :id AND data_execucao = :data";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $usuario_id);
        $stmt->bindParam(':data', $date);
        $stmt->execute();
        $minutos = $stmt->fetch(PDO::FETCH_ASSOC)['minutos'];
        $chartData['hours'][] = $minutos ? round($minutos / 60, 2) : 0;
    }
    
    // Atividades recentes
    $query = "SELECT id, nome_atividade, nome_cliente, data_execucao, 
              DATE_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
              DATE_FORMAT(hora_fim, '%H:%i') as hora_fim
              FROM atividades 
              WHERE usuario_id = :id 
              ORDER BY data_execucao DESC, hora_inicio DESC 
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'charts' => $chartData,
        'recent' => $recent
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao carregar dados: ' . $e->getMessage()
    ]);
}
?>