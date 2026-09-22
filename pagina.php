<?php
/**
 * EXIBIÇÃO DE PÁGINAS DINÂMICAS - INSTITUTO HUMANIZA RR
 * Localização: src/pagina.php
 */
require_once __DIR__ . '/config/Database.php';
$database = new Database();
$db = $database->getConnection();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// 1. Busca os dados básicos da página
$stmt = $db->prepare("SELECT * FROM paginas WHERE slug = :slug LIMIT 1");
$stmt->bindParam(':slug', $slug);
$stmt->execute();
$pagina = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pagina) {
    header("Location: index.php");
    exit;
}

// 2. Busca todas as seções desta página
$stmt_sec = $db->prepare("SELECT * FROM secoes WHERE pagina_id = :id ORDER BY ordem ASC, id ASC");
$stmt_sec->bindParam(':id', $pagina['id']);
$stmt_sec->execute();
$secoes = $stmt_sec->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/public/includes/header.php';
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red, #E30613);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-0" style="font-family: 'Montserrat', sans-serif;">
            <?= htmlspecialchars($pagina['titulo']) ?>
        </h1>
    </div>
</div>

<main class="min-vh-100 bg-white">
    <?php foreach ($secoes as $s): ?>
        <section class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    
                    <?php if ($s['tipo'] == 'texto'): ?>
                        <div class="col-12">
                            <div class="fs-5 text-muted" style="text-align: justify; line-height: 1.8;">
                                <?= nl2br(html_entity_decode($s['texto'] ?? '')) ?>
                            </div>
                        </div>

                    <?php elseif ($s['tipo'] == 'imagem'): ?>
                        <div class="col-12 text-center">
                            <img src="public/uploads/<?= htmlspecialchars($s['imagem'] ?? '') ?>" class="img-fluid rounded-4 shadow">
                        </div>

                    <?php elseif ($s['tipo'] == 'texto_imagem'): ?>
                        <?php if ($s['posicao_texto'] == 'esquerda'): ?>
                            <div class="col-md-6 mb-4 mb-md-0">
                                <div class="fs-5 text-muted" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(html_entity_decode($s['texto'] ?? '')) ?>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <img src="public/uploads/<?= htmlspecialchars($s['imagem'] ?? '') ?>" class="img-fluid rounded-4 shadow">
                            </div>
                        <?php elseif ($s['posicao_texto'] == 'direita'): ?>
                            <div class="col-md-6 text-center mb-4 mb-md-0">
                                <img src="public/uploads/<?= htmlspecialchars($s['imagem'] ?? '') ?>" class="img-fluid rounded-4 shadow">
                            </div>
                            <div class="col-md-6">
                                <div class="fs-5 text-muted" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(html_entity_decode($s['texto'] ?? '')) ?>
                                </div>
                            </div>
                        <?php elseif ($s['posicao_texto'] == 'cima'): ?>
                            <div class="col-12 mb-4">
                                <div class="fs-5 text-muted" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(html_entity_decode($s['texto'] ?? '')) ?>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <img src="public/uploads/<?= htmlspecialchars($s['imagem'] ?? '') ?>" class="img-fluid rounded-4 shadow">
                            </div>
                        <?php elseif ($s['posicao_texto'] == 'baixo'): ?>
                            <div class="col-12 text-center mb-4">
                                <img src="public/uploads/<?= htmlspecialchars($s['imagem'] ?? '') ?>" class="img-fluid rounded-4 shadow">
                            </div>
                            <div class="col-12">
                                <div class="fs-5 text-muted" style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(html_entity_decode($s['texto'] ?? '')) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </section>
        <hr class="container my-0" style="opacity: 0.05;">
    <?php endforeach; ?>
</main>

<?php include __DIR__ . '/public/includes/footer.php'; ?>