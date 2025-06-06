<?php
require_once '../utils/Utils.php';

// Garante que a resposta será em JSON com UTF-8
header('Content-Type: application/json; charset=utf-8');

// Inicializa a resposta
$response = [
    'success' => false,
    'message' => '',
    'address' => null
];

// Verifica se é uma requisição GET e se tem o parâmetro CEP
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cep'])) {
    $cep = preg_replace('/[^0-9]/', '', $_GET['cep']);
    
    try {
        // Busca o endereço
        $address = Utils::validarCEP($cep);
        
        if ($address) {
            // Formata a resposta de sucesso
            $response = [
                'success' => true,
                'message' => '',
                'address' => [
                    'logradouro' => $address['logradouro'] ?? '',
                    'bairro' => $address['bairro'] ?? '',
                    'localidade' => $address['localidade'] ?? '',
                    'uf' => $address['uf'] ?? ''
                ]
            ];
        } else {
            $response['message'] = 'CEP não encontrado ou inválido.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Erro ao validar CEP: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Requisição inválida.';
}

// Converte para JSON mantendo os caracteres especiais
$json_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Verifica se houve erro na codificação JSON
if ($json_response === false) {
    $response = [
        'success' => false,
        'message' => 'Erro ao processar resposta: ' . json_last_error_msg(),
        'address' => null
    ];
    $json_response = json_encode($response);
}

// Envia a resposta
echo $json_response; 