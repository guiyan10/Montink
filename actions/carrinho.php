<?php
session_start();
require_once '../config/database.php';
require_once '../models/Model.php';
require_once '../models/Produto.php';

header('Content-Type: application/json');

$produto = new Produto();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'adicionar':
            $id_produto = $_POST['id_produto'] ?? 0;
            $variacao = $_POST['variacao'] ?? '';
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            
            if ($id_produto && $variacao && $quantidade > 0) {
                // Verificar estoque
                if ($produto->verificarEstoque($id_produto, $variacao, $quantidade)) {
                    // Adicionar ao carrinho
                    if (!isset($_SESSION['carrinho'])) {
                        $_SESSION['carrinho'] = [];
                    }
                    
                    $item_key = $id_produto . '_' . $variacao;
                    
                    if (isset($_SESSION['carrinho'][$item_key])) {
                        $_SESSION['carrinho'][$item_key]['quantidade'] += $quantidade;
                    } else {
                        $_SESSION['carrinho'][$item_key] = [
                            'id_produto' => $id_produto,
                            'variacao' => $variacao,
                            'quantidade' => $quantidade
                        ];
                    }
                    
                    $response['success'] = true;
                    $response['message'] = 'Produto adicionado ao carrinho';
                } else {
                    $response['message'] = 'Quantidade indisponível em estoque';
                }
            } else {
                $response['message'] = 'Dados inválidos';
            }
            break;
            
        case 'atualizar':
            $id_produto = $_POST['id'] ?? 0;
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            
            if ($id_produto) {
                foreach ($_SESSION['carrinho'] as $key => $item) {
                    if ($item['id_produto'] == $id_produto) {
                        if ($quantidade > 0) {
                            // Verificar estoque
                            if ($produto->verificarEstoque($id_produto, $item['variacao'], $quantidade)) {
                                $_SESSION['carrinho'][$key]['quantidade'] = $quantidade;
                                $response['success'] = true;
                                $response['message'] = 'Quantidade atualizada';
                            } else {
                                $response['message'] = 'Quantidade indisponível em estoque';
                            }
                        } else {
                            // Remover item
                            unset($_SESSION['carrinho'][$key]);
                            $response['success'] = true;
                            $response['message'] = 'Item removido do carrinho';
                        }
                        break;
                    }
                }
            }
            break;
            
        default:
            $response['message'] = 'Ação inválida';
    }
}

echo json_encode($response); 