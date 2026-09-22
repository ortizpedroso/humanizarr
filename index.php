<?php
/**
 * HOME - INSTITUTO HUMANIZA RR
 * Versão: 3.2 - Revisada com links .php e segurança de dados
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Busca Slides Ativos
$slides = $db->query("SELECT * FROM slides WHERE status = 'ativo' ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Busca Projetos Ativos (Últimos 3)
$projetos = $db->query("SELECT * FROM projetos WHERE status = 'ativo' ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// 3. Busca Notícias Ativas
try {
    $noticias = $db->query("SELECT * FROM noticias WHERE status = 'ativo' ORDER BY data_publicacao DESC, id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $noticias = []; }

// 4. Busca Configurações da Seção Institucional
try {
    $stmt_inst = $db->prepare("SELECT valor, imagem FROM configuracoes WHERE chave = 'institucional' LIMIT 1");
    $stmt_inst->execute();
    $inst = $stmt_inst->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) { $inst = null; }

// 5. Busca Fotos da Galeria para o Carrossel
try {
    $fotos_galeria = $db->query("SELECT * FROM galeria ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $fotos_galeria = []; }

// INCLUIR HEADER
include __DIR__ . '/public/includes/header.php';
?>

<style>
    .section-title {
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 800 !important;
        color: var(--h-red) !important;
        margin-bottom: 40px;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
        text-transform: none;
    }
    .section-title::after {
        content: ''; position: absolute; width: 60px; height: 4px;
        background: var(--h-red); bottom: 0; left: 50%; transform: translateX(-50%);
    }
    .h2-institucional::after { left: 0 !important; transform: none !important; }
    
    .card-img-top { object-fit: cover; }
    
    .carousel-item img { height: 600px; object-fit: cover; }
    .carousel-caption { 
        bottom: 10%; 
        text-shadow: 2px 2px 10px rgba(0,0,0,0.8);
    }
    @media (max-width: 768px) { .carousel-item img { height: 350px; } }
</style>

<div id="sliderH" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php if(!empty($slides)): ?>
            <?php foreach($slides as $key => $s): ?>
            <div class="carousel-item <?php echo ($key == 0 ? 'active' : ''); ?>">
                <img src="public/uploads/<?php echo $s['imagem']; ?>" class="d-block w-100" alt="Banner">
                <div class="carousel-caption d-none d-md-block text-start p-0">
                    <h2 class="display-5 fw-bold text-white"><?php echo htmlspecialchars($s['titulo']); ?></h2>
                    <p class="lead text-white"><?php echo htmlspecialchars($s['subtitulo']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="carousel-item active">
                <img src="https://via.placeholder.com/1920x600?text=Instituto+Humaniza+RR" class="d-block w-100">
            </div>
        <?php endif; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#sliderH" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#sliderH" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</div>

<section id="projetos" class="py-5">
    <div class="container text-center">
        <h2 class="section-title">Nossos Projetos</h2>
        <div class="row g-4 text-start mt-2">
            <?php foreach($projetos as $p): ?>
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                    <img src="public/uploads/<?php echo $p['imagem']; ?>" class="card-img-top" style="height: 250px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($p['titulo']); ?></h5>
                        <p class="text-muted small"><?php echo mb_strimwidth(strip_tags($p['descricao']), 0, 110, "..."); ?></p>
                        <a href="detalhe_projeto.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger px-4 rounded-pill fw-bold mt-auto">Ver Mais</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="sobre" class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start" data-aos="fade-right">
                <h2 class="section-title h2-institucional fw-bold mb-4">Conheça o Instituto</h2>
                <div style="text-align: justify; color: #555; line-height: 1.6;">
                    <?php echo nl2br(html_entity_decode($inst['valor'] ?? 'Conteúdo em atualização.')); ?>
                </div>
                <a href="sobre.php" class="btn btn-danger btn-lg px-5 shadow fw-bold mt-4">Nossa História</a>
            </div>
            <div class="col-md-6 text-center mt-4 mt-md-0" data-aos="fade-left">
                <?php if(!empty($inst['imagem'])): ?>
                    <img src="public/uploads/<?php echo $inst['imagem']; ?>" class="img-fluid rounded shadow border border-5 border-white">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container text-center">
        <h2 class="section-title">Nossas Especialidades</h2>
        <div class="row g-5 mt-2">
            <div class="col-md-4" data-aos="zoom-in">
                <i class="bi bi-heart-pulse fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Saúde</h5>
                <p class="text-muted small px-3">Suporte em atendimentos básicos e encaminhamentos médicos.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <i class="bi bi-book fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Educação</h5>
                <p class="text-muted small px-3">Reforço escolar e distribuição de material didático.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <i class="bi bi-people fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Social</h5>
                <p class="text-muted small px-3">Ações de cidadania e apoio direto às famílias.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in">
                <i class="bi bi-palette fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Cultura</h5>
                <p class="text-muted small px-3">Fomento ao acesso cultural e oficinas de artes.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <i class="bi bi-basket fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Alimentação</h5>
                <p class="text-muted small px-3">Distribuição estratégica de cestas básicas.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <i class="bi bi-mortarboard fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">Capacitação</h5>
                <p class="text-muted small px-3">Treinamento profissional para gerar autonomia.</p>
            </div>
        </div>
    </div>
</section>

<section id="noticias" class="bg-light py-5">
    <div class="container text-center">
        <h2 class="section-title">Últimas Notícias</h2>
        <div class="row g-4 text-start mt-2">
            <?php if(!empty($noticias)): ?>
                <?php foreach($noticias as $n): 
                    $img_n = !empty($n['imagem_destaque']) ? 'public/uploads/' . $n['imagem_destaque'] : 'https://via.placeholder.com/400x250?text=Sem+Imagem';
                ?>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                        <img src="<?php echo $img_n; ?>" class="card-img-top" style="height: 200px;">
                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-danger fw-bold"><?php echo date('d/m/Y', strtotime($n['data_publicacao'])); ?></small>
                            <h5 class="fw-bold mt-2"><?php echo htmlspecialchars($n['titulo']); ?></h5>
                            <p class="text-muted small"><?php echo mb_strimwidth(strip_tags($n['conteudo']), 0, 100, "..."); ?></p>
                            <a href="detalhe_noticia.php?id=<?php echo $n['id']; ?>" class="text-danger fw-bold text-decoration-none mt-auto">Ler mais <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="eventos" class="bg-white py-5">
    <div class="container text-center">
        <h2 class="section-title">Eventos Realizados</h2>
        <div id="carouselEventos" class="carousel slide mt-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php if(!empty($fotos_galeria)): 
                    $chunks = array_chunk($fotos_galeria, 4); 
                    foreach($chunks as $index => $grupo): ?>
                    <div class="carousel-item <?php echo ($index === 0 ? 'active' : ''); ?>">
                        <div class="row g-2">
                            <?php foreach($grupo as $foto): ?>
                            <div class="col-md-3 col-6">
                                <img src="public/uploads/<?php echo $foto['imagem']; ?>" class="img-fluid rounded shadow-sm" style="height: 200px; width: 100%; object-fit: cover;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselEventos" data-bs-slide="prev" style="filter: invert(1);"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselEventos" data-bs-slide="next" style="filter: invert(1);"><span class="carousel-control-next-icon"></span></button>
        </div>

        <div class="text-center mt-5">
            <a href="galeria_completa.php" class="btn btn-danger btn-lg px-5 rounded-pill shadow fw-bold">
                <i class="bi bi-images me-2"></i> Ver Galeria Completa
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/public/includes/footer.php'; ?>