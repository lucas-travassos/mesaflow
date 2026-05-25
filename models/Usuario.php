<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Busca usuário pelo e-mail para validação de credenciais
    public function buscarPorEmail($email) {
        $query = "SELECT usuario_id, nome, email, senha, nivel_acesso, status FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}