<?php
$produto = new Produto();
$pedido = new Pedido();
$cupom = new Cupom();

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Handle potential actions from form submissions (before outputting HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action'] ?? '';
    
    switch ($action_type) {
        case 'criar':
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'preco' => $_POST['preco'] ?? 0,
                'variacoes' => $_POST['variacoes'] ?? []
            ];
            try {
                if ($produto->criarProduto($dados)) {
                    $_SESSION['alert'] = ['message' => 'Produto criado com sucesso', 'type' => 'success'];
                } else {
                    $_SESSION['alert'] = ['message' => $produto->getMessage() ?: 'Erro ao criar produto', 'type' => 'danger'];
                }
            } catch (Exception $e) {
                $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
            }
            header('Location: index.php?page=admin&action=produtos');
            exit();
        case 'atualizar':
             $id = $_POST['id'] ?? 0;
             $dados = [
                 'nome' => $_POST['nome'] ?? '',
                 'descricao' => $_POST['descricao'] ?? '',
                 'preco' => $_POST['preco'] ?? 0,
                 'variacoes' => $_POST['variacoes'] ?? []
             ];
            try {
                if ($produto->atualizarProduto($id, $dados)) {
                    $_SESSION['alert'] = ['message' => 'Produto atualizado com sucesso', 'type' => 'success'];
                } else {
                    $_SESSION['alert'] = ['message' => $produto->getMessage() ?: 'Erro ao atualizar produto', 'type' => 'danger'];
                }
            } catch (Exception $e) {
                $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
            }
            header('Location: index.php?page=admin&action=produtos');
            exit();
        case 'criar_cupom':
             $dados = [
                 'codigo' => $_POST['codigo'] ?? '',
                 'valor_desconto' => $_POST['valor_desconto'] ?? 0,
                 'valor_minimo' => $_POST['valor_minimo'] ?? 0,
                 'validade' => $_POST['validade'] ?? ''
             ];
            try {
                if ($cupom->criarCupom($dados)) {
                    $_SESSION['alert'] = ['message' => 'Cupom criado com sucesso', 'type' => 'success'];
                } else {
                    $_SESSION['alert'] = ['message' => $cupom->getMessage() ?: 'Erro ao criar cupom', 'type' => 'danger'];
                }
            } catch (Exception $e) {
                $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
            }
             header('Location: index.php?page=admin&action=cupons');
             exit();

    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
     $action_type = $_GET['action_type'] ?? '';

     switch ($action_type) {
         case 'excluir_produto':
             $id = $_GET['id'] ?? 0;
             try {
                 if ($produto->delete($id)) {
                     $_SESSION['alert'] = ['message' => $produto->getMessage() ?: 'Produto excluído com sucesso', 'type' => 'success'];
                 } else {
                      $_SESSION['alert'] = ['message' => $produto->getMessage() ?: 'Erro ao excluir produto', 'type' => 'danger'];
                 }
             } catch (Exception $e) {
                 $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
             }
             header('Location: index.php?page=admin&action=produtos');
             exit();
         case 'excluir_cupom':
             $id = $_GET['id'] ?? 0;
              try {
                  if ($cupom->delete($id)) {
                      $_SESSION['alert'] = ['message' => $cupom->getMessage() ?: 'Cupom excluído com sucesso', 'type' => 'success'];
                  } else {
                       $_SESSION['alert'] = ['message' => $cupom->getMessage() ?: 'Erro ao excluir cupom', 'type' => 'danger'];
                  }
              } catch (Exception $e) {
                  $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
              }
             header('Location: index.php?page=admin&action=cupons');
             exit();

         case 'atualizar_status_pedido':
              $id = $_GET['id'] ?? 0;
              $status = $_GET['status'] ?? '';
              try {
                  if ($pedido->atualizarStatus($id, $status)) {
                      $_SESSION['alert'] = ['message' => $pedido->getMessage() ?: 'Status do pedido atualizado com sucesso', 'type' => 'success'];
                  } else {
                       $_SESSION['alert'] = ['message' => $pedido->getMessage() ?: 'Erro ao atualizar status do pedido', 'type' => 'danger'];
                  }
              } catch (Exception $e) {
                  $_SESSION['alert'] = ['message' => 'Erro: ' . $e->getMessage(), 'type' => 'danger'];
              }
             header('Location: index.php?page=admin&action=pedidos');
             exit();

     }
}

