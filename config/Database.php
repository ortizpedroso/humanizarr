<?php
/**
 * Classe de Conexão com o Banco de Dados
 * Localização: src/config/Database.php
 * Editado para: Produção (Hostinger)
 */

class Database {
    // 1. Na Hostinger, o host é sempre localhost
    private $host = "localhost"; 
    
    // 2. Dados fornecidos
    private $db_name = "u970180508_humanizarr";
    private $username = "u970180508_humanizarr";
    private $password = "H@i2026*#";
    
    private $port = "3306";
    public $conn;

    public function __construct() {
        // O construtor fica vazio pois definimos as credenciais diretamente acima.
        // Removemos o getenv() pois na hospedagem compartilhada não usamos variáveis de ambiente do Docker.
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $exception) {
            // Mantivemos sua formatação de erro original
            echo "<div class='alert alert-warning m-3 small'><strong>Erro de Conexão:</strong> " . $exception->getMessage() . "</div>";
            return null;
        }

        return $this->conn;
    }
}