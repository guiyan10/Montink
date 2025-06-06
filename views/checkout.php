<?php
if (empty($_SESSION['carrinho'])) {
    header('Location: index.php?page=carrinho');
    exit;
}

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
        <h1 class="mb-4">Finalizar Compra</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Dados do Cliente</h5>
                <form id="checkout-form" action="actions/pedido.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cliente_nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cliente_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="cliente_email" name="cliente_email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="cliente_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="cliente_telefone" name="cliente_telefone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   pattern="[0-9]{8}" maxlength="8" required
                                   onblur="validarCEP(this.value)">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="logradouro" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="uf" 
                                   maxlength="2" required>
                        </div>
                    </div>
                    
                    <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                    <input type="hidden" name="frete" value="<?php echo $frete; ?>">
                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                    <input type="hidden" name="desconto" value="<?php echo $desconto; ?>">
                    
                    <button type="submit" class="btn btn-primary">
                        Finalizar Pedido
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Resumo do Pedido</h5>
                
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
                
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        Após finalizar o pedido, você receberá um e-mail com os detalhes da compra.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar CEP
    const cep = document.getElementById('cep').value;
    if (!/^\d{8}$/.test(cep)) {
        alert('CEP inválido');
        return;
    }
    
    // Validar e-mail
    const email = document.getElementById('cliente_email').value;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('E-mail inválido');
        return;
    }
    
    // Se tudo estiver ok, enviar o formulário
    this.submit();
});

// Função para validar CEP e preencher campos de endereço
function validarCEP(cep) {
    // Remove caracteres não numéricos do CEP
    cep = cep.replace(/\D/g, '');

    // Verifica se o campo CEP tem valor informado e se tem 8 dígitos
    if (cep.length != 8) {
        alert('CEP inválido');
        return;
    }

    // Mostra loading
    $('#endereco').val('Buscando...');
    $('#cidade').val('Buscando...');
    $('#bairro').val('Buscando...');

    // Faz a requisição
    $.ajax({
        url: 'actions/cep.php',
        method: 'GET',
        data: { cep: cep },
        dataType: 'json',
        success: function(data) {
            console.log('Dados recebidos:', data);
            
            // Verifica se temos os dados do endereço
            if (data && data.success && data.address) {
                // Preenche os campos
                $('#endereco').val(data.address.logradouro || '');
                $('#cidade').val(data.address.localidade || '');
                $('#bairro').val(data.address.bairro || '');
                
                // Foca no número
                $('#numero').focus();
            } else {
                // Limpa os campos em caso de erro
                $('#endereco').val('');
                $('#cidade').val('');
                $('#bairro').val('');
                alert(data.message || 'CEP não encontrado');
            }
        },
        error: function() {
            // Limpa os campos em caso de erro na requisição
            $('#endereco').val('');
            $('#cidade').val('');
            $('#bairro').val('');
            alert('Erro ao buscar CEP');
        }
    });
}
</script> 