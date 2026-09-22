<?php
/**
 * SIDEBAR DEFINITIVA - PAINEL ADMINISTRATIVO HUMANIZA RR
 * Localização: src/public/includes/sidebar.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// caminhos e níveis de acesso
$pagina_atual = basename($_SERVER['PHP_SELF']);
$nivel_acesso = $_SESSION['usuario_nivel'] ?? 'editor';
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse shadow" style="background-color: #2D2D2D; min-height: 100vh; position: sticky; top: 0; z-index: 9999;">
    <div class="position-sticky pt-4">
        
        <div class="text-center mb-4 px-3">
            <h4 class="fw-bold text-white mb-0" style="font-family: 'Montserrat', sans-serif; letter-spacing: -1px;">
                HUMANIZA <span style="color: #E30613;">RR</span>
            </h4>
            <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Gestão de Conteúdo</small>
        </div>

        <ul class="nav flex-column px-2">
            
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'index.php') ? 'active' : '' ?>" href="index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Início
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'perfil.php') ? 'active' : '' ?>" href="perfil.php">
                    <i class="bi bi-person-circle me-2"></i> Meu Perfil
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'slides.php') ? 'active' : '' ?>" href="slides.php">
                    <i class="bi bi-image me-2"></i> Banners (Slides)
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= (strpos($pagina_atual, 'paginas') !== false) ? 'active' : '' ?>" href="paginas_lista.php">
                    <i class="bi bi-file-earmark-richtext me-2"></i> Páginas do Site
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'projetos.php') ? 'active' : '' ?>" href="projetos.php">
                    <i class="bi bi-briefcase me-2"></i> Projetos Sociais
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'noticias.php') ? 'active' : '' ?>" href="noticias.php">
                    <i class="bi bi-newspaper me-2"></i> Notícias / Blog
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'galeria.php') ? 'active' : '' ?>" href="galeria.php">
                    <i class="bi bi-images me-2"></i> Galeria de Eventos
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link <?= (strpos($pagina_atual, 'curso') !== false || strpos($pagina_atual, 'inscricao') !== false) ? 'active' : '' ?>" href="lista_cursos.php">
                    <i class="bi bi-mortarboard me-2"></i> Cursos e Palestras
                </a>
            </li>

            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">

            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'configuracoes.php') ? 'active' : '' ?>" href="configuracoes.php">
                    <i class="bi bi-gear-fill me-2"></i> Configurações
                </a>
            </li>

            <?php if ($nivel_acesso === 'admin'): ?>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($pagina_atual == 'usuarios.php') ? 'active' : '' ?>" href="usuarios.php">
                    <i class="bi bi-person-badge me-2"></i> Administradores
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-3">
                <a class="nav-link btn-logout-especial" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema
                </a>
            </li>
        </ul>

        <div class="text-center p-3 mt-4 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
            <p class="text-muted mb-0" style="font-size: 0.7rem;">&copy; 2025 Instituto Humaniza</p>
        </div>
    </div>
</nav>

<style>
    .nav-link {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 0.9rem;
        padding: 10px 15px !important;
        border-radius: 8px;
        transition: all 0.3s ease;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }

    .nav-link.active {
        background-color: #E30613 !important;
        color: #ffffff !important;
    }

    .nav-link:hover:not(.active):not(.btn-logout-especial) {
        background-color: rgba(255, 255, 255, 0.05);
        color: #ffffff !important;
    }

    .btn-logout-especial {
        color: #ffc107 !important;
        font-weight: 700 !important;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .btn-logout-especial:hover {
        background-color: #ffc107 !important;
        color: #2D2D2D !important;
    }
</style>