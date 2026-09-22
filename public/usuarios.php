<?php
/**
 * Gestão de Usuários (Acesso Restrito ao Admin)
 * Localização: src/public/usuarios.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

// SEGURANÇA: Só Admin entra aqui
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$msg = "";

// --- 1. LÓGICA DE PROCESSAMENTO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $nome  = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $nivel = $_POST['nivel_acesso'];

    if ($_POST['action'] == 'create') {
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$nome, $email, $senha, $nivel]);
    } 
    elseif ($_POST['action'] == 'update') {
        $id = (int)$_POST['id'];
        if (!empty($_POST['senha'])) {
            $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, nivel_acesso = ?, senha = ? WHERE id = ?";
            $params = [$nome, $email, $nivel, $senha, $id];
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ?, nivel_acesso = ? WHERE id = ?";
            $params = [$nome, $email, $nivel, $id];
        }
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);
    }
    if ($res) $msg = "<div class='alert alert-success alert-dismissible fade show'>✅ Operação realizada!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

// --- 2. LÓGICA DE EXCLUSÃO (GET) ---
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    if ($id_del != $_SESSION['usuario_id']) {
        $db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id_del]);
        header("Location: usuarios.php?msg=deleted");
        exit;
    }
}

$query = "SELECT id, nome, email, nivel_acesso FROM usuarios ORDER BY nome ASC";
$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Equipe - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --humaniza-red: #E30613; --humaniza-dark: #2D2D2D; }
        body { background-color: #f8f9fa; color: #333; }
        .sidebar { min-height: 100vh; background: var(--humaniza-dark); color: white; }
        .nav-link { color: rgba(255,255,255,0.8); transition: 0.3s; padding: 12px 20px; }
        .nav-link:hover, .nav-link.active { color: white; background: var(--humaniza-red) !important; border-radius: 5px; }
        .btn-humaniza { background: var(--humaniza-red); color: white; border: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="h2 fw-bold text-dark">Gerenciar Usuários</h1>
                <button class="btn btn-humaniza shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="prepararCadastro()">
                    <i class="bi bi-person-plus-fill me-2"></i> NOVO USUÁRIO
                </button>
            </div>

            <?= $msg ?>

            <div class="card border-0 shadow-sm text-dark">
                <div class="card-body p-0 text-dark">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-4">Nome</th><th>E-mail</th><th>Nível</th><th class="text-end pe-4">Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="ps-4"><strong><?= htmlspecialchars($row['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><span class="badge <?= $row['nivel_acesso'] == 'admin' ? 'bg-danger' : 'bg-secondary' ?>"><?= strtoupper($row['nivel_acesso']) ?></span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1 btn-editar" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-nome="<?= htmlspecialchars($row['nome']) ?>" 
                                            data-email="<?= htmlspecialchars($row['email']) ?>" 
                                            data-nivel="<?= $row['nivel_acesso'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if($row['id'] != $_SESSION['usuario_id']): ?>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir usuário?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content border-0 shadow text-dark" method="POST" id="formUsuario">
            <input type="hidden" name="action" id="inputAction" value="create">
            <input type="hidden" name="id" id="inputId">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold" id="modalTitle">Cadastrar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">NOME</label>
                    <input type="text" name="nome" id="inputNome" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">E-MAIL</label>
                    <input type="email" name="email" id="inputEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">SENHA <span id="infoSenha" class="text-muted small d-none">(Deixe vazio para manter atual)</span></label>
                    <input type="password" name="senha" id="inputSenha" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold small">NÍVEL</label>
                    <select name="nivel_acesso" id="inputNivel" class="form-select">
                        <option value="editor">Editor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="submit" class="btn btn-humaniza px-4">SALVAR</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Captura o clique em todos os botões de editar
document.querySelectorAll('.btn-editar').forEach(button => {
    button.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        
        // Preenche os campos do formulário
        document.getElementById('modalTitle').innerText = 'Editar Usuário';
        document.getElementById('inputAction').value = 'update';
        document.getElementById('inputId').value = this.getAttribute('data-id');
        document.getElementById('inputNome').value = this.getAttribute('data-nome');
        document.getElementById('inputEmail').value = this.getAttribute('data-email');
        document.getElementById('inputNivel').value = this.getAttribute('data-nivel');
        
        // Ajusta campo de senha
        document.getElementById('infoSenha').classList.remove('d-none');
        document.getElementById('inputSenha').required = false;
        
        modal.show();
    });
});

function prepararCadastro() {
    document.getElementById('modalTitle').innerText = 'Cadastrar Usuário';
    document.getElementById('inputAction').value = 'create';
    document.getElementById('formUsuario').reset();
    document.getElementById('infoSenha').classList.add('d-none');
    document.getElementById('inputSenha').required = true;
}
</script>
</body>
</html>