?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Painel Administrativo</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="index.php?page=admin&action=dashboard" 
               class="list-group-item list-group-item-action <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="index.php?page=admin&action=produtos" 
               class="list-group-item list-group-item-action <?php echo $action === 'produtos' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Produtos
            </a>
            <a href="index.php?page=admin&action=pedidos" 
               class="list-group-item list-group-item-action <?php echo $action === 'pedidos' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a>
            <a href="index.php?page=admin&action=cupons" 
               class="list-group-item list-group-item-action <?php echo $action === 'cupons' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> Cupons
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <?php
         // Display alerts if they exist in the session
         if (isset($_SESSION['alert'])) {
             $alert = $_SESSION['alert'];
             echo '<div class=\"alert alert-' . $alert['type'] . ' alert-dismissible fade show\" role=\"alert\">' . $alert['message'] . '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>';
             unset($_SESSION['alert']); // Clear the alert after displaying
         }

        switch ($action) {
            case 'produtos':
                $produtos = $produto->findAll();
                ?>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title">Produtos</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                                <i class="fas fa-plus"></i> Novo Produto
                            </button>
                        </div>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Preço</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $prod): ?>
                                    <tr>
                                        <td><?php echo $prod['id']; ?></td>
                                        <td><?php echo htmlspecialchars($prod['nome'] ?? ''); ?></td>
                                        <td><?php echo Utils::formatarPreco($prod['preco'] ?? 0); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="editarProduto(<?php echo $prod['id']; ?>)" data-bs-toggle="modal" data-bs-target="#modalProduto">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="index.php?page=admin&action_type=excluir_produto&id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                break;
                
            case 'pedidos':
                $pedidos = $pedido->findAll();
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pedidos</h5>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $ped): ?>
                                    <tr>
                                        <td><?php echo $ped['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ped['cliente_nome'] ?? ''); ?></td>
                                        <td><?php echo Utils::formatarPreco($ped['total'] ?? 0); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $ped['status'] === 'pendente' ? 'warning' : 
                                                    ($ped['status'] === 'aprovado' ? 'info' : 
                                                    ($ped['status'] === 'enviado' ? 'primary' : 
                                                    ($ped['status'] === 'entregue' ? 'success' : 'danger'))); 
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($ped['status']) ?? ''); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ped['data_criacao'] ?? '')) ?? ''); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="verPedido(<?php echo $ped['id']; ?>)" data-bs-toggle="modal" data-bs-target="#modalVerPedido">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="atualizarStatus(<?php echo $ped['id']; ?>)">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                break;
                
            case 'cupons':
                $cupons = $cupom->findAll();
                ?>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title">Cupons</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCupom">
                                <i class="fas fa-plus"></i> Novo Cupom
                            </button>
                        </div>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Valor</th>
                                    <th>Mínimo</th>
                                    <th>Validade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cupons as $cup): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cup['codigo'] ?? ''); ?></td>
                                        <td><?php echo Utils::formatarPreco($cup['valor_desconto'] ?? 0); ?></td>
                                        <td><?php echo Utils::formatarPreco($cup['valor_minimo'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($cup['validade'] ?? '')) ?? ''); ?></td>
                                        <td>
                                            <a href="index.php?page=admin&action_type=excluir_cupom&id=<?php echo $cup['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este cupom?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                break;
                
            default:
                // Dashboard
                $total_pedidos = count($pedido->findAll());
                $total_produtos = count($produto->findAll());
                $total_cupons = count($cupom->findAll());
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total de Pedidos</h5>
                                <h2><?php echo $total_pedidos; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total de Produtos</h5>
                                <h2><?php echo $total_produtos; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Cupons Ativos</h5>
                                <h2><?php echo $total_cupons; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                break;
        }
        ?>
    </div>
