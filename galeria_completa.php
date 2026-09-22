<?php
/**
 * PÁGINA PÚBLICA DE GALERIA - INSTITUTO HUMANIZA RR
 * Localização: src/galeria_completa.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Busca todas as fotos da galeria
try {
    $fotos = $db->query("SELECT * FROM galeria ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $fotos = [];
}

include __DIR__ . '/public/includes/header.php';
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
    :root { --h-red: #E30613; }
    
    /* Grid de 4 Colunas */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* 4 colunas iguais */
        gap: 20px;
    }

    /* Ajustes Responsivos */
    @media (max-width: 992px) { .gallery-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .gallery-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .gallery-grid { grid-template-columns: repeat(1, 1fr); } }

    /* Efeito nos Cards */
    .gallery-card {
        border-radius: 15px;
        overflow: hidden;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.4s ease;
        background: #fff;
    }

    .gallery-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .img-container {
        position: relative;
        overflow: hidden;
        height: 250px;
    }

    .img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .gallery-card:hover .img-container img {
        transform: scale(1.1);
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(227, 6, 19, 0.7); /* Vermelho do Instituto com transparência */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .gallery-card:hover .overlay {
        opacity: 1;
    }

    .overlay i {
        color: white;
        font-size: 2rem;
    }

    .caption {
        padding: 15px;
        text-align: center;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
</style>

<div class="py-5" style="background-color: var(--h-red);">
    <div class="container text-center py-4" data-aos="fade-up">
        <h1 class="display-4 fw-bold text-white mb-2">Galeria de Eventos</h1>
        <p class="text-white-50 mb-0 fs-5">Acompanhe de perto as ações do Instituto Humaniza RR</p>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <?php if(!empty($fotos)): ?>
            <div class="gallery-grid">
                <?php 
                $delay = 100; // Delay inicial para a animação
                foreach($fotos as $f): 
                ?>
                    <div class="gallery-card" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                        <div class="img-container">
                            <img src="public/uploads/<?= $f['imagem'] ?>" alt="<?= htmlspecialchars($f['legenda']) ?>">
                            <div class="overlay">
                                <i class="bi bi-search"></i>
                            </div>
                        </div>
                        <div class="caption">
                            <?= $f['legenda'] ?: 'Ação Social' ?>
                        </div>
                    </div>
                <?php 
                    $delay += 50; // Aumenta o delay de cada foto para efeito cascata
                    if($delay > 400) $delay = 100; // Reseta para não demorar demais nas fotos de baixo
                endforeach; 
                ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5" data-aos="zoom-in">
                <i class="bi bi-images display-1 text-muted opacity-25"></i>
                <p class="mt-3 fs-5 text-muted">Nossa galeria está sendo preparada. Volte em breve!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800, // Duração da animação em milisegundos
        once: true     // Anima apenas uma vez ao rolar
    });
</script>

<?php include __DIR__ . '/public/includes/footer.php'; ?>