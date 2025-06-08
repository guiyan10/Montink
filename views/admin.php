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

<!-- Alert Placeholder -->
<div id="alertPlaceholder"></div>

<?php if (isset($_SESSION['alert'])): ?>
    <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['alert']['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
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
                                                    onclick="editarProduto(<?php echo $prod['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="index.php?page=admin&action_type=excluir_produto&id=<?php echo $prod['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Tem certeza que deseja excluir este produto?')">
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

<!-- Modal Novo Produto -->
<div class="modal fade" id="modalNovoProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoProduto" action="index.php?page=admin" method="POST">
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
                        <div id="variacoesNovoProduto">
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
                        <button type="button" class="btn btn-secondary btn-sm" id="addVariacaoNovoProduto"><i class="fas fa-plus"></i> Adicionar Variação</button>
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

<!-- Modal Editar Produto -->
<div class="modal fade" id="modalEditarProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarProduto" action="index.php?page=admin" method="POST">
                    <input type="hidden" name="action" value="atualizar">
                    <input type="hidden" name="id" id="edit_produto_id">
                    
                    <div class="mb-3">
                        <label for="edit_nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit_nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_preco" class="form-label">Preço</label>
                        <input type="number" class="form-control" id="edit_preco" name="preco" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Variações</label>
                        <div id="variacoesEditarProduto">
                            <!-- Variações serão adicionadas dinamicamente -->
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="addVariacaoEditarProduto"><i class="fas fa-plus"></i> Adicionar Variação</button>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
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
// Funções globais
window.showAlert = function(message, type) {
    let alertPlaceholder = document.getElementById('alertPlaceholder');
    
    // Se o elemento não existir, criar um
    if (!alertPlaceholder) {
        alertPlaceholder = document.createElement('div');
        alertPlaceholder.id = 'alertPlaceholder';
        // Inserir após o título
        const title = document.querySelector('h1.mb-4');
        if (title) {
            title.parentNode.insertBefore(alertPlaceholder, title.nextSibling);
        } else {
            document.body.insertBefore(alertPlaceholder, document.body.firstChild);
        }
    }
    
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

window.htmlspecialchars = function(str) {
    if (typeof str !== 'string') {
        return str;
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}

window.editarProduto = function(id) {
    console.log('Editando produto ID:', id);
    
    // Limpa o formulário
    const form = document.getElementById('formEditarProduto');
    form.reset();
    
    // Limpa as variações
    const variacoesDiv = document.getElementById('variacoesEditarProduto');
    variacoesDiv.innerHTML = '';
    
    // Busca os dados do produto
    fetch(`actions/produto.php?action=buscar&id=${id}`)
    .then(response => response.json())
    .then(data => {
        console.log('Dados recebidos:', data);
        
        if (data.success) {
            const produto = data.data;
            
            // Preenche o formulário
            document.getElementById('edit_produto_id').value = produto.id;
            document.getElementById('edit_nome').value = produto.nome;
            document.getElementById('edit_preco').value = produto.preco;
            document.getElementById('edit_descricao').value = produto.descricao;
            
            // Preenche as variações
            if (produto.variacoes && produto.variacoes.length > 0) {
                produto.variacoes.forEach((variacao, index) => {
                    const newItem = `
                        <div class="variacao-item row mb-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="variacoes[${index}][nome]" 
                                       placeholder="Nome da Variação" value="${htmlspecialchars(variacao.variacao)}" required>
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control" name="variacoes[${index}][quantidade]" 
                                       placeholder="Quantidade" value="${variacao.quantidade}" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remover-variacao">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    variacoesDiv.insertAdjacentHTML('beforeend', newItem);
                });
            } else {
                // Se não houver variações, adiciona um campo vazio
                addDefaultVariacaoField(variacoesDiv);
            }

            // Abre o modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarProduto'));
            modal.show();
        } else {
            showAlert(data.message || 'Erro ao carregar dados do produto', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Ocorreu um erro ao buscar dados do produto', 'danger');
    });
}

window.addDefaultVariacaoField = function(variacoesDiv) {
    const index = variacoesDiv.querySelectorAll('.variacao-item').length;
    const newItem = `
        <div class="variacao-item row mb-2">
            <div class="col-md-6">
                <input type="text" class="form-control" name="variacoes[${index}][nome]" 
                       placeholder="Nome da Variação" required>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="variacoes[${index}][quantidade]" 
                       placeholder="Quantidade" min="0" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remover-variacao">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    variacoesDiv.insertAdjacentHTML('beforeend', newItem);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar/Remover variação no formulário de novo produto
    const addVariacaoNovoButton = document.getElementById('addVariacaoNovoProduto');
    if (addVariacaoNovoButton) {
        addVariacaoNovoButton.addEventListener('click', function() {
            const variacoesDiv = document.getElementById('variacoesNovoProduto');
            addDefaultVariacaoField(variacoesDiv);
        });
    }

    // Adicionar/Remover variação no formulário de editar produto
    const addVariacaoEditarButton = document.getElementById('addVariacaoEditarProduto');
    if (addVariacaoEditarButton) {
        addVariacaoEditarButton.addEventListener('click', function() {
            const variacoesDiv = document.getElementById('variacoesEditarProduto');
            addDefaultVariacaoField(variacoesDiv);
        });
    }

    // Remover variação (funciona para ambos os formulários)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remover-variacao') || e.target.parentElement.classList.contains('remover-variacao')) {
            const itemToRemove = e.target.closest('.variacao-item');
            if (itemToRemove) {
                itemToRemove.remove();
            }
        }
    });

    // Função para ver detalhes do pedido (AJAX)
    window.verPedido = function(id) {
        console.log('Buscando detalhes do pedido:', id);
        
        fetch(`actions/pedido.php?action=buscar&id=${id}`)
        .then(response => response.json())
        .then(data => {
            console.log('Dados recebidos:', data);
            
            if (data.success) {
                const pedido = data.data;
                let detalhesHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações do Cliente</h6>
                            <p><strong>Nome:</strong> ${htmlspecialchars(pedido.cliente_nome)}</p>
                            <p><strong>Email:</strong> ${htmlspecialchars(pedido.cliente_email)}</p>
                            <p><strong>Telefone:</strong> ${htmlspecialchars(pedido.cliente_telefone || 'N/A')}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Endereço de Entrega</h6>
                            <p>${htmlspecialchars(pedido.endereco_logradouro)}, ${htmlspecialchars(pedido.endereco_numero)}</p>
                            ${pedido.endereco_complemento ? `<p>${htmlspecialchars(pedido.endereco_complemento)}</p>` : ''}
                            <p>${htmlspecialchars(pedido.endereco_bairro)}</p>
                            <p>${htmlspecialchars(pedido.endereco_cidade)} - ${htmlspecialchars(pedido.endereco_estado)}</p>
                            <p>CEP: ${htmlspecialchars(pedido.endereco_cep)}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Itens do Pedido</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Variação</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                if (pedido.itens && pedido.itens.length > 0) {
                    pedido.itens.forEach(item => {
                        const subtotal = item.quantidade * item.preco_unitario;
                        detalhesHtml += `
                            <tr>
                                <td>${htmlspecialchars(item.produto_nome)}</td>
                                <td>${htmlspecialchars(item.variacao)}</td>
                                <td>${item.quantidade}</td>
                                <td>R$ ${parseFloat(item.preco_unitario).toFixed(2)}</td>
                                <td>R$ ${subtotal.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                } else {
                    detalhesHtml += `<tr><td colspan="5" class="text-center">Nenhum item encontrado</td></tr>`;
                }
                
                detalhesHtml += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td>R$ ${parseFloat(pedido.subtotal).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Frete:</strong></td>
                                <td>R$ ${parseFloat(pedido.frete).toFixed(2)}</td>
                            </tr>
                            ${pedido.desconto > 0 ? `
                            <tr class="text-success">
                                <td colspan="4" class="text-end"><strong>Desconto:</strong></td>
                                <td>-R$ ${parseFloat(pedido.desconto).toFixed(2)}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>R$ ${parseFloat(pedido.total).toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(pedido.status)}">${htmlspecialchars(pedido.status)}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data do Pedido:</strong> ${new Date(pedido.data_criacao).toLocaleString()}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('detalhesPedido').innerHTML = detalhesHtml;
            } else {
                document.getElementById('detalhesPedido').innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Erro ao carregar detalhes do pedido'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('detalhesPedido').innerHTML = `
                <div class="alert alert-danger">
                    Ocorreu um erro ao buscar detalhes do pedido
                </div>
            `;
        });
    }

    // Função auxiliar para determinar a cor do badge de status
    function getStatusColor(status) {
        switch (status) {
            case 'pendente': return 'warning';
            case 'aprovado': return 'info';
            case 'enviado': return 'primary';
            case 'entregue': return 'success';
            case 'cancelado': return 'danger';
            default: return 'secondary';
        }
    }

    // Função para atualizar status do pedido
    window.atualizarStatus = function(id) {
        const statusOptions = ['pendente', 'aprovado', 'enviado', 'entregue', 'cancelado'];
        const statusLabels = {
            'pendente': 'Pendente',
            'aprovado': 'Aprovado',
            'enviado': 'Enviado',
            'entregue': 'Entregue',
            'cancelado': 'Cancelado'
        };

        // Criar um select com as opções de status
        const select = document.createElement('select');
        select.className = 'form-select';
        statusOptions.forEach(status => {
            const option = document.createElement('option');
            option.value = status;
            option.textContent = statusLabels[status];
            select.appendChild(option);
        });

        // Substituir o prompt por um modal do Bootstrap
        const modalHtml = `
            <div class="modal fade" id="modalAtualizarStatus" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Atualizar Status do Pedido</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="novoStatus" class="form-label">Novo Status</label>
                                <select id="novoStatus" class="form-select">
                                    ${statusOptions.map(status => 
                                        `<option value="${status}">${statusLabels[status]}</option>`
                                    ).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" onclick="confirmarAtualizacaoStatus(${id})">
                                Atualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Adicionar o modal ao body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar o modal
        const modal = new bootstrap.Modal(document.getElementById('modalAtualizarStatus'));
        modal.show();

        // Remover o modal do DOM quando for fechado
        document.getElementById('modalAtualizarStatus').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

    // Função para confirmar a atualização do status
    window.confirmarAtualizacaoStatus = function(id) {
        const novoStatus = document.getElementById('novoStatus').value;
        
        fetch(`actions/pedido.php?action=atualizar_status&id=${id}&status=${novoStatus}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                // Fechar o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAtualizarStatus'));
                modal.hide();
                // Recarregar a página para atualizar a lista
                location.reload();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Ocorreu um erro ao atualizar o status.', 'danger');
        });
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