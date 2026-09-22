<?php
/**
 * DETALHE DO PROJETO - INSTITUTO HUMANIZA RR
 * Localização: src/detalhe_projeto.php
 * Versão Final: Layout em Duas Colunas (Imagem/Ajuda | Texto Justificado)
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header("Location: projetos.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM projetos WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    header("Location: projetos.php");
    exit;
}

include __DIR__ . '/public/includes/header.php';
?>

<style>
    .projeto-capa {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .sidebar-ajuda {
        background-color: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-top: 4px solid var(--h-red);
        position: sticky;
        top: 100px; /* Faz a sidebar acompanhar o scroll */
    }

    .content-text-justified {
        text-align: justify;
        line-height: 1.9;
        color: #333;
        font-size: 1.1rem;
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .badge-projeto {
        background-color: var(--h-red);
        color: white;
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    @media (max-width: 991px) {
        .sidebar-ajuda { position: static; margin-top: 30px; }
        .content-text-justified { padding: 25px; text-align: left; }
    }
</style>

<article class="bg-light pb-5">
    <div class="container py-5">
        
        <nav aria-label="breadcrumb" class="mb-5">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-danger text-decoration-none fw-bold small">Início</a></li>
                <li class="breadcrumb-item"><a href="projetos.php" class="text-danger text-decoration-none fw-bold small">Projetos</a></li>
                <li class="breadcrumb-item active fw-semibold small" aria-current="page"><?= htmlspecialchars($projeto['titulo']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <aside class="col-lg-4 order-2 order-lg-1">
                <img src="public/uploads/<?= htmlspecialchars($projeto['imagem']) ?>" 
                     class="projeto-capa img-fluid" 
                     alt="<?= htmlspecialchars($projeto['titulo']) ?>">

                <div class="sidebar-ajuda">
                    <h5 class="fw-bold text-dark mb-3">Como você ajuda?</h5>
                    <p class="text-muted small mb-4">
                        Ao apoiar o <strong><?= htmlspecialchars($projeto['titulo']) ?></strong>, você garante que as ações do Instituto Humaniza cheguem a quem mais precisa em Roraima.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="doe-aqui.php" class="btn btn-danger fw-bold rounded-pill py-2 shadow-sm">
                            <i class="bi bi-heart-fill me-2"></i> DOAR AGORA
                        </a>
                        <a href="contato.php" class="btn btn-outline-dark fw-bold rounded-pill py-2">
                            <i class="bi bi-chat-dots me-2"></i> SER VOLUNTÁRIO
                        </a>
                    </div>
                    <hr class="my-4 opacity-10">
                    <p class="small text-center text-muted mb-0">Compartilhe esta causa:</p>
                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <a href="#" class="text-dark fs-5"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="text-dark fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-dark fs-5"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </aside>

            <div class="col-lg-8 order-1 order-lg-2">
                <div class="mb-4">
                    <span class="badge-projeto text-uppercase mb-2 d-inline-block">Impacto Social</span>
                    <h1 class="display-4 fw-bold text-dark mb-3"><?= htmlspecialchars($projeto['titulo']) ?></h1>
                </div>

                <div class="content-text-justified">
                    <?php 
                        // LIMPEZA DOS CARACTERES ESPECIAIS (&#13;&#10;)
                        $texto = html_entity_decode($projeto['descricao'], ENT_QUOTES, 'UTF-8');
                        echo nl2br($texto); 
                    ?>
                </div>

                <div class="mt-5">
                    <a href="projetos.php" class="btn btn-link text-dark text-decoration-none fw-bold p-0">
                        <i class="bi bi-arrow-left me-2"></i> Voltar para todos os projetos
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>

<?php include __DIR__ . '/public/includes/footer.php'; ?>