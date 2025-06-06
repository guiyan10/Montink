<?php
session_start();
require_once '../config/database.php';
require_once '../models/Model.php';
require_once '../models/Produto.php';

error_log("actions/produto.php accessed");

header('Content-Type: application/json');

$produto = new Produto();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    error_log("POST request, action: " . $action);
    
    switch ($action) {
        case 'criar':
            error_log("Action: criar");
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'preco' => $_POST['preco'] ?? 0,
                'variacoes' => $_POST['variacoes'] ?? []
            ];
            error_log("Dados recebidos for criar: " . print_r($dados, true));
            
            try {
                if ($produto->criarProduto($dados)) {
                    $response['success'] = true;
                    $response['message'] = 'Produto criado com sucesso';
                    error_log("Produto criado com sucesso");
                } else {
                    $response['message'] = 'Erro ao criar produto';
                    error_log("Erro ao criar produto na model");
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
                error_log("Exception ao criar produto: " . $e->getMessage());
            }
            break;
            
        case 'atualizar':
            error_log("Action: atualizar");
            $id = $_POST['id'] ?? 0;
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'preco' => $_POST['preco'] ?? 0,
                'variacoes' => $_POST['variacoes'] ?? []
            ];
            error_log("Dados recebidos for atualizar (ID: " . $id . "): " . print_r($dados, true));
            
            if ($id) {
                try {
                    if ($produto->atualizarProduto($id, $dados)) {
                        $response['success'] = true;
                        $response['message'] = 'Produto atualizado com sucesso';
                        error_log("Produto atualizado com sucesso (ID: " . $id . ")");
                    } else {
                        $response['message'] = 'Erro ao atualizar produto';
                        error_log("Erro ao atualizar produto na model (ID: " . $id . ")");
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Erro: ' . $e->getMessage();
                    error_log("Exception ao atualizar produto (ID: " . $id . "): " . $e->getMessage());
                }
            } else {
                $response['message'] = 'ID inválido';
                error_log("ID inválido para atualizar produto");
            }
            break;
            
        case 'atualizar_estoque':
            error_log("Action: atualizar_estoque");
            $id = $_POST['id'] ?? 0;
            $variacao = $_POST['variacao'] ?? '';
            $quantidade = $_POST['quantidade'] ?? 0;
            error_log("Dados recebidos for atualizar_estoque (ID: " . $id . ", Variacao: " . $variacao . ", Quantidade: " . $quantidade . ")");
            
            if ($id && $variacao) {
                try {
                    if ($produto->atualizarEstoque($id, $variacao, $quantidade)) {
                        $response['success'] = true;
                        $response['message'] = 'Estoque atualizado com sucesso';
                        error_log("Estoque atualizado com sucesso (ID: " . $id . ")");
                    } else {
                        $response['message'] = 'Erro ao atualizar estoque';
                        error_log("Erro ao atualizar estoque na model (ID: " . $id . ")");
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Erro: ' . $e->getMessage();
                    error_log("Exception ao atualizar estoque (ID: " . $id . "): " . $e->getMessage());
                }
            } else {
                $response['message'] = 'Dados inválidos';
                error_log("Dados inválidos para atualizar estoque");
            }
            break;
            
        default:
            $response['message'] = 'Ação inválida';
            error_log("Invalid action: " . $action);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    error_log("GET request, action: " . $action);
    
    if ($action === 'excluir') {
        error_log("Action: excluir");
        $id = $_GET['id'] ?? 0;
        error_log("ID to delete: " . $id);
        
        if ($id) {
            try {
                if ($produto->delete($id)) {
                    $response['success'] = true;
                    $response['message'] = 'Produto excluído com sucesso';
                    error_log("Produto excluído com sucesso (ID: " . $id . ")");
                } else {
                    $response['message'] = 'Erro ao excluir produto';
                    error_log("Erro ao excluir produto na model (ID: " . $id . ")");
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
                error_log("Exception ao excluir produto (ID: " . $id . "): " . $e->getMessage());
            }
        } else {
            $response['message'] = 'ID inválido';
            error_log("ID inválido para excluir produto");
        }
    } elseif ($action === 'buscar') {
        error_log("Action: buscar");
        $id = $_GET['id'] ?? 0;
        error_log("ID to fetch: " . $id);
        
        if ($id) {
            try {
                $produto_info = $produto->find($id);
                if ($produto_info) {
                    $response['success'] = true;
                    $response['data'] = $produto_info;
                    error_log("Produto encontrado (ID: " . $id . "): " . print_r($produto_info, true));
                } else {
                    $response['message'] = 'Produto não encontrado';
                    error_log("Produto não encontrado (ID: " . $id . ")");
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
                error_log("Exception ao buscar produto (ID: " . $id . "): " . $e->getMessage());
            }
        } else {
            $response['message'] = 'ID inválido';
            error_log("ID inválido para buscar produto");
        }
    }
}

echo json_encode($response); 