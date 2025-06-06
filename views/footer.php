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
            $.post('actions/cupom.php', { 
                action: 'validar',
                codigo: codigo,
                subtotal: parseFloat($('input[name="subtotal"]').val() || 0)
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            });
        }
    </script>
</body>
</html> 