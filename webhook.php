<?php
require_once 'config/database.php';
require_once 'models/Model.php';
require_once 'models/Pedido.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obtém o corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);

// Valida os dados recebidos
if (!isset($data['pedido_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

try {
    $pedido = new Pedido();
    
    // Se o status for "cancelado", remove o pedido
    if ($data['status'] === 'cancelado') {
        if ($pedido->delete($data['pedido_id'])) {
            echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido não encontrado']);
        }
    } else {
        // Atualiza o status do pedido
        if ($pedido->atualizarStatus($data['pedido_id'], $data['status'])) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido não encontrado']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
} 