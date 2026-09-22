<?php
/**
 * HEADER ATUALIZADO - INSTITUTO HUMANIZA RR
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/Database.php';
$database_h = new Database();
$db_h = $database_h->getConnection();

try {
    $stmt_conf = $db_h->query("SELECT chave, imagem FROM configuracoes WHERE chave IN ('logo', 'favicon')");
    $conf_site = [];
    while($row = $stmt_conf->fetch(PDO::FETCH_ASSOC)) { $conf_site[$row['chave']] = $row['imagem']; }
} catch (Exception $e) { $conf_site = []; }

$prefixo = (strpos($_SERVER['PHP_SELF'], 'public/') !== false) ? 'uploads/' : 'public/uploads/';
$logo_url = !empty($conf_site['logo']) ? $prefixo . $conf_site['logo'] : "";
$favicon_url = !empty($conf_site['favicon']) ? $prefixo . $conf_site['favicon'] : "";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituto Humaniza RR</title>
    <?php if($favicon_url): ?><link rel="icon" type="image/x-icon" href="<?= $favicon_url ?>"><?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; --h-white: #FFFFFF; }
        body { font-family: 'Montserrat', sans-serif; font-weight: 400; font-size: 1rem; color: #555; line-height: 1.6; overflow-x: hidden; }
        .navbar { background-color: var(--h-red) !important; padding: 12px 0; }
        .nav-link { color: var(--h-white) !important; font-weight: 600; font-size: 1rem; margin: 0 12px; transition: 0.3s; }
        .nav-link:hover { opacity: 0.8; }
        .btn-admin-nav { border: 2px solid var(--h-white); border-radius: 50px; padding: 5px 20px !important; background: rgba(255,255,255,0.1); font-weight: 700; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white fw-bold fs-3" href="index.php">
            <?php if($logo_url): ?><img src="<?= $logo_url ?>" style="max-height: 55px;"><?php else: ?>Humaniza <span style="font-weight: 300;">RR</span><?php endif; ?>
        </a>
        
        <button class="navbar-toggler border-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="sobre.php">Sobre</a></li>
                <li class="nav-item"><a class="nav-link" href="galeria_completa.php">Galeria</a></li>
                <li class="nav-item"><a class="nav-link" href="projetos.php">Projetos</a></li>
                <li class="nav-item"><a class="nav-link" href="noticias.php">Notícias</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="nav-link btn-admin-nav" href="public/login.php">Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>