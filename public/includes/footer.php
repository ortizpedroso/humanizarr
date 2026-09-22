<?php
/**
 * FOOTER DINÂMICO - INSTITUTO HUMANIZA RR
 * Localização: src/public/includes/footer.php
 */

// Tenta identificar qual variável de conexão está ativa para evitar erros
$conexao = isset($db) ? $db : (isset($db_h) ? $db_h : null);

try {
    $inf = [];
    if ($conexao) {
        // Busca configurações gerais
        $stmt_f = $conexao->query("SELECT chave, valor FROM configuracoes");
        while($row = $stmt_f->fetch(PDO::FETCH_ASSOC)) { 
            $inf[$row['chave']] = $row['valor']; 
        }
    }
} catch (Exception $e) { 
    $inf = []; 
}
?>

<footer style="background-color: #1a1a1a; color: #fff; padding-top: 70px; position: relative;">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4" style="color: var(--h-red); text-transform: none;">Instituto Humaniza RR</h5>
                <p style="font-size: 0.95rem; color: #aaa; line-height: 1.7; text-align: justify;">
                    Transformando realidades e levando esperança através de projetos sociais em todo o estado de Roraima. Nossa missão é promover a dignidade humana.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <?php if(!empty($inf['instagram'])): ?><a href="<?= $inf['instagram'] ?>" target="_blank" class="text-white fs-5"><i class="bi bi-instagram"></i></a><?php endif; ?>
                    <?php if(!empty($inf['facebook'])): ?><a href="<?= $inf['facebook'] ?>" target="_blank" class="text-white fs-5"><i class="bi bi-facebook"></i></a><?php endif; ?>
                    <?php if(!empty($inf['youtube'])): ?><a href="<?= $inf['youtube'] ?>" target="_blank" class="text-white fs-5"><i class="bi bi-youtube"></i></a><?php endif; ?>
                    <?php if(!empty($inf['whatsapp'])): ?><a href="https://wa.me/<?= preg_replace('/\D/', '', $inf['whatsapp']) ?>" target="_blank" class="text-white fs-5"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
                </div>
            </div>

            <div class="col-lg-2 ms-auto">
                <h5 class="fw-bold mb-4" style="text-transform: none;">Links rápidos</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Início</a></li>
                    <li class="mb-2"><a href="sobre.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Sobre</a></li>
                    <li class="mb-2"><a href="galeria_completa.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Galeria</a></li>
                    
                    <?php
                    // Páginas Dinâmicas
                    try {
                        if($conexao){
                            $paginas_f = $conexao->query("SELECT titulo, slug FROM paginas WHERE no_menu = 1 ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($paginas_f as $pf):
                    ?>
                        <li class="mb-2">
                            <a href="pagina.php?slug=<?= $pf['slug'] ?>" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">
                                <?= htmlspecialchars($pf['titulo']) ?>
                            </a>
                        </li>
                    <?php endforeach; } } catch(Exception $e) {} ?>

                    <li class="mb-2"><a href="projetos.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Projetos</a></li>
                    <li class="mb-2"><a href="noticias.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Notícias</a></li>
                    <li class="mb-2"><a href="contato.php" class="text-decoration-none" style="color:#aaa; font-size: 0.95rem;">Contato</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h5 class="fw-bold mb-4" style="text-transform: none;">Informações de contato</h5>
                <ul class="list-unstyled" style="font-size: 0.95rem; color: #aaa;">
                    <li class="mb-3 d-flex align-items-start"><i class="bi bi-geo-alt-fill text-danger me-3 mt-1"></i><span><?= htmlspecialchars($inf['endereco'] ?? 'Boa Vista - RR') ?></span></li>
                    <li class="mb-3 d-flex align-items-center"><i class="bi bi-envelope-fill text-danger me-3"></i><span><?= htmlspecialchars($inf['email'] ?? 'contato@humanizarr.org') ?></span></li>
                    <li class="mb-3 d-flex align-items-center"><i class="bi bi-whatsapp text-danger me-3"></i><span><?= htmlspecialchars($inf['whatsapp'] ?? '') ?></span></li>
                    
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-envelope-arrow-up-fill text-danger me-3"></i>
                        <a href="https://mail.hostinger.com/v2/auth/login?p=1" target="_blank" class="text-decoration-none" style="color: #aaa;">
                            Webmail
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-5 py-3 text-center" style="background-color: var(--h-red); color: #fff;">
        <div class="container">
            <p class="mb-0 opacity-75" style="font-size: 0.85rem; text-transform: none; font-weight: 400;">
                &copy; <?= date('Y') ?> Instituto Humaniza RR - Todos os direitos reservados.
            </p>
        </div>
    </div>

    <a href="#" id="backToTop" class="shadow-lg"><i class="bi bi-arrow-up"></i></a>
</footer>

<style>
    #backToTop {
        position: fixed; bottom: 30px; right: 30px; width: 45px; height: 45px;
        background: var(--h-red); color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none; border: 2px solid #fff;
        visibility: hidden; opacity: 0; transition: 0.4s; z-index: 9999;
    }
    #backToTop.show { visibility: visible; opacity: 1; }
    #backToTop:hover { background: #fff; color: var(--h-red); transform: translateY(-5px); }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    const btnTopo = document.getElementById('backToTop');
    window.onscroll = function() {
        if (document.body.scrollTop > 400 || document.documentElement.scrollTop > 400) {
            btnTopo.classList.add("show");
        } else {
            btnTopo.classList.remove("show");
        }
    };
    btnTopo.onclick = function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
</script>