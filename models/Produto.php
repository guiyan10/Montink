<?php
require_once 'Model.php';

class Produto extends Model {
    protected $table = 'produtos';
    protected $db;
    
    public function __construct() {
        parent::__construct();
        $this->db = $this->getConnection();
    }
    
    public function criarProduto($dados) {
        try {
            $this->db->beginTransaction();
            
            // Insere o produto
            $sql = "INSERT INTO produtos (nome, descricao, preco) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dados['nome'], $dados['descricao'], $dados['preco']]);
            $produto_id = $this->db->lastInsertId();
            
            // Insere as variações
            if (!empty($dados['variacoes'])) {
                $sql = "INSERT INTO estoque (produto_id, variacao, quantidade) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($dados['variacoes'] as $variacao) {
                    $stmt->execute([$produto_id, $variacao['nome'], $variacao['quantidade']]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function atualizarProduto($id, $dados) {
        try {
            $this->db->beginTransaction();
            
            // Atualiza o produto
            $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dados['nome'], $dados['descricao'], $dados['preco'], $id]);
            
            // Remove variações existentes
            $sql = "DELETE FROM estoque WHERE produto_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            // Insere as novas variações
            if (!empty($dados['variacoes'])) {
                $sql = "INSERT INTO estoque (produto_id, variacao, quantidade) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($dados['variacoes'] as $variacao) {
                    $stmt->execute([$id, $variacao['nome'], $variacao['quantidade']]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function atualizarEstoque($produto_id, $variacao, $quantidade) {
        try {
            $sql = "UPDATE estoque SET quantidade = ? WHERE produto_id = ? AND variacao = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$quantidade, $produto_id, $variacao]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function verificarEstoque($produto_id, $variacao, $quantidade) {
        try {
            $sql = "SELECT quantidade FROM estoque WHERE produto_id = ? AND variacao = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$produto_id, $variacao]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && $result['quantidade'] >= $quantidade;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getProdutoComEstoque($id) {
        try {
            $sql = "SELECT p.*, e.variacao, e.quantidade 
                    FROM produtos p 
                    LEFT JOIN estoque e ON p.id = e.produto_id 
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function find($id) {
        try {
            // Primeiro, busca os dados básicos do produto
            $sql = "SELECT * FROM produtos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($produto) {
                // Busca as variações do produto
                $sql = "SELECT variacao, quantidade FROM estoque WHERE produto_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
                $variacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Adiciona as variações ao array do produto
                $produto['variacoes'] = $variacoes;
            }
            
            return $produto;
        } catch (Exception $e) {
            error_log("Erro ao buscar produto: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll() {
        try {
            $sql = "SELECT p.*, GROUP_CONCAT(CONCAT(e.variacao, ':', e.quantidade) SEPARATOR '|') as variacoes 
                    FROM produtos p 
                    LEFT JOIN estoque e ON p.id = e.produto_id 
                    GROUP BY p.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processa as variações para cada produto
            foreach ($produtos as &$produto) {
                $variacoes = [];
                if ($produto['variacoes']) {
                    foreach (explode('|', $produto['variacoes']) as $var) {
                        list($nome, $quantidade) = explode(':', $var);
                        $variacoes[] = [
                            'nome' => $nome,
                            'quantidade' => $quantidade
                        ];
                    }
                }
                $produto['variacoes'] = $variacoes;
            }
            
            return $produtos;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?> 