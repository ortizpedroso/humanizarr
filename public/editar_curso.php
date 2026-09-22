<?php
session_start();

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/Database.php';
require_once '../models/Curso.php';

$database = new Database();
$db = $database->getConnection();
$curso = new Curso($db);

$mensagem = '';
$tipo_mensagem = '';

// Verifica se recebeu ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lista_cursos.php");
    exit;
}

$id_curso = $_GET['id'];

// Busca dados do curso
$curso_item = $curso->lerPorId($id_curso);

if (!$curso_item) {
    $_SESSION['msg_curso'] = "Curso não encontrado.";
    $_SESSION['tipo_msg_curso'] = 'erro';
    header("Location: lista_cursos.php");
    exit;
}

// Processa formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id' => $id_curso,
        'nome' => htmlspecialchars($_POST['nome']),
        'descricao' => htmlspecialchars($_POST['descricao']),
        'duracao' => htmlspecialchars($_POST['duracao']),
        'local' => htmlspecialchars($_POST['local']),
        'palestrante' => htmlspecialchars($_POST['palestrante']),
        'presidente' => htmlspecialchars($_POST['presidente']),
        'vice_presidente' => htmlspecialchars($_POST['vice_presidente']),
        'data_evento' => $_POST['data_evento'],
        'horario_inicio' => $_POST['horario_inicio'] ?? null,
        'vagas_limite' => intval($_POST['vagas_limite']),
        'status' => $_POST['status']
    ];

    // Upload de Banner (opcional)
    if (isset($_FILES['banner_imagem']) && $_FILES['banner_imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['banner_imagem']['name'], PATHINFO_EXTENSION);
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($extensao), $extensoes_permitidas)) {
            $novo_nome = 'banner_' . time() . '.' . $extensao;
            $caminho_upload = __DIR__ . '/uploads/' . $novo_nome;
            
            if (move_uploaded_file($_FILES['banner_imagem']['tmp_name'], $caminho_upload)) {
                $dados['banner_imagem'] = $novo_nome;
            }
        }
    }

    // Upload de Arte do Certificado (opcional)
    if (isset($_FILES['certificado_arquivo']) && $_FILES['certificado_arquivo']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['certificado_arquivo']['name'], PATHINFO_EXTENSION);
        $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($extensao), $extensoes_permitidas)) {
            $novo_nome = 'cert_' . time() . '.' . $extensao;
            $caminho_upload = __DIR__ . '/uploads/' . $novo_nome;
            
            if (move_uploaded_file($_FILES['certificado_arquivo']['tmp_name'], $caminho_upload)) {
                $dados['certificado_arquivo'] = $novo_nome;
            }
        }
    }

    // Checkbox de emitir certificado
    $dados['emitir_certificado'] = isset($_POST['emitir_certificado']) ? 1 : 0;

    if ($curso->editar($dados)) {
        $_SESSION['msg_curso'] = "Curso atualizado com sucesso!";
        $_SESSION['tipo_msg_curso'] = 'sucesso';
        header("Location: lista_cursos.php");
        exit;
    } else {
        $mensagem = "Erro ao atualizar curso.";
        $tipo_mensagem = 'erro';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Humaniza RR</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f9fa; }
        .sidebar { background-color: #2D2D2D; min-height: 100vh; position: sticky; top: 0; z-index: 1000; }
        .main-content { padding: 30px; }
        .card-edit { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .form-label { font-weight: 600; color: #2D2D2D; }
        .preview-imagem { max-width: 200px; margin-top: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <main class="col-md-10 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i>Editar Curso</h2>
                        <p class="text-muted mb-0">Atualize as informações do curso/palestra</p>
                    </div>
                    <a href="lista_cursos.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-2"></i>Voltar
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensagem) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-edit p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nome" class="form-label">Nome do Evento *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= htmlspecialchars($curso_item['nome']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="duracao" class="form-label">Duração *</label>
                                <input type="text" class="form-control" id="duracao" name="duracao" 
                                       value="<?= htmlspecialchars($curso_item['duracao']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição *</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?= htmlspecialchars($curso_item['descricao']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_evento" class="form-label">Data do Evento *</label>
                                <input type="datetime-local" class="form-control" id="data_evento" name="data_evento" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($curso_item['data_evento'])) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="horario_inicio" class="form-label">Horário de Início</label>
                                <input type="time" class="form-control" id="horario_inicio" name="horario_inicio" 
                                       value="<?= htmlspecialchars($curso_item['horario_inicio'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="local" class="form-label">Local *</label>
                            <input type="text" class="form-control" id="local" name="local" 
                                   value="<?= htmlspecialchars($curso_item['local']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="palestrante" class="form-label">Palestrante *</label>
                                <input type="text" class="form-control" id="palestrante" name="palestrante" 
                                       value="<?= htmlspecialchars($curso_item['palestrante']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vagas_limite" class="form-label">Limite de Vagas (0 = ilimitado)</label>
                                <input type="number" class="form-control" id="vagas_limite" name="vagas_limite" 
                                       value="<?= intval($curso_item['vagas_limite']) ?>" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="presidente" class="form-label">Presidente *</label>
                                <input type="text" class="form-control" id="presidente" name="presidente" 
                                       value="<?= htmlspecialchars($curso_item['presidente']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vice_presidente" class="form-label">Vice-Presidente *</label>
                                <input type="text" class="form-control" id="vice_presidente" name="vice_presidente" 
                                       value="<?= htmlspecialchars($curso_item['vice_presidente']) ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="banner_imagem" class="form-label">Banner do Evento</label>
                                <input type="file" class="form-control" id="banner_imagem" name="banner_imagem" accept="image/*">
                                <?php if (!empty($curso_item['banner_imagem'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($curso_item['banner_imagem']) ?>" 
                                         alt="Banner Atual" class="preview-imagem img-thumbnail">
                                    <small class="text-muted d-block mt-1">Banner atual será substituído se enviar novo.</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="certificado_arquivo" class="form-label">Arte do Certificado</label>
                                <input type="file" class="form-control" id="certificado_arquivo" name="certificado_arquivo" accept=".pdf,image/*">
                                <?php if (!empty($curso_item['certificado_arquivo'])): ?>
                                    <small class="text-success d-block mt-1">
                                        <i class="bi bi-check-circle"></i> Certificado já cadastrado
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="emitir_certificado" name="emitir_certificado" 
                                   <?= !empty($curso_item['emitir_certificado']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="emitir_certificado">
                                Habilitar emissão de certificados para os inscritos
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Rascunho" <?= $curso_item['status'] == 'Rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="Aberto" <?= $curso_item['status'] == 'Aberto' ? 'selected' : '' ?>>Aberto (Inscrições Ativas)</option>
                                <option value="Encerrado" <?= $curso_item['status'] == 'Encerrado' ? 'selected' : '' ?>>Encerrado</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-save me-2"></i>Salvar Alterações
                            </button>
                            <a href="lista_cursos.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
