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
            
            // Insert order with explicit column names to avoid 'id_pedido' in the main table query
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
            $pedido_id = $this->db->lastInsertId();
            
            // Insert order items
            $sql_itens = "INSERT INTO pedido_itens (
                pedido_id, produto_id, variacao, quantidade, preco_unitario, subtotal
            ) VALUES (
                :pedido_id, :produto_id, :variacao, :quantidade, :preco_unitario, :subtotal
            )";
            
            $stmt_itens = $this->db->prepare($sql_itens);
            
            foreach ($itens as $item) {
                $stmt_itens->bindParam(':pedido_id', $pedido_id);
                $stmt_itens->bindParam(':produto_id', $item['produto_id']);
                $stmt_itens->bindParam(':variacao', $item['variacao']);
                $stmt_itens->bindParam(':quantidade', $item['quantidade']);
                $stmt_itens->bindParam(':preco_unitario', $item['preco_unitario']);
                $stmt_itens->bindParam(':subtotal', $item['subtotal']);
                $stmt_itens->execute();
                
                // Update stock
                $this->atualizarEstoque($item['produto_id'], $item['variacao'], $item['quantidade']);
            }
            
            $this->db->commit();
            return $pedido_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function atualizarStatus($id, $status) {
        try {
            // Validate status
            $status_validos = ['pendente', 'aprovado', 'enviado', 'entregue', 'cancelado'];
            if (!in_array($status, $status_validos)) {
                throw new Exception("Status inválido");
            }
            
            $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            $result = $stmt->execute();
            
            if ($result) {
                // Log status change
                error_log("Order #{$id} status updated to: {$status}");
                
                // If order is cancelled, restore stock
                if ($status === 'cancelado') {
                    $this->restaurarEstoque($id);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getPedidoComItens($id) {
        try {
            $sql = "SELECT p.*, pi.*, pr.nome as produto_nome 
                    FROM {$this->table} p 
                    JOIN pedido_itens pi ON p.id = pi.pedido_id 
                    JOIN produtos pr ON pi.produto_id = pr.id 
                    WHERE p.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching order with items: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function atualizarEstoque($produto_id, $variacao, $quantidade) {
        try {
            $sql = "UPDATE estoque 
                    SET quantidade = quantidade - :quantidade 
                    WHERE produto_id = :produto_id AND variacao = :variacao";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->bindParam(':variacao', $variacao);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function restaurarEstoque($pedido_id) {
        try {
            $sql = "UPDATE estoque e 
                    JOIN pedido_itens pi ON e.produto_id = pi.produto_id AND e.variacao = pi.variacao 
                    SET e.quantidade = e.quantidade + pi.quantidade 
                    WHERE pi.pedido_id = :pedido_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':pedido_id', $pedido_id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error restoring stock: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function calcularFrete($subtotal) {
        // Shipping calculation based on subtotal ranges
        if ($subtotal >= 200.00) {
            return 0.00; // Free shipping for orders over R$ 200
        } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
            return 15.00; // R$ 15 shipping for orders between R$ 52 and R$ 166.59
        } else {
            return 20.00; // R$ 20 shipping for other values
        }
    }
}
?> 