</div>

<!-- Modal Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formProduto" action="index.php?page=admin" method="POST">
                    <input type="hidden" name="action" value="criar">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <input type="number" class="form-control" id="preco" name="preco" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Variações</label>
                        <div id="variacoesProduto">
                            <div class="variacao-item row mb-2">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="variacoes[0][nome]" placeholder="Nome da Variação" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="variacoes[0][quantidade]" placeholder="Quantidade" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remover-variacao"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="addVariacaoProduto"><i class="fas fa-plus"></i> Adicionar Variação</button>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cupom -->
<div class="modal fade" id="modalCupom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Cupom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCupom" action="index.php?page=admin" method="POST">
                    <input type="hidden" name="action" value="criar_cupom">
                    
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valor_desconto" class="form-label">Valor de Desconto</label>
                        <input type="number" class="form-control" id="valor_desconto" name="valor_desconto" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valor_minimo" class="form-label">Valor Mínimo do Pedido</label>
                        <input type="number" class="form-control" id="valor_minimo" name="valor_minimo" 
                               step="0.01" min="0" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="validade" class="form-label">Validade</label>
                        <input type="date" class="form-control" id="validade" name="validade" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Cupom</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Pedido -->
<div class="modal fade" id="modalVerPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesPedido"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para exibir alerta
function showAlert(message, type) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <div>${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertPlaceholder.append(wrapper);
    
    // Remove o alerta após 5 segundos
    setTimeout(() => wrapper.remove(), 5000);
}

