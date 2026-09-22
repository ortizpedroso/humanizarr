<?php
/**
 * PÁGINA SOBRE NÓS - INSTITUTO HUMANIZA RR
 * Localização: src/sobre.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Busca Configurações da Seção Institucional (para pegar a imagem cadastrada)
try {
    $stmt_inst = $db->prepare("SELECT valor, imagem FROM configuracoes WHERE chave = 'institucional' LIMIT 1");
    $stmt_inst->execute();
    $inst = $stmt_inst->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) { $inst = null; }

// 2. Busca Redes Sociais para o Footer
try {
    $redes_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
    $redes = [];
    foreach($redes_raw as $r) { $redes[$r['chave']] = $r['valor']; }
} catch(Exception $e) { $redes = []; }

include __DIR__ . '/public/includes/header.php';
?>

<style>
    /* Padronização de Título igual à Index */
    .section-title-sobre {
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 800 !important;
        color: var(--h-red) !important;
        text-transform: none !important;
        margin-bottom: 30px;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
    }
    .section-title-sobre::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 4px;
        background: var(--h-red);
        bottom: 0;
        left: 0;
    }
</style>

<div class="py-5 shadow-sm" style="background-color: var(--h-red);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2">Sobre o Instituto</h1>
        <p class="text-white-50 mb-0 fs-5">Conheça nossa trajetória e nosso compromisso com Roraima</p>
    </div>
</div>

<section class="bg-white py-5">
    <div class="container py-4">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="section-title-sobre">Nossa História</h2>
                <div class="fs-5 text-muted lh-lg" style="text-align: justify; font-family: 'Montserrat', sans-serif;">
                    <p>O <strong>Instituto Humaniza RR</strong> nasceu do desejo de transformar a realidade das famílias em situação de vulnerabilidade no estado de Roraima. O que começou como um pequeno grupo de voluntários, hoje se tornou uma instituição sólida e respeitada.</p>
                    
                    <p>Atuamos em diversas frentes, desde o suporte básico com alimentação e vestuário, até programas complexos de educação profissional e assistência à saúde especializada.</p>
                    
                    <p>Nosso trabalho é pautado na transparência, no respeito à dignidade humana e na busca constante por soluções que gerem impacto social de longo prazo.</p>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0 text-center">
                <?php if(!empty($inst['imagem'])): ?>
                    <img src="public/uploads/<?= htmlspecialchars($inst['imagem']) ?>" class="img-fluid rounded-4 shadow-lg border border-5 border-white">
                <?php else: ?>
                    <img src="https://via.placeholder.com/600x450?text=Instituto+Humaniza+RR" class="img-fluid rounded-4 shadow-lg border border-5 border-white">
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4 mt-5">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center rounded-4">
                    <div class="mb-3">
                        <i class="bi bi-bullseye display-4 text-danger"></i>
                    </div>
                    <h4 class="fw-bold">Missão</h4>
                    <p class="text-muted small">Promover a inclusão social e o desenvolvimento humano através de ações integradas de saúde, educação e assistência.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center rounded-4">
                    <div class="mb-3">
                        <i class="bi bi-eye display-4 text-danger"></i>
                    </div>
                    <h4 class="fw-bold">Visão</h4>
                    <p class="text-muted small">Ser referência em gestão de projetos sociais em Roraima, reconhecida pela eficácia e transparência de seus resultados.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center rounded-4">
                    <div class="mb-3">
                        <i class="bi bi-shield-check display-4 text-danger"></i>
                    </div>
                    <h4 class="fw-bold">Valores</h4>
                    <p class="text-muted small">Ética, Solidariedade, Compromisso Social, Transparência e Empatia em cada atendimento realizado.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/public/includes/footer.php'; ?>