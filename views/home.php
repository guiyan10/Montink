<?php
$produto = new Produto();
$produtos = $produto->findAll();
?>

<div class="row">
    <div class="col-md-12 text-center mb-4">
        <h1>Bem-vindo ao Mini ERP</h1>
        <p class="lead">Sistema completo para gestão de pedidos, produtos e estoque</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Produtos em Destaque</h2>
    </div>
</div>

<div class="row">
    <?php foreach ($produtos as $produto): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                    <p class="card-text">
                        <strong>Preço: </strong>
                        <?php echo Utils::formatarPreco($produto['preco']); ?>
                    </p>
                    <a href="index.php?page=produtos&id=<?php echo $produto['id']; ?>" 
                       class="btn btn-primary">
                        Ver Detalhes
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-box fa-3x mb-3 text-primary"></i>
                <h3>Gestão de Produtos</h3>
                <p>Cadastre e gerencie seus produtos com facilidade</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x mb-3 text-primary"></i>
                <h3>Controle de Pedidos</h3>
                <p>Acompanhe todos os pedidos em tempo real</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                <h3>Gestão de Estoque</h3>
                <p>Mantenha seu estoque sempre atualizado</p>
            </div>
        </div>
    </div>
</div> 