<?php
session_start();

require __DIR__ . '/../vendor/autoload.php'; // Incluir o autoloader do Composer

require_once '../config/database.php';
require_once '../models/Model.php';
require_once '../models/Pedido.php';
require_once '../models/Produto.php';
require_once '../utils/Utils.php';

header('Content-Type: application/json');

$pedido = new Pedido();
$produto = new Produto();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Criar novo pedido
    $dados_pedido = [
        'cliente_nome' => $_POST['cliente_nome'] ?? '',
        'cliente_email' => $_POST['cliente_email'] ?? '',
        'cliente_telefone' => $_POST['cliente_telefone'] ?? null,
        'endereco_cep' => $_POST['cep'] ?? '',
        'endereco_logradouro' => $_POST['logradouro'] ?? '',
        'endereco_numero' => $_POST['numero'] ?? '',
        'endereco_complemento' => $_POST['complemento'] ?? null,
        'endereco_bairro' => $_POST['bairro'] ?? '',
        'endereco_cidade' => $_POST['localidade'] ?? '',
        'endereco_estado' => $_POST['uf'] ?? '',
        'subtotal' => $_POST['subtotal'] ?? 0,
        'frete' => $_POST['frete'] ?? 0,
        'desconto' => $_POST['desconto'] ?? 0,
        'total' => $_POST['total'] ?? 0,
        'status' => 'pendente'
    ];
    
    // Validar dados (ajustar validação conforme os novos campos)
    // if (empty($dados_pedido['cliente_nome']) || empty($dados_pedido['cliente_email']) || 
    //     empty($dados_pedido['endereco_cep']) || empty($dados_pedido['endereco_logradouro']) || 
    //     empty($dados_pedido['endereco_numero']) || empty($dados_pedido['endereco_bairro']) || 
    //     empty($dados_pedido['endereco_cidade']) || empty($dados_pedido['endereco_estado'])) {
    //     $response['message'] = 'Todos os campos obrigatórios (Nome, Email, CEP, Endereço, Número, Bairro, Cidade, Estado) devem ser preenchidos';
    // } else {
        // Preparar itens do pedido
        $itens_pedido = [];
        if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
            foreach ($_SESSION['carrinho'] as $item) {
                $produto_info = $produto->find($item['id_produto']);
                if ($produto_info) {
                    // Encontrar a variação correta para obter o preço e verificar estoque
                    $variacao_encontrada = null;
                    foreach ($produto_info['variacoes'] as $variacao) {
                        if ($variacao['nome'] === $item['variacao']) {
                            $variacao_encontrada = $variacao;
                            break;
                        }
                    }

                    if ($variacao_encontrada) {
                         // Verificar se há estoque suficiente para esta variação
                        if ($variacao_encontrada['quantidade'] >= $item['quantidade']) {
                            $itens_pedido[] = [
                                'id_produto' => $item['id_produto'],
                                'variacao' => $item['variacao'],
                                'quantidade' => $item['quantidade'],
                                'preco_unitario' => $produto_info['preco'] // Usar o preço base do produto ou da variação se disponível
                            ];
                        } else {
                             $response['message'] = 'Estoque insuficiente para o produto ' . $produto_info['nome'] . ' (' . $item['variacao'] . ').';
                             echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                             exit; // Interrompe o processamento se o estoque for insuficiente
                        }
                    } else {
                        $response['message'] = 'Variação ' . $item['variacao'] . ' não encontrada para o produto ' . $produto_info['nome'] . '.';
                        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        exit; // Interrompe o processamento se a variação não for encontrada
                    }
                } else {
                    $response['message'] = 'Produto com ID ' . $item['id_produto'] . ' não encontrado.';
                    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    exit; // Interrompe o processamento se o produto não for encontrado
                }
            }
        } else {
             $response['message'] = 'Carrinho vazio ou inválido';
             echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
             exit;
        }
        
        if (empty($itens_pedido)) {
            // Isso só aconteceria se todos os itens do carrinho tivessem estoque insuficiente ou fossem inválidos, o que já é tratado acima
             $response['message'] = 'Não foi possível adicionar itens válidos ao pedido.';
        } else {
            try {
                // Criar pedido
                $id_pedido = $pedido->criarPedido($dados_pedido, $itens_pedido);
                
                if ($id_pedido) {
                    // Enviar e-mail
                    $pedido_info = $pedido->getPedidoComItens($id_pedido);
                    // Ensure $pedido_info is not empty before accessing $pedido_info[0]
                    $template = !empty($pedido_info) ? Utils::gerarTemplateEmailPedido($pedido_info[0], $pedido_info) : '';
                    
                    // Check if template was generated successfully before sending email
                    if (!empty($template) && Utils::enviarEmail(
                        $dados_pedido['cliente_email'],
                        'Pedido Confirmado - #' . $id_pedido,
                        $template
                    )) {
                        // Limpar carrinho
                        $_SESSION['carrinho'] = [];
                        if (isset($_SESSION['cupom'])) {
                            unset($_SESSION['cupom']);
                        }
                        
                        $response['success'] = true;
                        $response['message'] = 'Pedido criado com sucesso';
                        $response['id_pedido'] = $id_pedido;
                    } else if (empty($template)) {
                         $response['message'] = 'Pedido criado, mas falha ao gerar template de e-mail.';
                         $response['success'] = true; // Considerar sucesso pois o pedido foi criado
                         $response['id_pedido'] = $id_pedido;
                    } else {
                        $response['message'] = 'Pedido criado, mas falha ao enviar e-mail';
                        $response['success'] = true; // Considerar sucesso pois o pedido foi criado
                        $response['id_pedido'] = $id_pedido;
                    }
                } else {
                    $response['message'] = 'Erro ao criar pedido';
                }
            } catch (Exception $e) {
                $response['message'] = 'Erro: ' . $e->getMessage();
            }
        }
    // } // End of temporary commented-out validation else block
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'buscar':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $pedido_info = $pedido->getPedidoComItens($id);
                    if ($pedido_info) {
                        $response['success'] = true;
                        $response['data'] = $pedido_info[0];
                        $response['data']['itens'] = $pedido_info;
                    } else {
                        $response['message'] = 'Pedido não encontrado';
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Erro: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'ID inválido';
            }
            break;
            
        case 'atualizar_status':
            $id_pedido = $_GET['id'] ?? 0;
            $status = $_GET['status'] ?? '';
            
            if ($id_pedido && $status) {
                try {
                    if ($pedido->atualizarStatus($id_pedido, $status)) {
                        $response['success'] = true;
                        $response['message'] = 'Status atualizado com sucesso';
                    } else {
                        $response['message'] = 'Erro ao atualizar status';
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Erro: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Dados inválidos';
            }
            break;
            
        default:
            $response['message'] = 'Ação inválida';
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 