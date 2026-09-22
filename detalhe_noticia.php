<?php
/**
 * DETALHE DA NOTÍCIA - INSTITUTO HUMANIZA RR
 * Localização: src/detalhe_noticia.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Captura o ID da notícia via GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: noticias.php");
    exit;
}

// Busca a notícia pelo ID
$stmt = $db->prepare("SELECT n.*, u.nome as autor_nome FROM noticias n LEFT JOIN usuarios u ON n.autor_id = u.id WHERE n.id = :id AND n.status = 'ativo' LIMIT 1");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar nada, volta para a lista de notícias
if (!$noticia) {
    header("Location: noticias.php");
    exit;
}

include __DIR__ . '/public/includes/header.php';
?>

<div class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb shadow-sm p-3 bg-white rounded">
                        <li class="breadcrumb-item"><a href="index.php" class="text-danger text-decoration-none fw-bold">Início</a></li>
                        <li class="breadcrumb-item"><a href="noticias.php" class="text-danger text-decoration-none fw-bold">Notícias</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($noticia['titulo'] ?? '') ?></li>
                    </ol>
                </nav>

                <div class="card border-0 shadow rounded-4 p-4 p-md-5 bg-white">
                    <header class="mb-5">
                        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Montserrat', sans-serif; color: #2D2D2D;">
                            <?= htmlspecialchars($noticia['titulo'] ?? '') ?>
                        </h1>
                        <div class="text-muted d-flex align-items-center flex-wrap gap-3">
                            <span><i class="bi bi-calendar3 me-2 text-danger"></i> <?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></span>
                            <span class="d-none d-md-inline">|</span>
                            <span><i class="bi bi-person me-2 text-danger"></i> Por: <?= htmlspecialchars($noticia['autor_nome'] ?? 'Equipe Humaniza') ?></span>
                        </div>
                    </header>

                    <?php 
                    $img = !empty($noticia['imagem']) ? $noticia['imagem'] : ($noticia['imagem_destaque'] ?? '');
                    if($img): 
                    ?>
                    <div class="mb-5 text-center">
                        <img src="public/uploads/<?= htmlspecialchars($img) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 550px; object-fit: cover;">
                    </div>
                    <?php endif; ?>

                    <div class="content-text fs-5" style="text-align: justify; line-height: 1.8; color: #444;">
                        <?= nl2br(html_entity_decode($noticia['conteudo'] ?? '')) ?>
                    </div>

                    <hr class="my-5" style="opacity: 0.1;">
                    
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="noticias.php" class="btn btn-outline-danger px-4 rounded-pill fw-bold">
                            <i class="bi bi-arrow-left me-2"></i> Voltar para todas as notícias
                        </a>
                        <a href="https://api.whatsapp.com/send?text=Veja esta notícia: <?= urlencode($noticia['titulo']) ?> - <?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" target="_blank" class="btn btn-success px-4 rounded-pill fw-bold">
                            <i class="bi bi-whatsapp me-2"></i> Compartilhar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/public/includes/footer.php'; ?>