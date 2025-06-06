<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Utils {
    public static function validarCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) != 8) {
            return false;
        }
        
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $response = file_get_contents($url);
        
        if ($response === false) {
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['erro'])) {
            return false;
        }
        
        return $data;
    }
    
    public static function enviarEmail($para, $assunto, $mensagem) {
        $mail = new PHPMailer(true);
        
        try {
            // Configurações do Servidor SMTP (PREENCHA SEUS DADOS AQUI!)
            // $mail->SMTPDebug = 2; // Ativar saída de debug detalhada (para testes)
            $mail->isSMTP(); // Usar SMTP para envio
            $mail->Host       = 'smtp.gmail.com'; // Ex: smtp.gmail.com, smtp-mail.outlook.com
            $mail->SMTPAuth   = true; // Habilitar autenticação SMTP
            $mail->Username   = 'guilhermeyan.leite12@gmail.com'; // Seu e-mail do SMTP
            $mail->Password   = 'obrs jkpc jfxf hhbn'; // Sua senha do SMTP (ou senha de app)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar criptografia TLS
            $mail->Port       = 587; // Porta TCP para conexão (587 para TLS, 465 para SSL)
            $mail->CharSet = 'UTF-8';

            // Remetente e Destinatários
            $mail->setFrom('guilhermeyan.leite12@gmail.com', 'Guiyan Leite'); // E-mail e nome do remetente
            $mail->addAddress($para); // Adicionar um destinatário

            // Conteúdo
            $mail->isHTML(true); // Definir formato de e-mail para HTML
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = strip_tags($mensagem); // Corpo em texto plano para clientes sem HTML

            $mail->send();
            return true; // E-mail enviado com sucesso
        } catch (Exception $e) {
            // Capture e logue o erro detalhado do PHPMailer
            error_log("Erro ao enviar e-mail (PHPMailer): {$mail->ErrorInfo}");
            return false; // Falha no envio
        }
    }
    
    public static function formatarPreco($preco) {
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }
    
    public static function gerarTemplateEmailPedido($pedido, $itens) {
        // Use null coalescing operator (??) para evitar warnings se algum campo for null/indefinido
        $logradouro = $pedido['endereco_logradouro'] ?? '';
        $numero = $pedido['endereco_numero'] ?? '';
        $complemento = $pedido['endereco_complemento'] ?? '';
        $bairro = $pedido['endereco_bairro'] ?? '';
        $cidade = $pedido['endereco_cidade'] ?? '';
        $estado = $pedido['endereco_estado'] ?? '';
        $cep = $pedido['endereco_cep'] ?? '';
        $telefone = $pedido['cliente_telefone'] ?? 'Não informado';

        // Formata o endereço completo, incluindo complemento se existir
        $endereco_completo = $logradouro . ', ' . $numero;
        if (!empty($complemento)) {
            $endereco_completo .= ' - ' . $complemento;
        }
        $endereco_completo .= ' - ' . $bairro;
        $endereco_completo .= ', ' . $cidade . ' - ' . $estado;


        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; border: 1px solid #ddd; }
                th { background: #f8f9fa; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Pedido Confirmado</h2>
                </div>
                <div class='content'>
                    <p>Olá {$pedido['cliente_nome']},</p>
                    <p>Seu pedido foi confirmado com sucesso!</p>
                    
                    <h3>Detalhes do Pedido:</h3>
                    <table>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>";
        
        foreach ($itens as $item) {
            // Calcular subtotal do item caso não venha nos dados (embora já esteja vindo do join)
            $item_subtotal = ($item['quantidade'] ?? 0) * ($item['preco_unitario'] ?? 0);
            $html .= "
                        <tr>
                            <td>" . ($item['produto_nome'] ?? 'N/A') . "</td>
                            <td>" . ($item['variacao'] ?? 'N/A') . "</td>
                            <td>" . ($item['quantidade'] ?? 0) . "</td>
                            <td>" . self::formatarPreco($item['preco_unitario'] ?? 0) . "</td>
                            <td>" . self::formatarPreco($item['subtotal'] ?? $item_subtotal) . "</td>
                        </tr>";
        }
        
        $html .= "
                    </table>
                    
                    <h3>Resumo do Pedido:</h3>
                    <p>Subtotal: " . self::formatarPreco($pedido['subtotal'] ?? 0) . "</p>
                    <p>Frete: " . self::formatarPreco($pedido['frete'] ?? 0) . "</p>
                     <p>Desconto: " . self::formatarPreco($pedido['desconto'] ?? 0) . "</p>
                    <p><strong>Total: " . self::formatarPreco($pedido['total'] ?? 0) . "</strong></p>
                    
                    <h3>Endereço de Entrega:</h3>
                    <p>{$endereco_completo}</p>
                    <p>CEP: {$cep}</p>
                    <p>Telefone: {$telefone}</p>
                </div>
                <div class='footer'>
                    <p>Este é um e-mail automático, por favor não responda.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}
?> 