    </div>
    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Mini ERP. Todos os direitos reservados.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Função para atualizar quantidade no carrinho
        function atualizarQuantidade(id, quantidade) {
            $.post('actions/carrinho.php', {
                action: 'atualizar',
                id: id,
                quantidade: quantidade
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            });
        }

        // Função para validar CEP
        function validarCEP(cep) {
            $.get('actions/cep.php', { cep: cep }, function(response) {
                if (response.success) {
                    $('#endereco').val(response.data.logradouro);
                    $('#bairro').val(response.data.bairro);
                    $('#cidade').val(response.data.localidade);
                    $('#estado').val(response.data.uf);
                } else {
                    alert('CEP inválido');
                }
            });
        }

        // Função para validar cupom
        function validarCupom(codigo) {
            if (!codigo) {
                alert('Por favor, digite o código do cupom');
                return;
            }

            const subtotal = parseFloat($('input[name="subtotal"]').val() || 0);
            if (subtotal <= 0) {
                alert('Valor do pedido inválido');
                return;
            }

            $.post('actions/cupom.php', { 
                action: 'validar',
                codigo: codigo,
                subtotal: subtotal
            }, function(response) {
                if (response.success) {
                    // Atualiza o valor do desconto e total na página
                    if (response.desconto > 0) {
                        // Atualiza o valor do desconto
                        $('.desconto-valor').text('-R$ ' + response.desconto.toFixed(2));
                        $('.desconto-row').show();
                        
                        // Atualiza o total
                        const novoTotal = subtotal - response.desconto;
                        $('.total-valor').text('R$ ' + novoTotal.toFixed(2));
                    }
                    
                    // Mostra mensagem de sucesso
                    alert(response.message);
                    
                    // Recarrega a página para atualizar todos os valores
                    location.reload();
                } else {
                    alert(response.message);
                }
            }).fail(function() {
                alert('Erro ao validar cupom. Por favor, tente novamente.');
            });
        }
    </script>
</body>
</html> 