<?php
/**
 * Página 404 - Personalizada Instituto Humaniza RR
 * Localização: /404.php
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - Instituto Humaniza RR</title>
    <meta name="description" content="Página não encontrada no site do Instituto Humaniza RR. Volte para a página inicial ou entre em contato.">
    <meta name="robots" content="noindex, follow">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; }
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
        }
        .error-content {
            text-align: center;
            max-width: 600px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            color: var(--h-red);
            line-height: 1;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.1);
        }
        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--h-dark);
            margin: 20px 0;
        }
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn-humaniza {
            background-color: var(--h-red);
            color: white;
            border: none;
            padding: 15px 40px;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-humaniza:hover {
            background-color: #b3050f;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(227, 6, 19, 0.3);
        }
        .btn-outline-humaniza {
            background-color: transparent;
            color: var(--h-red);
            border: 2px solid var(--h-red);
            padding: 13px 38px;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .btn-outline-humaniza:hover {
            background-color: var(--h-red);
            color: white;
        }
        .icon-error {
            font-size: 80px;
            color: var(--h-red);
            margin-bottom: 20px;
        }
        footer {
            background-color: var(--h-dark);
            color: #fff;
            padding: 30px 0;
            text-align: center;
        }
        @media (max-width: 768px) {
            .error-code { font-size: 80px; }
            .error-title { font-size: 24px; }
            .btn-outline-humaniza { margin-left: 0; margin-top: 10px; display: block; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="icon-error">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            
            <div class="error-code">404</div>
            
            <h1 class="error-title">Ops! Página não encontrada</h1>
            
            <p class="error-message">
                A página que você está procurando não existe ou foi movida. 
                Isso pode acontecer se você digitou um endereço incorreto ou clicou em um link desatualizado.
            </p>
            
            <div class="mt-5">
                <a href="index.php" class="btn-humaniza">
                    <i class="bi bi-house-door me-2"></i>Voltar ao Início
                </a>
                <a href="contato.php" class="btn-outline-humaniza">
                    <i class="bi bi-chat-dots me-2"></i>Fale Conosco
                </a>
            </div>
            
            <div class="mt-5 p-4 bg-white rounded-4 shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--h-red);">Você estava procurando por:</h6>
                <ul class="list-unstyled mb-0 text-start d-inline-block">
                    <li><a href="sobre.php" class="text-decoration-none text-muted">→ Sobre o Instituto</a></li>
                    <li><a href="projetos.php" class="text-decoration-none text-muted">→ Nossos Projetos</a></li>
                    <li><a href="noticias.php" class="text-decoration-none text-muted">→ Últimas Notícias</a></li>
                    <li><a href="galeria_completa.php" class="text-decoration-none text-muted">→ Galeria de Fotos</a></li>
                    <li><a href="doe-aqui.php" class="text-decoration-none text-muted">→ Como Doar</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Instituto Humaniza RR - Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
