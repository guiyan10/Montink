<?php
$produto = new Produto();
$pedido = new Pedido();
$cupom = new Cupom();

$subtotal = 0;
$itens_carrinho = [];

// Processar itens do carrinho
foreach ($_SESSION['carrinho'] as $item) {
    $produto_info = $produto->find($item['id_produto']);
    if ($produto_info) {
        $item['produto'] = $produto_info;
        $item['subtotal'] = $item['quantidade'] * $produto_info['preco'];
        $subtotal += $item['subtotal'];
        $itens_carrinho[] = $item;
    }
}

$frete = $pedido->calcularFrete($subtotal);
$total = $subtotal + $frete;

// Processar cupom se existir
$desconto = 0;
if (isset($_SESSION['cupom'])) {
    $cupom_info = $cupom->validarCupom($_SESSION['cupom'], $subtotal);
    if ($cupom_info) {
        $desconto = $cupom_info['valor_desconto'];
        $total -= $desconto;
    } else {
        unset($_SESSION['cupom']);
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Carrinho de Compras</h1>
    </div>
</div>

<?php if (empty($itens_carrinho)): ?>
    <div class="alert alert-info">
        Seu carrinho está vazio. <a href="index.php?page=produtos">Continue comprando</a>.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Variação</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_carrinho as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['produto']['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($item['variacao']); ?></td>
                                    <td>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    onclick="atualizarQuantidade(<?php echo $item['id_produto']; ?>, 
                                                    Math.max(1, document.getElementById('qtd-<?php echo $item['id_produto']; ?>').value - 1))">
                                                -
                                            </button>
                                            <input type="number" class="form-control text-center" 
                                                   id="qtd-<?php echo $item['id_produto']; ?>"
                                                   value="<?php echo $item['quantidade']; ?>" min="1"
                                                   onchange="atualizarQuantidade(<?php echo $item['id_produto']; ?>, this.value)">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    onclick="atualizarQuantidade(<?php echo $item['id_produto']; ?>, 
                                                    parseInt(document.getElementById('qtd-<?php echo $item['id_produto']; ?>').value) + 1)">
                                                +
                                            </button>
                                        </div>
                                    </td>
                                    <td><?php echo Utils::formatarPreco($item['produto']['preco']); ?></td>
                                    <td><?php echo Utils::formatarPreco($item['subtotal']); ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="atualizarQuantidade(<?php echo $item['id_produto']; ?>, 0)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Resumo do Pedido</h5>
                    
                    <div class="mb-3">
                        <label for="cupom" class="form-label">Cupom de Desconto</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cupom" 
                                   value="<?php echo isset($_SESSION['cupom']) ? $_SESSION['cupom'] : ''; ?>"
                                   placeholder="Digite o código">
                            <button class="btn btn-outline-primary" type="button" 
                                    onclick="validarCupom(document.getElementById('cupom').value)">
                                Aplicar
                            </button>
                        </div>
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo Utils::formatarPreco($subtotal); ?></span>
                    </div>
                    
                    <?php if ($desconto > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Desconto:</span>
                            <span>-<?php echo Utils::formatarPreco($desconto); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span><?php echo Utils::formatarPreco($frete); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong><?php echo Utils::formatarPreco($total); ?></strong>
                    </div>
                    
                    <a href="index.php?page=checkout" class="btn btn-primary w-100">
                        Finalizar Compra
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?> 