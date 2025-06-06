<?php
require_once '../config/database.php';

try {
    // Lê o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Divide o arquivo em comandos individuais
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    // Executa cada comando
    foreach ($commands as $command) {
        if (!empty($command)) {
            $stmt = Database::getInstance()->getConnection()->prepare($command);
            $stmt->execute();
        }
    }
    
    echo "Banco de dados criado com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao criar banco de dados: " . $e->getMessage() . "\n";
} 