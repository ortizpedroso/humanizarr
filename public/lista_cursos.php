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

// Busca todos os cursos
$cursos = $curso->lerTodos();

// Mensagens de feedback
$mensagem = $_SESSION['msg_curso'] ?? '';
$tipo_mensagem = $_SESSION['tipo_msg_curso'] ?? '';
unset($_SESSION['msg_curso'], $_SESSION['tipo_msg_curso']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos e Palestras - Humaniza RR</title>
    
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
        .card-curso {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card-curso:hover {
            transform: translateY(-2px);
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
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
                        <h2 class="fw-bold mb-1"><i class="bi bi-mortarboard me-2"></i>Cursos e Palestras</h2>
                        <p class="text-muted mb-0">Gerencie todos os cursos e palestras da campanha</p>
                    </div>
                    <a href="criar_curso.php" class="btn btn-danger px-4 py-2">
                        <i class="bi bi-plus-circle me-2"></i>Novo Curso
                    </a>
                </div>

                <!-- Alertas -->
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $tipo_mensagem == 'sucesso' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $tipo_mensagem == 'sucesso' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                        <?= htmlspecialchars($mensagem) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-curso bg-primary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total de Cursos</h6>
                                    <h3 class="mb-0 fw-bold"><?= $cursos->rowCount() ?></h3>
                                </div>
                                <i class="bi bi-mortarboard-fill fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $cursosAbertos = 0;
                        $cursos->execute();
                        while($c = $cursos->fetch(PDO::FETCH_ASSOC)) {
                            if($c['status'] == 'Aberto') $cursosAbertos++;
                        }
                        ?>
                        <div class="card card-curso bg-success text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Cursos Abertos</h6>
                                    <h3 class="mb-0 fw-bold"><?= $cursosAbertos ?></h3>
                                </div>
                                <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-curso bg-warning text-dark p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Em Rascunho</h6>
                                    <h3 class="mb-0 fw-bold"><?= $cursos->rowCount() - $cursosAbertos ?></h3>
                                </div>
                                <i class="bi bi-pencil-fill fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Cursos -->
                <div class="card card-curso p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome do Curso/Palestra</th>
                                    <th>Data/Local</th>
                                    <th>Palestrante</th>
                                    <th>Status</th>
                                    <th>Inscritos</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cursos->execute();
                                while($curso_item = $cursos->fetch(PDO::FETCH_ASSOC)): 
                                    // Define cor do badge
                                    $badgeClass = 'secondary';
                                    if($curso_item['status'] == 'Aberto') $badgeClass = 'success';
                                    elseif($curso_item['status'] == 'Rascunho') $badgeClass = 'warning';
                                    
                                    // Conta inscrições
                                    $total_inscritos = $curso->contarInscricoes($curso_item['id']);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($curso_item['nome']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($curso_item['duracao']) ?></small>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($curso_item['data_evento'])) ?><br>
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($curso_item['local']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($curso_item['palestrante']) ?></td>
                                    <td>
                                        <span class="badge badge-status bg-<?= $badgeClass ?>">
                                            <?= htmlspecialchars($curso_item['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-people me-1"></i><?= $total_inscritos ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="toggle_status.php?id=<?= $curso_item['id'] ?>" 
                                           class="btn btn-sm btn-outline-<?= $curso_item['status'] == 'Aberto' ? 'warning' : 'success' ?> btn-action" 
                                           title="<?= $curso_item['status'] == 'Aberto' ? 'Pausar' : 'Ativar' ?>">
                                            <i class="bi bi-<?= $curso_item['status'] == 'Aberto' ? 'pause' : 'play' ?>-fill"></i>
                                        </a>
                                        <a href="inscricao.php?id=<?= $curso_item['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary btn-action" 
                                           title="Ver Link de Inscrição" target="_blank">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <a href="excluir_curso.php?id=<?= $curso_item['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger btn-action" 
                                           title="Excluir"
                                           onclick="return confirm('Tem certeza que deseja excluir este curso? Todas as inscrições serão apagadas!')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if($cursos->rowCount() == 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Nenhum curso cadastrado ainda.</p>
                            <a href="criar_curso.php" class="btn btn-danger btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>Criar Primeiro Curso
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
