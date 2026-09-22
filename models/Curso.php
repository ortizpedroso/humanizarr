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
                  (nome, descricao, duracao, local, palestrante, presidente, vice_presidente, data_evento, horario_inicio, vagas_limite, status, banner_imagem, certificado_arquivo, emitir_certificado) 
                  VALUES (:nome, :descricao, :duracao, :local, :palestrante, :presidente, :vice_presidente, :data_evento, :horario_inicio, :vagas_limite, :status, :banner_imagem, :certificado_arquivo, :emitir_certificado)";
        
        $stmt = $this->conn->prepare($query);
        
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
        $stmt->bindParam(":horario_inicio", $dados['horario_inicio']);
        $stmt->bindParam(":vagas_limite", $dados['vagas_limite']);
        $stmt->bindParam(":status", $dados['status']);
        
        $banner = $dados['banner_imagem'] ?? null;
        $certificado = $dados['certificado_arquivo'] ?? null;
        $emitir = $dados['emitir_certificado'] ?? 0;
        
        $stmt->bindParam(":banner_imagem", $banner);
        $stmt->bindParam(":certificado_arquivo", $certificado);
        $stmt->bindParam(":emitir_certificado", $emitir);

        return $stmt->execute();
    }

    // Editar curso existente
    public function editar($dados) {
        $query = "UPDATE " . $this->tabela . " 
                  SET nome = :nome, 
                      descricao = :descricao, 
                      duracao = :duracao, 
                      local = :local, 
                      palestrante = :palestrante, 
                      presidente = :presidente, 
                      vice_presidente = :vice_presidente, 
                      data_evento = :data_evento, 
                      horario_inicio = :horario_inicio, 
                      vagas_limite = :vagas_limite, 
                      status = :status";
        
        if (isset($dados['banner_imagem']) && !empty($dados['banner_imagem'])) {
            $query .= ", banner_imagem = :banner_imagem";
        }
        if (isset($dados['certificado_arquivo']) && !empty($dados['certificado_arquivo'])) {
            $query .= ", certificado_arquivo = :certificado_arquivo";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $dados['nome'] = htmlspecialchars(strip_tags($dados['nome']));
        $dados['descricao'] = htmlspecialchars(strip_tags($dados['descricao']));
        $dados['duracao'] = htmlspecialchars(strip_tags($dados['duracao']));
        $dados['local'] = htmlspecialchars(strip_tags($dados['local']));
        $dados['palestrante'] = htmlspecialchars(strip_tags($dados['palestrante']));
        $dados['presidente'] = htmlspecialchars(strip_tags($dados['presidente']));
        $dados['vice_presidente'] = htmlspecialchars(strip_tags($dados['vice_presidente']));

        $stmt->bindParam(":id", $dados['id']);
        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":descricao", $dados['descricao']);
        $stmt->bindParam(":duracao", $dados['duracao']);
        $stmt->bindParam(":local", $dados['local']);
        $stmt->bindParam(":palestrante", $dados['palestrante']);
        $stmt->bindParam(":presidente", $dados['presidente']);
        $stmt->bindParam(":vice_presidente", $dados['vice_presidente']);
        $stmt->bindParam(":data_evento", $dados['data_evento']);
        $stmt->bindParam(":horario_inicio", $dados['horario_inicio']);
        $stmt->bindParam(":vagas_limite", $dados['vagas_limite']);
        $stmt->bindParam(":status", $dados['status']);
        
        if (isset($dados['banner_imagem']) && !empty($dados['banner_imagem'])) {
            $stmt->bindParam(":banner_imagem", $dados['banner_imagem']);
        }
        if (isset($dados['certificado_arquivo']) && !empty($dados['certificado_arquivo'])) {
            $stmt->bindParam(":certificado_arquivo", $dados['certificado_arquivo']);
        }
        
        $emitir = isset($dados['emitir_certificado']) ? 1 : 0;
        $stmt->bindParam(":emitir_certificado", $emitir);

        return $stmt->execute();
    }

    public function lerTodos() {
        $query = "SELECT * FROM " . $this->tabela . " ORDER BY data_evento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function lerPorId($id) {
        $query = "SELECT * FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function toggleStatus($id) {
        $curso = $this->lerPorId($id);
        if (!$curso) return false;
        $novoStatus = ($curso['status'] == 'Aberto') ? 'Rascunho' : 'Aberto';
        $query = "UPDATE " . $this->tabela . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $novoStatus);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function excluir($id) {
        $query = "DELETE FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function contarInscricoes($id_curso) {
        $query = "SELECT COUNT(*) as total FROM inscricoes WHERE id_curso = :id_curso";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $id_curso);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
    
    public function estaLotado($id_curso) {
        $curso = $this->lerPorId($id_curso);
        if (!$curso || $curso['vagas_limite'] == 0) return false;
        $total_inscritos = $this->contarInscricoes($id_curso);
        return $total_inscritos >= $curso['vagas_limite'];
    }
    
    public function lerCursosAtivos() {
        $query = "SELECT * FROM " . $this->tabela . " WHERE status = 'Aberto' ORDER BY data_evento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
