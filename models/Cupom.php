<?php
require_once __DIR__ . '/Model.php';

class Cupom extends Model {
    protected $table = 'cupons';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function validarCupom($codigo, $subtotal) {
        try {
            $query = "SELECT * FROM cupons 
                     WHERE codigo = :codigo 
                     AND validade > NOW() 
                     AND valor_minimo <= :subtotal
                     AND ativo = 1";
            
            $stmt = $this->getConnection()->prepare($query);
            $stmt->bindParam(":codigo", $codigo);
            $stmt->bindParam(":subtotal", $subtotal);
            $stmt->execute();
            
            $cupom = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cupom) {
                // Calcula o valor do desconto
                $desconto = min($cupom['valor_desconto'], $subtotal);
                $cupom['valor_desconto'] = $desconto;
                return $cupom;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao validar cupom: " . $e->getMessage());
            return false;
        }
    }
    
    public function criarCupom($dados) {
        // Validar formato da data
        if (!strtotime($dados['validade'])) {
            throw new Exception("Data de validade inválida");
        }
        
        // Validar valor mínimo
        if ($dados['valor_minimo'] < 0) {
            throw new Exception("Valor mínimo inválido");
        }
        
        // Validar valor do desconto
        if ($dados['valor_desconto'] <= 0) {
            throw new Exception("Valor do desconto inválido");
        }
        
        return $this->create($dados);
    }
    
    public function aplicarDesconto($subtotal, $valor_desconto) {
        $desconto = min($valor_desconto, $subtotal);
        return $subtotal - $desconto;
    }
}
?> 