// Helper function to escape HTML entities for displaying data
function htmlspecialchars(str) {
    if (typeof str !== 'string') {
        return str; // Return non-string values as is
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>\umerable\'\"]/g, function(m) { return map[m]; });
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');

    const variacaoSelect = document.getElementById('variacao');
    const quantidadeInput = document.getElementById('quantidade');
    
    if (variacaoSelect && quantidadeInput) {
        variacaoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const quantidadeDisponivel = selectedOption.dataset.quantidade;
            
            quantidadeInput.max = quantidadeDisponivel;
            if (parseInt(quantidadeInput.value) > parseInt(quantidadeDisponivel)) {
                quantidadeInput.value = quantidadeDisponivel;
            }
        });
    }

    // --- Produtos --- //

    // Adicionar/Remover variação no formulário de produto
    const addVariacaoButton = document.getElementById('addVariacaoProduto');
    if (addVariacaoButton) {
        addVariacaoButton.addEventListener('click', function() {
             console.log('Add Variação button clicked');
            const variacoesDiv = document.getElementById('variacoesProduto');
            const index = variacoesDiv.querySelectorAll('.variacao-item').length;
            const newItem = `
                <div class=\"variacao-item row mb-2\">\n
                    <div class=\"col-md-6\">\n
                        <input type=\"text\" class=\"form-control\" name=\"variacoes[${index}][nome]\" placeholder=\"Nome da Variação\" required>\n
                    </div>\n
                    <div class=\"col-md-4\">\n
                        <input type=\"number\" class=\"form-control\" name=\"variacoes[${index}][quantidade]\" placeholder=\"Quantidade\" min=\"0\" required>\n
                    </div>\n
                    <div class=\"col-md-2\">\n
                        <button type=\"button\" class=\"btn btn-danger remover-variacao\"><i class=\"fas fa-times\"></i></button>\n
                    </div>\n
                </div>
            `;
            variacoesDiv.insertAdjacentHTML('beforeend', newItem);
        });
    }

    const variacoesProdutoDiv = document.getElementById('variacoesProduto');
    if (variacoesProdutoDiv) {
        variacoesProdutoDiv.addEventListener('click', function(e) {
            console.log('Variações div clicked', e.target);
            if (e.target.classList.contains('remover-variacao') || e.target.parentElement.classList.contains('remover-variacao')) {
                 console.log('Remover Variação button clicked');
                const itemToRemove = e.target.closest('.variacao-item');
                if (itemToRemove) {
                    itemToRemove.remove();
                }
            }
        });
    }

    // Tratar submissão do formulário de produto via AJAX
    const formProduto = document.getElementById('formProduto');
    if (formProduto) {
        console.log('formProduto element found');
        // formProduto.addEventListener('submit', function(e) {
        //     console.log('Product form submitted');
        //     e.preventDefault();
        //     const form = e.target;
        //     const formData = new FormData(form);
            
        //     fetch(form.action, {
        //         method: form.method,
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             showAlert(data.message, 'success');
        //             // Fechar modal e recarregar lista de produtos (opcional)
        //             const modal = bootstrap.Modal.getInstance(document.getElementById('modalProduto'));
        //             modal.hide();
        //             location.reload(); // Recarrega a página para ver a lista atualizada
        //         } else {
        //             showAlert(data.message, 'danger');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Erro:', error);
        //         showAlert('Ocorreu um erro ao processar a requisição.', 'danger');
        //     });
        // });
    } else {
         console.log('formProduto element NOT found');
    }

    // Função para editar produto (carregar dados no modal)
    function editarProduto(id) {
        // Reset form before populating for edit
        resetProductForm(); // Ensure form is reset first

        fetch(`actions/produto.php?action=buscar&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const produto = data.data;
                const modal = new bootstrap.Modal(document.getElementById('modalProduto'));
                const form = document.getElementById('formProduto');
                
                // Update modal title
                modal._element.querySelector('.modal-title').textContent = 'Editar Produto';

                // Set the action type to 'atualizar' and add the product ID
                form.querySelector('input[name="action"]').value = 'atualizar';
                let idInput = form.querySelector('input[name="id"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                idInput.value = produto.id;
                
                // Preenche os campos do formulário
                form.querySelector('input[name="nome"]').value = produto.nome;
                form.querySelector('input[name="preco"]').value = produto.preco;
                form.querySelector('textarea[name="descricao"]').value = produto.descricao;
                
                // Preenche as variações existentes
                const variacoesDiv = document.getElementById('variacoesProduto');
                variacoesDiv.innerHTML = '<h6>Variações de Estoque</h6>'; // Limpa variações antigas
                
                if (produto.variacoes && produto.variacoes.length > 0) {
                    produto.variacoes.forEach((variacao, index) => {
                        const newItem = `
                            <div class=\"variacao-item row mb-2\">\n
                                <div class=\"col-md-6\">\n
                                    <input type=\"text\" class=\"form-control\" name=\"variacoes[${index}][nome]\" placeholder=\"Nome da Variação\" value=\"${htmlspecialchars(variacao.nome)}\" required>\n
                                </div>\n
                                <div class=\"col-md-4\">\n
                                    <input type=\"number\" class=\"form-control\" name=\"variacoes[${index}][quantidade]\" placeholder=\"Quantidade\" value=\"${variacao.quantidade}\" min=\"0\" required>\n
                                </div>\n
                                <div class=\"col-md-2\">\n
                                    <button type=\"button\" class=\"btn btn-danger remover-variacao\"><i class=\"fas fa-times\"></i></button>\n
                                </div>\n
                            </div>
                        `;
                        variacoesDiv.insertAdjacentHTML('beforeend', newItem);
                    });
                } else {
                    // Add at least one variation field if none exist
                     addDefaultVariacaoField(variacoesDiv);
                }

                modal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Ocorreu um erro ao buscar dados do produto.', 'danger');
        });
    }

    // Helper function to add a default variation field
    function addDefaultVariacaoField(variacoesDiv) {
         const index = variacoesDiv.querySelectorAll('.variacao-item').length;
         const newItem = `
             <div class=\"variacao-item row mb-2\">\n
                 <div class=\"col-md-6\">\n
                     <input type=\"text\" class=\"form-control\" name=\"variacoes[${index}][nome]\" placeholder=\"Nome da Variação\" required>\n
                 </div>\n
                 <div class=\"col-md-4\">\n
                     <input type=\"number\" class=\"form-control\" name=\"variacoes[${index}][quantidade]\" placeholder=\"Quantidade\" min=\"0\" required>\n
                 </div>\n
                 <div class=\"col-md-2\">\n
                     <button type=\"button\" class=\"btn btn-danger remover-variacao\"><i class=\"fas fa-times\"></i></button>\n
                 </div>\n
             </div>
         `;
         variacoesDiv.insertAdjacentHTML('beforeend', newItem);
    }

    // Function to reset the product form for creation
    function resetProductForm() {
        const form = document.getElementById('formProduto');
        if (form) {
            form.reset();
            form.action = 'index.php?page=admin'; // Ensure correct action URL for standard submission
            form.querySelector('input[name="action"]').value = 'criar';
            // Remove hidden ID input if it exists
            const idInput = form.querySelector('input[name="id"]');
            if (idInput) {
                idInput.remove();
            }
            // Clear and add a default variation field
            const variacoesDiv = document.getElementById('variacoesProduto');
             if(variacoesDiv) { // Add a check if the element exists
                 variacoesDiv.innerHTML = '<h6>Variações de Estoque</h6>';
                 addDefaultVariacaoField(variacoesDiv);
             }

             // Reset modal title
            const modal = document.getElementById('modalProduto');
            if (modal) { // Add a check if modal exists
                 modal.querySelector('.modal-title').textContent = 'Novo Produto';
            }
        }
    }

    // Listen for the modal show event to reset the form
    const productModal = document.getElementById('modalProduto');
    if (productModal) {
        productModal.addEventListener('show.bs.modal', function (event) {
            // Check if the modal is being opened by the 'Novo Produto' button
            const relatedButton = event.relatedTarget;
            // Assuming your 'Novo Produto' button has the ID 'novoProdutoBtn'
            if (relatedButton && relatedButton.id === 'novoProdutoBtn') {
                 resetProductForm();
            } else { // If opened by edit button, reset then populate
                 resetProductForm(); // Start with a clean form
                 // The editProduct function will be called separately and will populate the form
            }
        });
    }

    // Add a click listener to the submit button for debugging
    const saveProductButton = document.querySelector('#modalProduto .modal-footer .btn-primary[type="submit"]');
    if (saveProductButton) {
        saveProductButton.addEventListener('click', function() {
            const form = document.getElementById('formProduto');
            if (form) {
                console.log('Debug Form Action:', form.action);
                const actionInput = form.querySelector('input[name="action"]');
                console.log('Debug Hidden Action Input Value:', actionInput ? actionInput.value : 'Not Found');
            }
        });
    }

    // Função para excluir produto
    function excluirProduto(id) {
        if (confirm('Tem certeza que deseja excluir este produto?')) {
            fetch(`actions/produto.php?action=excluir&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    location.reload(); // Recarrega a página para ver a lista atualizada
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Ocorreu um erro ao excluir o produto.', 'danger');
            });
        }
    }

    // --- Pedidos --- //

    // Função para ver detalhes do pedido (AJAX)
    function verPedido(id) {
        fetch(`actions/pedido.php?action=buscar&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pedido = data.data;
                let detalhesHtml = `
                    <p><strong>ID do Pedido:</strong> ${pedido.id}</p>
                    <p><strong>Cliente:</strong> ${htmlspecialchars(pedido.cliente_nome)}</p>
                    <p><strong>Email:</strong> ${htmlspecialchars(pedido.cliente_email)}</p>
                    <p><strong>Telefone:</strong> ${htmlspecialchars(pedido.cliente_telefone ?? 'N/A')}</p>
                    <p><strong>Endereço:</strong> ${htmlspecialchars(pedido.endereco_logradouro)}, ${htmlspecialchars(pedido.endereco_numero)} ${htmlspecialchars(pedido.endereco_complemento ?? '')}, ${htmlspecialchars(pedido.endereco_bairro)}, ${htmlspecialchars(pedido.endereco_cidade)} - ${htmlspecialchars(pedido.endereco_estado)} CEP: ${htmlspecialchars(pedido.endereco_cep)}</p>
                    <p><strong>Subtotal:</strong> ${htmlspecialchars(pedido.subtotal)}</p>
                    <p><strong>Frete:</strong> ${htmlspecialchars(pedido.frete)}</p>
                    <p><strong>Desconto:</strong> ${htmlspecialchars(pedido.desconto)}</p>
                    <p><strong>Total:</strong> ${htmlspecialchars(pedido.total)}</p>
                    <p><strong>Status:</strong> ${htmlspecialchars(pedido.status)}</p>
                    <p><strong>Data:</strong> ${htmlspecialchars(pedido.data_criacao)}</p>
                    <h6>Itens do Pedido:</h6>
                    <ul>
                `;
                
                if (pedido.itens && pedido.itens.length > 0) {
                    pedido.itens.forEach(item => {
                        detalhesHtml += `
                            <li>${htmlspecialchars(item.quantidade)} x ${htmlspecialchars(item.produto_nome)} (${htmlspecialchars(item.variacao)}) - ${htmlspecialchars(item.preco_unitario)}</li>
                        `;
                    });
                } else {
                    detalhesHtml += `<li>Nenhum item encontrado.</li>`;
                }
                
                detalhesHtml += `</ul>`;
                
                document.getElementById('detalhesPedido').innerHTML = detalhesHtml;
                const modal = new bootstrap.Modal(document.getElementById('modalVerPedido'));
                modal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Ocorreu um erro ao buscar detalhes do pedido.', 'danger');
        });
    }

    // Função para atualizar status do pedido (Prompt simples por enquanto)
    function atualizarStatus(id) {
        const novoStatus = prompt('Digite o novo status (pendente, aprovado, enviado, entregue, cancelado):');
        if (novoStatus) {
            fetch(`actions/pedido.php?action=atualizarStatus&id=${id}&status=${novoStatus}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    location.reload(); // Recarrega a página para ver a lista atualizada
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Ocorreu um erro ao atualizar o status.', 'danger');
            });
        }
    }

    // --- Cupons --- //

    // Tratar submissão do formulário de cupom via AJAX
    const formCupom = document.getElementById('formCupom');
    if (formCupom) {
        console.log('formCupom element found');
        // Removido o event listener de submit via AJAX
        // formCupom.addEventListener('submit', function(e) {
        //     console.log('Cupom form submitted');
        //     e.preventDefault();
        //     const form = e.target;
        //     const formData = new FormData(form);
            
        //     fetch(form.action, {
        //         method: form.method,
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             showAlert(data.message, 'success');
        //             // Fechar modal e recarregar lista de cupons (opcional)
        //             const modal = bootstrap.Modal.getInstance(document.getElementById('modalCupom'));
        //             modal.hide();
        //             location.reload(); // Recarrega a página para ver a lista atualizada
        //         } else {
        //             showAlert(data.message, 'danger');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Erro:', error);
        //         showAlert('Ocorreu um erro ao processar a requisição.', 'danger');
        //     });
        // });
    } else {
         console.log('formCupom element NOT found');
    }

    // Função para excluir cupom
    function excluirCupom(id) {
        if (confirm('Tem certeza que deseja excluir este cupom?')) {
            fetch(`actions/cupom.php?action=excluir&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    location.reload(); // Recarrega a página para ver a lista atualizada
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Ocorreu um erro ao excluir o cupom.', 'danger');
            });
        }
    }
});
</script> 