<?php
/**
 * PÁGINA DE LISTAGEM DE NOTÍCIAS - INSTITUTO HUMANIZA RR
 * Localização: src/noticias.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Busca TODAS as Notícias Ativas ordenadas pela data mais recente
try {
    $noticias = $db->query("SELECT * FROM noticias WHERE status = 'ativo' ORDER BY data_publicacao DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $noticias = [];
}

// 2. Busca Redes Sociais para o Footer (Garante que os links do rodapé funcionem)
try {
    $redes_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
    $redes = [];
    foreach($redes_raw as $r) { 
        $redes[$r['chave']] = $r['valor']; 
    }
} catch(Exception $e) {
    $redes = [];
}

// INCLUIR HEADER PROFISSIONAL
include __DIR__ . '/public/includes/header.php';
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red, #E30613);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2" style="font-family: 'Montserrat', sans-serif;">Portal de Notícias</h1>
        <p class="text-white-50 mb-0 fs-5">Fique por dentro de todas as ações e novidades do Instituto Humaniza RR</p>
    </div>
</div>

<section class="bg-light min-vh-100 py-5">
    <div class="container">
        <div class="row g-4">
            <?php if(!empty($noticias)): ?>
                <?php foreach($noticias as $n): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white border-hover">
                        <div class="position-relative">
                            <?php 
                                // Verifica qual campo de imagem está preenchido
                                $imagem = !empty($n['imagem']) ? $n['imagem'] : ($n['imagem_destaque'] ?? ''); 
                            ?>
                            <img src="public/uploads/<?= htmlspecialchars($imagem ?? '') ?>" 
                                 class="card-img-top" 
                                 style="height:240px; object-fit:cover;" 
                                 alt="<?= htmlspecialchars($n['titulo'] ?? 'Notícia') ?>">
                            
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">
                                    <?= date('d/m/Y', strtotime($n['data_publicacao'])) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="fw-bold mb-3 h5" style="color: var(--h-dark, #2D2D2D); font-family: 'Montserrat', sans-serif;">
                                <?= htmlspecialchars($n['titulo'] ?? '') ?>
                            </h4>
                            <p class="text-muted small flex-grow-1" style="text-align: justify;">
                                <?= mb_strimwidth(strip_tags($n['conteudo'] ?? ''), 0, 160, "...") ?>
                            </p>
                            
                            <div class="mt-4 border-top pt-3">
                                <a href="detalhe_noticia.php?id=<?= $n['id'] ?>" class="btn btn-danger w-100 rounded-pill fw-bold py-2 shadow-sm text-uppercase">
                                    LER MATÉRIA COMPLETA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-newspaper display-1 text-muted mb-3"></i>
                        <h3 class="fw-bold">Nenhuma notícia encontrada</h3>
                        <p class="text-muted">No momento, não existem publicações ativas no nosso portal.</p>
                        <a href="index.php" class="btn btn-danger px-5 rounded-pill mt-3 fw-bold">Voltar ao Início</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .border-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .border-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
</style>

<?php 
// INCLUIR FOOTER PROFISSIONAL
include __DIR__ . '/public/includes/footer.php'; 
?>