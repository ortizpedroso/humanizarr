<?php
/**
 * Model de Usuário
 * Localização: src/models/Usuario.php
 */

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $nivel_acesso;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Verifica as credenciais de login
     */
    public function login($email, $password) {
        $query = "SELECT id, nome, email, senha, nivel_acesso FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verifica o hash da senha
                if (password_verify($password, $row['senha'])) {
                    $this->id = $row['id'];
                    $this->nome = $row['nome'];
                    $this->nivel_acesso = $row['nivel_acesso'];
                    return true;
                }
            }
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
        }
        
        return false;
    }
}