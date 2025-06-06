<?php
require_once __DIR__ . '/Model.php';

class Pedido extends Model {
    protected $table = 'pedidos';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function criarPedido($dados_pedido, $itens) {
        try {
            $this->db->beginTransaction();
            
            // Inserir pedido com nomes de colunas explicitos para evitar 'id_pedido' na query da tabela principal
            $sql_pedido = "INSERT INTO {$this->table} (
                cliente_nome, cliente_email, cliente_telefone, 
                endereco_cep, endereco_logradouro, endereco_numero, endereco_complemento, 
                endereco_bairro, endereco_cidade, endereco_estado, 
                subtotal, frete, desconto, total, status
            ) VALUES (
                :cliente_nome, :cliente_email, :cliente_telefone, 
                :endereco_cep, :endereco_logradouro, :endereco_numero, :endereco_complemento, 
                :endereco_bairro, :endereco_cidade, :endereco_estado, 
                :subtotal, :frete, :desconto, :total, :status
            )";
            
            $stmt_pedido = $this->db->prepare($sql_pedido);

            $stmt_pedido->bindParam(':cliente_nome', $dados_pedido['cliente_nome']);
            $stmt_pedido->bindParam(':cliente_email', $dados_pedido['cliente_email']);
            $stmt_pedido->bindParam(':cliente_telefone', $dados_pedido['cliente_telefone']);
            $stmt_pedido->bindParam(':endereco_cep', $dados_pedido['endereco_cep']);
            $stmt_pedido->bindParam(':endereco_logradouro', $dados_pedido['endereco_logradouro']);
            $stmt_pedido->bindParam(':endereco_numero', $dados_pedido['endereco_numero']);
            $stmt_pedido->bindParam(':endereco_complemento', $dados_pedido['endereco_complemento']);
            $stmt_pedido->bindParam(':endereco_bairro', $dados_pedido['endereco_bairro']);
            $stmt_pedido->bindParam(':endereco_cidade', $dados_pedido['endereco_cidade']);
            $stmt_pedido->bindParam(':endereco_estado', $dados_pedido['endereco_estado']);
            $stmt_pedido->bindParam(':subtotal', $dados_pedido['subtotal']);
            $stmt_pedido->bindParam(':frete', $dados_pedido['frete']);
            $stmt_pedido->bindParam(':desconto', $dados_pedido['desconto']);
            $stmt_pedido->bindParam(':total', $dados_pedido['total']);
            $stmt_pedido->bindParam(':status', $dados_pedido['status']);
            
            $stmt_pedido->execute();

            $id_pedido = $this->db->lastInsertId();
            
            // Inserir itens do pedido
            foreach ($itens as $item) {
                $query = "INSERT INTO pedido_itens (pedido_id, produto_id, variacao, quantidade, preco_unitario) 
                         VALUES (:pedido_id, :produto_id, :variacao, :quantidade, :preco_unitario)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":pedido_id", $id_pedido);
                $stmt->bindParam(":produto_id", $item['id_produto']);
                $stmt->bindParam(":variacao", $item['variacao']);
                $stmt->bindParam(":quantidade", $item['quantidade']);
                $stmt->bindParam(":preco_unitario", $item['preco_unitario']);
                $stmt->execute();
                
                // Atualizar estoque (corrigido nome da tabela e campos)
                $query = "UPDATE estoque 
                         SET quantidade = quantidade - :quantidade 
                         WHERE produto_id = :produto_id AND variacao = :variacao";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":quantidade", $item['quantidade']);
                $stmt->bindParam(":produto_id", $item['produto_id']);
                $stmt->bindParam(":variacao", $item['variacao']);
                $stmt->execute();
            }
            
            $this->db->commit();
            return $id_pedido;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getPedidoComItens($id) {
        $query = "SELECT p.*, pi.*, pr.nome as produto_nome 
                 FROM pedidos p 
                 JOIN pedido_itens pi ON p.id = pi.pedido_id 
                 JOIN produtos pr ON pi.produto_id = pr.id 
                 WHERE p.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function atualizarStatus($id, $status) {
        if ($status === 'cancelado') {
            try {
                $this->db->beginTransaction();
                
                // Buscar itens do pedido
                $query = "SELECT * FROM pedido_itens WHERE pedido_id = :pedido_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":pedido_id", $id);
                $stmt->execute();
                $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Devolver itens ao estoque (corrigido nome da tabela e campos)
                foreach ($itens as $item) {
                    $query = "UPDATE estoque 
                             SET quantidade = quantidade + :quantidade 
                             WHERE produto_id = :produto_id AND variacao = :variacao";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(":quantidade", $item['quantidade']);
                    $stmt->bindParam(":produto_id", $item['produto_id']);
                    $stmt->bindParam(":variacao", $item['variacao']);
                    $stmt->execute();
                }
                
                // Deletar pedido usando o método do Model
                $this->delete($id);
                
                $this->db->commit();
                return true;
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } else {
            return $this->update($id, ['status' => $status]);
        }
    }
    
    public function calcularFrete($subtotal) {
        if ($subtotal >= 200.00) {
            return 0.00;
        } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
            return 15.00;
        } else {
            return 20.00;
        }
    }
}
?> 