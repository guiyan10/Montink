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
    
    public static function enviarEmail($destinatario, $assunto, $mensagem) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Configure your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Configure your email
            $mail->Password = 'your-app-password'; // Configure your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Mini ERP');
            $mail->addAddress($destinatario);
            $mail->addReplyTo('your-email@gmail.com', 'Mini ERP');

            // Content
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            $mail->AltBody = strip_tags($mensagem);

            // Log email attempt
            error_log("Attempting to send email to: {$destinatario}");
            
            $mail->send();
            error_log("Email sent successfully to: {$destinatario}");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send email to {$destinatario}. Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    public static function formatarPreco($preco) {
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }
    
    public static function gerarTemplateEmailPedido($pedido, $itens) {
        // Use null coalescing operator (??) to avoid warnings if some field is null/undefined
        $logradouro = $pedido['endereco_logradouro'] ?? '';
        $numero = $pedido['endereco_numero'] ?? '';
        $complemento = $pedido['endereco_complemento'] ?? '';
        $bairro = $pedido['endereco_bairro'] ?? '';
        $cidade = $pedido['endereco_cidade'] ?? '';
        $estado = $pedido['endereco_estado'] ?? '';
        $cep = $pedido['endereco_cep'] ?? '';
        $telefone = $pedido['cliente_telefone'] ?? 'Não informado';

        // Format complete address, including complement if it exists
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
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background: #fff; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
                th { background: #f8f9fa; }
                .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
                .status { display: inline-block; padding: 5px 10px; border-radius: 3px; background: #28a745; color: white; }
                .alert { padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px 0; }
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
                    <p class='total'>Total: " . self::formatarPreco($pedido['total'] ?? 0) . "</p>
                    
                    <h3>Endereço de Entrega:</h3>
                    <p>{$endereco_completo}</p>
                    <p>CEP: {$cep}</p>
                    <p>Telefone: {$telefone}</p>

                    <div class='alert'>
                        <strong>Importante:</strong> Este é um e-mail automático. Por favor, não responda a esta mensagem.
                        Para qualquer dúvida, entre em contato com nosso suporte.
                    </div>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Mini ERP. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}
?> 