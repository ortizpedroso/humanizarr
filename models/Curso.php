<?php
class Curso {
    private $conn;
    private $tabela = "cursos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar novo curso
    public function criar($dados) {
        $query = "INSERT INTO " . $this->tabela . " 
                  (nome, descricao, duracao, local, palestrante, presidente, vice_presidente, data_evento, status) 
                  VALUES (:nome, :descricao, :duracao, :local, :palestrante, :presidente, :vice_presidente, :data_evento, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitização contra XSS
        $dados['nome'] = htmlspecialchars(strip_tags($dados['nome']));
        $dados['descricao'] = htmlspecialchars(strip_tags($dados['descricao']));
        $dados['duracao'] = htmlspecialchars(strip_tags($dados['duracao']));
        $dados['local'] = htmlspecialchars(strip_tags($dados['local']));
        $dados['palestrante'] = htmlspecialchars(strip_tags($dados['palestrante']));
        $dados['presidente'] = htmlspecialchars(strip_tags($dados['presidente']));
        $dados['vice_presidente'] = htmlspecialchars(strip_tags($dados['vice_presidente']));

        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":descricao", $dados['descricao']);
        $stmt->bindParam(":duracao", $dados['duracao']);
        $stmt->bindParam(":local", $dados['local']);
        $stmt->bindParam(":palestrante", $dados['palestrante']);
        $stmt->bindParam(":presidente", $dados['presidente']);
        $stmt->bindParam(":vice_presidente", $dados['vice_presidente']);
        $stmt->bindParam(":data_evento", $dados['data_evento']);
        $stmt->bindParam(":status", $dados['status']);

        return $stmt->execute();
    }

    // Listar todos os cursos
    public function lerTodos() {
        $query = "SELECT * FROM " . $this->tabela . " ORDER BY data_evento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler curso por ID
    public function lerPorId($id) {
        $query = "SELECT * FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alternar Status (Aberto <-> Rascunho)
    public function toggleStatus($id) {
        // Busca status atual
        $curso = $this->lerPorId($id);
        if (!$curso) return false;

        $novoStatus = ($curso['status'] == 'Aberto') ? 'Rascunho' : 'Aberto';

        $query = "UPDATE " . $this->tabela . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $novoStatus);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    // Excluir curso
    public function excluir($id) {
        $query = "DELETE FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Contar inscrições de um curso específico
    public function contarInscricoes($id_curso) {
        $query = "SELECT COUNT(*) as total FROM inscricoes WHERE id_curso = :id_curso";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $id_curso);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
    
    // Listar apenas cursos Abertos (para uso público se necessário)
    public function lerCursosAtivos() {
        $query = "SELECT * FROM " . $this->tabela . " WHERE status = 'Aberto' ORDER BY data_evento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
