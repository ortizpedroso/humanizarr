<?php
/**
 * Edição de Perfil Próprio
 * Localização: src/public/perfil.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$database = new Database();
$db = $database->getConnection();
$id_logado = $_SESSION['usuario_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!empty($_POST['senha'])) {
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
        $params = [$nome, $email, $senha, $id_logado];
    } else {
        $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        $params = [$nome, $email, $id_logado];
    }
    
    $stmt = $db->prepare($sql);
    if ($stmt->execute($params)) {
        $_SESSION['usuario_nome'] = $nome; // Atualiza nome na sessão
        $msg = "<div class='alert alert-success'>✅ Perfil atualizado com sucesso!</div>";
    }
}

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_logado]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --humaniza-red: #E30613; --humaniza-dark: #2D2D2D; }
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: var(--humaniza-dark); color: white; }
        .nav-link { color: rgba(255,255,255,0.8); transition: 0.3s; padding: 12px 20px; }
        .nav-link:hover, .nav-link.active { color: white; background: var(--humaniza-red) !important; border-radius: 5px; }
        .btn-humaniza { background: var(--humaniza-red); color: white; border: none; }
    </style>
</head>
<body>
<div class="container-fluid text-dark">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <h2 class="fw-bold border-bottom pb-3 mb-4">Meu Perfil</h2>
            <?= $msg ?>
            <div class="card border-0 shadow-sm col-md-6">
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">NOME</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">E-MAIL</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">NOVA SENHA</label>
                            <input type="password" name="senha" class="form-control" placeholder="Deixe vazio para não alterar">
                        </div>
                        <button type="submit" class="btn btn-humaniza w-100">SALVAR ALTERAÇÕES</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>