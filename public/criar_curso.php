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

$erro = '';
$sucesso = '';

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação básica
    if (empty($_POST['nome']) || empty($_POST['data_evento']) || empty($_POST['local'])) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        // Prepara dados
        $dados = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? '',
            'duracao' => $_POST['duracao'],
            'local' => $_POST['local'],
            'palestrante' => $_POST['palestrante'],
            'presidente' => $_POST['presidente'],
            'vice_presidente' => $_POST['vice_presidente'],
            'data_evento' => $_POST['data_evento'],
            'status' => $_POST['status']
        ];

        // Tenta criar o curso
        if ($curso->criar($dados)) {
            $_SESSION['msg_curso'] = "Curso/Palestra criado com sucesso!";
            $_SESSION['tipo_msg_curso'] = 'sucesso';
            header("Location: lista_cursos.php");
            exit;
        } else {
            $erro = "Erro ao criar curso. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Curso - Humaniza RR</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #2D2D2D;
            min-height: 100vh;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .main-content {
            padding: 30px;
        }
        .card-form {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-salvar {
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Conteúdo Principal -->
            <main class="col-md-10 ms-sm-auto col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="bi bi-plus-circle me-2"></i>Criar Novo Curso/Palestra</h2>
                        <p class="text-muted mb-0">Preencha as informações abaixo para cadastrar um novo evento</p>
                    </div>
                    <a href="lista_cursos.php" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-left me-2"></i>Voltar para Lista
                    </a>
                </div>

                <!-- Alertas -->
                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($erro) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <div class="card card-form p-4">
                    <form method="POST" action="">
                        <div class="row">
                            <!-- Nome do Evento -->
                            <div class="col-md-12 mb-3">
                                <label for="nome" class="form-label">Nome do Evento *</label>
                                <input type="text" class="form-control form-control-lg" id="nome" name="nome" 
                                       placeholder="Ex: Curso de Primeiros Socorros" required>
                            </div>

                            <!-- Descrição -->
                            <div class="col-md-12 mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                          placeholder="Breve descrição do evento..."></textarea>
                            </div>

                            <!-- Duração e Data -->
                            <div class="col-md-4 mb-3">
                                <label for="duracao" class="form-label">Duração *</label>
                                <input type="text" class="form-control" id="duracao" name="duracao" 
                                       placeholder="Ex: 4 horas" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="data_evento" class="form-label">Data e Horário *</label>
                                <input type="datetime-local" class="form-control" id="data_evento" name="data_evento" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status Inicial *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Rascunho">Rascunho (Não visível)</option>
                                    <option value="Aberto">Aberto (Inscrições liberadas)</option>
                                </select>
                            </div>

                            <!-- Local -->
                            <div class="col-md-8 mb-3">
                                <label for="local" class="form-label">Local *</label>
                                <input type="text" class="form-control" id="local" name="local" 
                                       placeholder="Ex: Auditório Principal - Hospital Geral" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="palestrante" class="form-label">Palestrante *</label>
                                <input type="text" class="form-control" id="palestrante" name="palestrante" 
                                       placeholder="Nome do palestrante" required>
                            </div>

                            <!-- Presidente e Vice -->
                            <div class="col-md-6 mb-3">
                                <label for="presidente" class="form-label">Presidente *</label>
                                <input type="text" class="form-control" id="presidente" name="presidente" 
                                       placeholder="Nome do presidente" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vice_presidente" class="form-label">Vice-Presidente *</label>
                                <input type="text" class="form-control" id="vice_presidente" name="vice_presidente" 
                                       placeholder="Nome do vice-presidente" required>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-danger btn-salvar">
                                <i class="bi bi-check-circle me-2"></i>Salvar e Criar
                            </button>
                            <a href="lista_cursos.php" class="btn btn-outline-secondary btn-salvar">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Informações Importantes -->
                <div class="alert alert-info mt-4" role="alert">
                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informações Importantes:</h6>
                    <ul class="mb-0">
                        <li>O status <strong>"Aberto"</strong> libera o link de inscrição para o público.</li>
                        <li>O status <strong>"Rascunho"</strong> mantém o curso invisível para inscrições.</li>
                        <li>Você pode alterar o status a qualquer momento na lista de cursos.</li>
                        <li>A exclusão de um curso remove automaticamente todas as inscrições associadas.</li>
                    </ul>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
