<?php
$produtoModel = new Produto();

// Se tiver ID, mostra detalhes do produto
if (isset($_GET['id'])) {
    $produto_detalhes = $produtoModel->getProdutoComEstoque($_GET['id']);
    if ($produto_detalhes) {
        $produto_atual = $produto_detalhes[0];
        ?>
        <div class="row">
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($produto_atual['nome'] ?? ''); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($produto_atual['descricao'] ?? ''); ?></p>
                <h3 class="text-primary"><?php echo Utils::formatarPreco($produto_atual['preco'] ?? 0); ?></h3>
                
                <form action="actions/carrinho.php" method="POST" class="mt-4">
                    <input type="hidden" name="action" value="adicionar">
                    <input type="hidden" name="id_produto" value="<?php echo $produto_atual['id'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label for="variacao" class="form-label">Variação</label>
                        <select name="variacao" id="variacao" class="form-select" required>
                            <option value="">Selecione uma variação</option>
                            <?php foreach ($produto_detalhes as $variacao): ?>
                                <option value="<?php echo htmlspecialchars($variacao['variacao'] ?? ''); ?>"
                                        data-quantidade="<?php echo $variacao['quantidade'] ?? 0; ?>">
                                    <?php echo htmlspecialchars($variacao['variacao'] ?? ''); ?> 
                                    (<?php echo $variacao['quantidade'] ?? 0; ?> disponíveis)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" id="quantidade" 
                               class="form-control" min="1" value="1" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Produto não encontrado.</div>';
    }
} else {
    // Lista todos os produtos
    $produtos = $produtoModel->findAll();
    ?>
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Nossos Produtos</h1>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($produtos as $produto): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome'] ?? ''); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($produto['descricao'] ?? ''); ?></p>
                        <p class="card-text">
                            <strong>Preço: </strong>
                            <?php echo Utils::formatarPreco($produto['preco'] ?? 0); ?>
                        </p>
                        <a href="index.php?page=produtos&id=<?php echo $produto['id'] ?? ''; ?>" 
                           class="btn btn-primary">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script> 