<?php
class Inscrito {
    private $conn;
    private $tabela = "inscritos";
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Buscar inscrito por CPF
    public function buscarPorCpf($cpf) {
        $query = "SELECT * FROM " . $this->tabela . " WHERE cpf = :cpf LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // Buscar ou criar inscrito (upsert)
    public function buscarOuCriar($dados) {
        // Tenta buscar por CPF primeiro
        if (!empty($dados['cpf'])) {
            $existente = $this->buscarPorCpf($dados['cpf']);
            if ($existente) {
                // Atualiza dados se necessário
                $this->atualizar($existente['id'], $dados);
                return $existente['id'];
            }
        }
        
        // Se não encontrou por CPF, tenta por e-mail
        if (!empty($dados['email'])) {
            $query_email = "SELECT id FROM " . $this->tabela . " WHERE email = :email LIMIT 1";
            $stmt_email = $this->conn->prepare($query_email);
            $stmt_email->bindParam(":email", $dados['email']);
            $stmt_email->execute();
            
            if ($stmt_email->rowCount() > 0) {
                $resultado = $stmt_email->fetch(PDO::FETCH_ASSOC);
                $this->atualizar($resultado['id'], $dados);
                return $resultado['id'];
            }
        }
        
        // Não encontrou, cria novo
        return $this->criar($dados);
    }

    // Criar novo inscrito
    public function criar($dados) {
        $query = "INSERT INTO " . $this->tabela . " 
                  (nome, email, telefone, cpf, tipo, instituicao, semestre_atuacao) 
                  VALUES (:nome, :email, :telefone, :cpf, :tipo, :instituicao, :semestre_atuacao)";
        
        $stmt = $this->conn->prepare($query);
        
        $dados['nome'] = htmlspecialchars(strip_tags($dados['nome']));
        $dados['email'] = htmlspecialchars(strip_tags($dados['email']));
        $dados['telefone'] = htmlspecialchars(strip_tags($dados['telefone']));
        $dados['cpf'] = htmlspecialchars(strip_tags($dados['cpf']));
        $dados['tipo'] = htmlspecialchars(strip_tags($dados['tipo']));
        $dados['instituicao'] = htmlspecialchars(strip_tags($dados['instituicao']));
        $dados['semestre_atuacao'] = htmlspecialchars(strip_tags($dados['semestre_atuacao']));
        
        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":email", $dados['email']);
        $stmt->bindParam(":telefone", $dados['telefone']);
        $stmt->bindParam(":cpf", $dados['cpf']);
        $stmt->bindParam(":tipo", $dados['tipo']);
        $stmt->bindParam(":instituicao", $dados['instituicao']);
        $stmt->bindParam(":semestre_atuacao", $dados['semestre_atuacao']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Atualizar inscrito existente
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->tabela . " 
                  SET nome = :nome, 
                      email = :email, 
                      telefone = :telefone, 
                      cpf = :cpf, 
                      tipo = :tipo, 
                      instituicao = :instituicao, 
                      semestre_atuacao = :semestre_atuacao 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $dados['nome'] = htmlspecialchars(strip_tags($dados['nome']));
        $dados['email'] = htmlspecialchars(strip_tags($dados['email']));
        $dados['telefone'] = htmlspecialchars(strip_tags($dados['telefone']));
        $dados['cpf'] = htmlspecialchars(strip_tags($dados['cpf']));
        $dados['tipo'] = htmlspecialchars(strip_tags($dados['tipo']));
        $dados['instituicao'] = htmlspecialchars(strip_tags($dados['instituicao']));
        $dados['semestre_atuacao'] = htmlspecialchars(strip_tags($dados['semestre_atuacao']));
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":email", $dados['email']);
        $stmt->bindParam(":telefone", $dados['telefone']);
        $stmt->bindParam(":cpf", $dados['cpf']);
        $stmt->bindParam(":tipo", $dados['tipo']);
        $stmt->bindParam(":instituicao", $dados['instituicao']);
        $stmt->bindParam(":semestre_atuacao", $dados['semestre_atuacao']);
        
        return $stmt->execute();
    }

    // Listar todos os inscritos de um curso
    public function listarPorCurso($id_curso) {
        $query = "SELECT i.*, ins.data_inscricao, ins.status 
                  FROM " . $this->tabela . " i 
                  INNER JOIN inscricoes ins ON i.id = ins.id_inscrito 
                  WHERE ins.id_curso = :id_curso 
                  ORDER BY i.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $id_curso);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
