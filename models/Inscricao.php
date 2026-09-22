<?php
class Inscricao {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Processa uma nova inscrição
     * 1. Verifica se o e-mail já existe na tabela 'inscritos'
     * 2. Se existir, reutiliza o ID; se não, cria novo inscrito
     * 3. Cria o vínculo na tabela 'inscricoes'
     * 
     * @param array $dados Dados do formulário (nome, email, telefone, tipo, instituicao, semestre_atuacao)
     * @param int $id_curso ID do curso
     * @return bool true em caso de sucesso, false em caso de erro
     */
    public function processarInscricao($dados, $id_curso) {
        try {
            // Inicia transação
            $this->conn->beginTransaction();

            // Sanitização dos dados
            $dados['nome'] = htmlspecialchars(strip_tags($dados['nome']));
            $dados['email'] = filter_var(trim($dados['email']), FILTER_SANITIZE_EMAIL);
            $dados['telefone'] = htmlspecialchars(strip_tags($dados['telefone']));
            $dados['tipo'] = htmlspecialchars(strip_tags($dados['tipo']));
            $dados['instituicao'] = htmlspecialchars(strip_tags($dados['instituicao']));
            $dados['semestre_atuacao'] = htmlspecialchars(strip_tags($dados['semestre_atuacao']));

            // 1. Verifica se e-mail já existe
            $query_check = "SELECT id FROM inscritos WHERE email = :email";
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bindParam(":email", $dados['email']);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                // E-mail já existe, pega o ID existente
                $inscrito_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $id_inscrito = $inscrito_existente['id'];
                
                // Atualiza dados do inscrito caso tenham mudado
                $query_update = "UPDATE inscritos 
                                 SET nome = :nome, telefone = :telefone, tipo = :tipo, 
                                     instituicao = :instituicao, semestre_atuacao = :semestre_atuacao 
                                 WHERE id = :id";
                $stmt_update = $this->conn->prepare($query_update);
                $stmt_update->bindParam(":nome", $dados['nome']);
                $stmt_update->bindParam(":telefone", $dados['telefone']);
                $stmt_update->bindParam(":tipo", $dados['tipo']);
                $stmt_update->bindParam(":instituicao", $dados['instituicao']);
                $stmt_update->bindParam(":semestre_atuacao", $dados['semestre_atuacao']);
                $stmt_update->bindParam(":id", $id_inscrito);
                $stmt_update->execute();
            } else {
                // E-mail não existe, cria novo inscrito
                $query_insert_inscrito = "INSERT INTO inscritos 
                                          (nome, email, telefone, tipo, instituicao, semestre_atuacao) 
                                          VALUES (:nome, :email, :telefone, :tipo, :instituicao, :semestre_atuacao)";
                $stmt_insert_inscrito = $this->conn->prepare($query_insert_inscrito);
                $stmt_insert_inscrito->bindParam(":nome", $dados['nome']);
                $stmt_insert_inscrito->bindParam(":email", $dados['email']);
                $stmt_insert_inscrito->bindParam(":telefone", $dados['telefone']);
                $stmt_insert_inscrito->bindParam(":tipo", $dados['tipo']);
                $stmt_insert_inscrito->bindParam(":instituicao", $dados['instituicao']);
                $stmt_insert_inscrito->bindParam(":semestre_atuacao", $dados['semestre_atuacao']);
                $stmt_insert_inscrito->execute();
                
                $id_inscrito = $this->conn->lastInsertId();
            }

            // 2. Verifica se já existe inscrição para este curso
            $query_check_inscricao = "SELECT id FROM inscricoes WHERE id_curso = :id_curso AND id_inscrito = :id_inscrito";
            $stmt_check_inscricao = $this->conn->prepare($query_check_inscricao);
            $stmt_check_inscricao->bindParam(":id_curso", $id_curso);
            $stmt_check_inscricao->bindParam(":id_inscrito", $id_inscrito);
            $stmt_check_inscricao->execute();

            if ($stmt_check_inscricao->rowCount() > 0) {
                // Já está inscrito, rollback e retorna erro
                $this->conn->rollBack();
                return false;
            }

            // 3. Cria a inscrição
            $query_insert_inscricao = "INSERT INTO inscricoes (id_curso, id_inscrito) VALUES (:id_curso, :id_inscrito)";
            $stmt_insert_inscricao = $this->conn->prepare($query_insert_inscricao);
            $stmt_insert_inscricao->bindParam(":id_curso", $id_curso);
            $stmt_insert_inscricao->bindParam(":id_inscrito", $id_inscrito);
            $stmt_insert_inscricao->execute();

            // Commit da transação
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Erro, faz rollback
            $this->conn->rollBack();
            // Em produção, logar o erro em arquivo ao invés de exibir
            error_log("Erro ao processar inscrição: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todas as inscrições de um curso específico
     * @param int $id_curso ID do curso
     * @return PDOStatement Resultado com dados dos inscritos
     */
    public function listarPorCurso($id_curso) {
        $query = "SELECT i.nome, i.email, i.telefone, i.tipo, i.instituicao, i.semestre_atuacao, 
                         insc.data_inscricao, insc.status
                  FROM inscricoes insc
                  INNER JOIN inscritos i ON insc.id_inscrito = i.id
                  WHERE insc.id_curso = :id_curso
                  ORDER BY insc.data_inscricao DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $id_curso);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Conta total de inscrições de um curso
     * @param int $id_curso ID do curso
     * @return int Total de inscrições
     */
    public function contarPorCurso($id_curso) {
        $query = "SELECT COUNT(*) as total FROM inscricoes WHERE id_curso = :id_curso";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $id_curso);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
}
?>
