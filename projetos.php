<?php
/**
 * LISTAGEM DE PROJETOS - INSTITUTO HUMANIZA RR
 * Localização: src/projetos.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Busca TODOS os projetos ativos
$projetos = $db->query("SELECT * FROM projetos WHERE status = 'ativo' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Busca Redes Sociais para o Footer
try {
    $redes_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
    $redes = [];
    foreach($redes_raw as $r) { $redes[$r['chave']] = $r['valor']; }
} catch(Exception $e) { $redes = []; }

include __DIR__ . '/public/includes/header.php';
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2">Nossos Projetos</h1>
        <p class="text-white-50 mb-0 fs-5">Ações que transformam vidas em todo o estado de Roraima</p>
    </div>
</div>

<section class="bg-light min-vh-100">
    <div class="container">
        <div class="row g-4">
            <?php if(!empty($projetos)): ?>
                <?php foreach($projetos as $p): ?>
                <div class="col-md-4">
                    <div class="card card-projeto h-100">
                        <img src="public/uploads/<?= htmlspecialchars($p['imagem']) ?>" class="card-img-top" style="height:250px; object-fit:cover;">
                        <div class="card-body d-flex flex-column">
                            <h4 class="fw-bold mb-3"><?= htmlspecialchars($p['titulo'] ?? $p['nome']) ?></h4>
                            <p class="text-muted small flex-grow-1">
                                <?= mb_strimwidth(strip_tags($p['descricao']), 0, 150, "...") ?>
                            </p>
                            <a href="detalhe_projeto.php?id=<?= $p['id'] ?>" class="btn btn-danger w-100 rounded-pill fw-bold mt-3 shadow-sm">
                                CONHECER PROJETO
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="lead">Nenhum projeto encontrado no momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/public/includes/footer.php'; ?>