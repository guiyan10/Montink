<?php
session_start();
require_once '../config/database.php';
require_once '../models/Model.php';
require_once '../models/Cupom.php';

header('Content-Type: application/json');

$cupom = new Cupom();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'validar':
            $codigo = $_POST['codigo'] ?? '';
            $subtotal = floatval($_POST['subtotal'] ?? 0);
            
            if (empty($codigo)) {
                $response['message'] = 'Código do cupom é obrigatório';
                break;
            }
            
            if ($subtotal <= 0) {
                $response['message'] = 'Valor do pedido inválido';
                break;
            }
            
            try {
                $cupom_info = $cupom->validarCupom($codigo, $subtotal);
                
                if ($cupom_info) {
                    $_SESSION['cupom'] = $codigo;
                    $response['success'] = true;
                    $response['message'] = 'Cupom aplicado com sucesso';
                    $response['desconto'] = $cupom_info['valor_desconto'];
                    $response['cupom'] = $cupom_info;
                } else {
                    $response['message'] = 'Cupom inválido, expirado ou valor mínimo não atingido';
                }
            } catch (Exception $e) {
                error_log("Erro ao validar cupom: " . $e->getMessage());
                $response['message'] = 'Erro ao validar cupom';
            }
            break;
            
        case 'criar':
            $dados = [
                'codigo' => $_POST['codigo'] ?? '',
                'valor_desconto' => $_POST['valor_desconto'] ?? 0,
                'valor_minimo' => $_POST['valor_minimo'] ?? 0,
                'validade' => $_POST['validade'] ?? ''
            ];
            
            try {
                if ($cupom->criarCupom($dados)) {
                    $response['success'] = true;
                    $response['message'] = 'Cupom criado com sucesso';
                } else {
                    $response['message'] = 'Erro ao criar cupom';
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
            }
            break;
            
        default:
            $response['message'] = 'Ação inválida';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'excluir') {
        $id = $_GET['id'] ?? 0;
        
        if ($id) {
            try {
                if ($cupom->delete($id)) {
                    $response['success'] = true;
                    $response['message'] = 'Cupom excluído com sucesso';
                } else {
                    $response['message'] = 'Erro ao excluir cupom';
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'ID inválido';
        }
    }
}

echo json_encode($response); 