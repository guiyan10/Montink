<?php
require_once 'config/config.php';
require_once 'models/Pedido.php';

// Set content type to JSON
header('Content-Type: application/json');

// Verify webhook signature
function verifyWebhookSignature($payload, $signature) {
    $expectedSignature = hash_hmac('sha256', $payload, WEBHOOK_SECRET);
    return hash_equals($expectedSignature, $signature);
}

// Get raw POST data
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verify webhook signature
if (!verifyWebhookSignature($payload, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

try {
    // Parse JSON payload
    $data = json_decode($payload, true);
    
    if (!$data || !isset($data['id']) || !isset($data['status'])) {
        throw new Exception('Invalid payload format');
    }
    
    $pedido = new Pedido();
    
    // Handle order status update
    if ($data['status'] === 'cancelado') {
        // Delete order if status is cancelled
        if ($pedido->delete($data['id'])) {
            echo json_encode(['message' => 'Order deleted successfully']);
        } else {
            throw new Exception('Order not found');
        }
    } else {
        // Update order status
        if ($pedido->atualizarStatus($data['id'], $data['status'])) {
            echo json_encode(['message' => 'Order status updated successfully']);
        } else {
            throw new Exception('Order not found');
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 