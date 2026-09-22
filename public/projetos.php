<?php
/**
 * Gestão de Projetos - Humaniza RR
 * Versão: 4.0 - Com Edição, Exclusão e Redimensionamento
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$database = new Database();
$db = $database->getConnection();
$msg = "";
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 'editor';

/**
 * 1. LÓGICA DE EXCLUSÃO (Apenas Admin)
 */
if (isset($_GET['delete']) && $nivel_usuario === 'admin') {
    $id_del = (int)$_GET['delete'];
    
    // Busca a imagem para remover do servidor
    $stmt_img = $db->prepare("SELECT imagem FROM projetos WHERE id = ?");
    $stmt_img->execute([$id_del]);
    $projeto = $stmt_img->fetch(PDO::FETCH_ASSOC);

    if ($projeto) {
        $del = $db->prepare("DELETE FROM projetos WHERE id = ?");
        if ($del->execute([$id_del])) {
            $caminho = __DIR__ . '/uploads/' . $projeto['imagem'];
            if ($projeto['imagem'] != 'default.jpg' && file_exists($caminho)) {
                @unlink($caminho);
            }
            header("Location: projetos.php?status=deleted");
            exit;
        }
    }
}

/**
 * 2. FUNÇÃO DE REDIMENSIONAMENTO (1000x600px)
 */
function processarImagemProjeto($origem, $destino) {
    if (!function_exists('imagecreatetruecolor')) return move_uploaded_file($origem, $destino);
    
    list($w_orig, $h_orig, $tipo) = getimagesize($origem);
    $w_alvo = 1000;
    $fator = $w_alvo / $w_orig;
    $h_alvo = (int)($h_orig * $fator);
    
    $canvas = imagecreatetruecolor($w_alvo, $h_alvo);
    
    switch ($tipo) {
        case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($origem); break;
        case IMAGETYPE_PNG:  $img = imagecreatefrompng($origem); break;
        default: return move_uploaded_file($origem, $destino);
    }
    
    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w_alvo, $h_alvo, $w_orig, $h_orig);
    imagejpeg($canvas, $destino, 80);
    imagedestroy($canvas);
    imagedestroy($img);
    return true;
}

/**
 * 3. LÓGICA DE SALVAMENTO (CREATE / UPDATE)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = isset($_POST['status_ativo']) ? 'ativo' : 'inativo';
    $nome_imagem = $_POST['imagem_atual'] ?? 'default.jpg';

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $nome_imagem = "proj_" . time() . ".jpg";
        processarImagemProjeto($_FILES['imagem']['tmp_name'], __DIR__ . '/uploads/' . $nome_imagem);
    }

    if ($_POST['action'] == 'create') {
        $sql = "INSERT INTO projetos (titulo, descricao, imagem, status) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$titulo, $descricao, $nome_imagem, $status]);
    } else {
        $id = (int)$_POST['id'];
        $sql = "UPDATE projetos SET titulo=?, descricao=?, imagem=?, status=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$titulo, $descricao, $nome_imagem, $status, $id]);
    }
    
    if($res) $msg = "<div class='alert alert-success shadow-sm'>✅ Projeto salvo com sucesso!</div>";
}

// Mensagem de status via GET
if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $msg = "<div class='alert alert-success shadow-sm'>✅ Projeto excluído com sucesso!</div>";
}

$projetos = $db->query("SELECT * FROM projetos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Projetos - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; }
        body { background-color: #f4f6f9; color: #333; }
        .img-table { width: 70px; height: 45px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="h3 fw-bold text-dark">Gerenciar Projetos</h1>
                <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProjeto" onclick="novoProjeto()">
                    <i class="bi bi-plus-lg me-1"></i> NOVO PROJETO
                </button>
            </div>

            <?= $msg ?>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Capa</th>
                                <th>Título</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($projetos as $p): ?>
                            <tr>
                                <td class="ps-3">
                                    <img src="uploads/<?= $p['imagem'] ?>" class="img-table" onerror="this.src='https://via.placeholder.com/70x45'">
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($p['titulo']) ?></td>
                                <td>
                                    <span class="badge <?= $p['status'] == 'ativo' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= strtoupper($p['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary btn-editar" 
                                            data-id="<?= $p['id'] ?>" 
                                            data-titulo="<?= htmlspecialchars($p['titulo']) ?>" 
                                            data-desc="<?= htmlspecialchars($p['descricao']) ?>" 
                                            data-status="<?= $p['status'] ?>"
                                            data-imagem="<?= $p['imagem'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <?php if($nivel_usuario === 'admin'): ?>
                                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este projeto?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalProjeto" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark">
        <form class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data" id="formProjeto">
            <input type="hidden" name="action" id="pAction" value="create">
            <input type="hidden" name="id" id="pId">
            <input type="hidden" name="imagem_atual" id="pImg">
            
            <div class="modal-header bg-light">
                <h5 class="fw-bold m-0" id="pModalTitle">Configurar Projeto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-9">
                        <label class="fw-bold small mb-1">TÍTULO</label>
                        <input type="text" name="titulo" id="pTitulo" class="form-control" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="status_ativo" id="pStatus" checked>
                            <label class="form-check-label fw-bold small" for="pStatus">ATIVO</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold small mb-1">RESUMO (PARA O CARD)</label>
                        <textarea name="descricao" id="pDesc" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold small mb-1">IMAGEM DE CAPA (IDEAL: 1000x600px)</label>
                        <input type="file" name="imagem" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-0">
                <button type="submit" class="btn btn-danger px-5 fw-bold shadow-sm">SALVAR PROJETO</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalProjeto'));
        document.getElementById('pModalTitle').innerText = 'Editar Projeto';
        document.getElementById('pAction').value = 'update';
        document.getElementById('pId').value = this.dataset.id;
        document.getElementById('pTitulo').value = this.dataset.titulo;
        document.getElementById('pDesc').value = this.dataset.desc;
        document.getElementById('pImg').value = this.dataset.imagem;
        document.getElementById('pStatus').checked = (this.dataset.status === 'ativo');
        modal.show();
    });
});

function novoProjeto() {
    document.getElementById('pModalTitle').innerText = 'Novo Projeto';
    document.getElementById('formProjeto').reset();
    document.getElementById('pAction').value = 'create';
    document.getElementById('pStatus').checked = true;
}
</script>
</body>
</html>