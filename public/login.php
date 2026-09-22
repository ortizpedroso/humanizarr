<?php
/**
 * Página de Login - Painel Administrativo Humaniza RR
 * Localização: src/public/login.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';

session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new Usuario($db);
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if ($userModel->login($email, $senha)) {
        $_SESSION['usuario_id'] = $userModel->id;
        $_SESSION['usuario_nome'] = $userModel->nome;
        $_SESSION['usuario_nivel'] = $userModel->nivel_acesso;
        header("Location: index.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Instituto Humaniza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --humaniza-red: #E30613;
            --humaniza-dark: #2D2D2D;
        }
        body { 
            background-color: #f8f9fa; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border: none;
            border-top: 5px solid var(--humaniza-red);
            border-radius: 8px;
        }
        .btn-humaniza {
            background-color: var(--humaniza-red);
            color: white;
            border: none;
            border-size: 0px;
            padding: 12px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-humaniza:hover {
            background-color: #b3050f;
            color: white;
            transform: translateY(-1px);
        }
        .text-humaniza { color: var(--humaniza-red); }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-humaniza">HUMANIZA <span style="color:var(--humaniza-dark)">RR</span></h2>
                </div>
                
                <div class="card shadow-lg login-card p-4">
                    <div class="card-body">
                        <h5 class="text-center mb-4 fw-bold">Acesso Restrito</h5>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger text-center py-2 small"><?= $erro ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">E-MAIL</label>
                                <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">SENHA</label>
                                <input type="password" name="senha" class="form-control" required placeholder="••••••••">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-humaniza shadow-sm">ENTRAR NO PAINEL</button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="../index.php" class="text-muted text-decoration-none small">
                                    <i class="bi bi-arrow-left"></i> voltar para o site
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">&copy; 2025 Instituto Humaniza Roraima</p>
            </div>
        </div>
    </div>

</body>
</html>