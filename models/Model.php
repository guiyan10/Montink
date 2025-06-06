<?php
require_once __DIR__ . '/../config/database.php';

class Model {
    protected $db;
    protected $table;
    protected $message;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    protected function setMessage($message) {
        $this->message = $message;
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findAll() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $set = array_map(function($field) {
            return "{$field} = ?";
        }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?> 