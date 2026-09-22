<?php
/**
 * Dashboard Principal - Humaniza RR
 * Localização: src/public/index.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

// Proteção de acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// --- BUSCA DE ESTATÍSTICAS PARA O RESUMO ---
try {
    $total_projetos = $db->query("SELECT count(*) FROM projetos")->fetchColumn();
    $total_usuarios = $db->query("SELECT count(*) FROM usuarios")->fetchColumn();
    $total_slides   = $db->query("SELECT count(*) FROM slides")->fetchColumn();
    // Novas estatísticas adicionadas
    $total_noticias = $db->query("SELECT count(*) FROM noticias")->fetchColumn();
    $total_galeria  = $db->query("SELECT count(*) FROM galeria")->fetchColumn();
} catch (Exception $e) {
    $total_projetos = $total_projetos ?? 0;
    $total_usuarios = $total_usuarios ?? 0;
    $total_slides   = $total_slides ?? 0;
    $total_noticias = $total_noticias ?? 0;
    $total_galeria  = $total_galeria ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --humaniza-red: #E30613; --humaniza-dark: #2D2D2D; }
        body { background-color: #f8f9fa; color: #333; }
        .stat-card { 
            border: none; 
            border-radius: 10px; 
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .card-projetos { border-left: 5px solid var(--humaniza-red) !important; }
        .card-usuarios { border-left: 5px solid #2D2D2D !important; }
        .card-slides   { border-left: 5px solid #ffc107 !important; }
        .card-noticias { border-left: 5px solid #0dcaf0 !important; }
        .card-galeria  { border-left: 5px solid #6610f2 !important; }
        .icon-box { padding: 15px; border-radius: 10px; background-color: #f1f1f1; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 text-dark">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="h2 fw-bold">Resumo do Site</h1>
                <div class="text-end">
                    <span class="text-muted d-block small">Bem-vindo,</span>
                    <span class="fw-bold"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card stat-card card-projetos shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-briefcase text-danger h3 mb-0"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Projetos</h6>
                                <h2 class="mb-0 fw-bold"><?= $total_projetos ?></h2>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="projetos.php" class="small text-decoration-none text-danger fw-bold">Gerenciar <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card stat-card card-noticias shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-newspaper text-info h3 mb-0"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Notícias</h6>
                                <h2 class="mb-0 fw-bold"><?= $total_noticias ?></h2>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="noticias.php" class="small text-decoration-none text-info fw-bold">Gerenciar <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card stat-card card-galeria shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-camera text-primary h3 mb-0" style="color: #6610f2 !important;"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Fotos na Galeria</h6>
                                <h2 class="mb-0 fw-bold"><?= $total_galeria ?></h2>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="galeria.php" class="small text-decoration-none fw-bold" style="color: #6610f2;">Gerenciar <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card stat-card card-slides shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-images text-warning h3 mb-0"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Slides / Banners</h6>
                                <h2 class="mb-0 fw-bold"><?= $total_slides ?></h2>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="slides.php" class="small text-decoration-none text-warning fw-bold">Gerenciar <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card stat-card card-usuarios shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-people text-dark h3 mb-0"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Usuários</h6>
                                <h2 class="mb-0 fw-bold"><?= $total_usuarios ?></h2>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <?php if($_SESSION['usuario_nivel'] === 'admin'): ?>
                                <a href="usuarios.php" class="small text-decoration-none text-dark fw-bold">Gerenciar <i class="bi bi-arrow-right"></i></a>
                            <?php else: ?>
                                <a href="perfil.php" class="small text-decoration-none text-dark fw-bold">Meu Perfil <i class="bi bi-arrow-right"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="row mt-2">
                <div class="col-12">
                    <div class="alert alert-light border shadow-sm">
                        <i class="bi bi-info-circle me-2 text-primary"></i> 
                        Utilize o menu lateral esquerdo para navegar e atualizar os conteúdos do site oficial.
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>