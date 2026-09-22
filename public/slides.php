<?php
/**
 * Gestão de Slides/Banners - Humaniza RR
 * Versão: 7.0 - Cadastro Ativo por Padrão
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

function processarSlide($origem, $destino) {
    if (!function_exists('imagecreatetruecolor')) return move_uploaded_file($origem, $destino);
    list($w_orig, $h_orig, $tipo) = getimagesize($origem);
    $w_alvo = 1920; 
    if ($w_orig < $w_alvo) $w_alvo = $w_orig;
    $fator = $w_alvo / $w_orig;
    $h_alvo = (int)($h_orig * $fator);
    $canvas = imagecreatetruecolor($w_alvo, $h_alvo);
    switch ($tipo) {
        case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($origem); break;
        case IMAGETYPE_PNG:  
            $img = imagecreatefrompng($origem);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            break;
        default: return move_uploaded_file($origem, $destino);
    }
    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w_alvo, $h_alvo, $w_orig, $h_orig);
    imagejpeg($canvas, $destino, 85);
    imagedestroy($canvas);
    imagedestroy($img);
    return true;
}

if (isset($_GET['delete']) && $_SESSION['usuario_nivel'] === 'admin') {
    $id_del = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT imagem FROM slides WHERE id = ?");
    $stmt->execute([$id_del]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($slide) {
        $db->prepare("DELETE FROM slides WHERE id = ?")->execute([$id_del]);
        @unlink(__DIR__ . '/uploads/' . $slide['imagem']);
        header("Location: slides.php?status=deleted");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $link = filter_input(INPUT_POST, 'link_botao', FILTER_SANITIZE_URL);
    $ordem = (int)$_POST['ordem'];
    
    // Lógica: Se for criação e o campo não existir (ou for marcado), assume 'ativo'
    $status = (isset($_POST['status_ativo'])) ? 'ativo' : 'inativo';

    $uploadDir = __DIR__ . '/uploads/';
    $nome_img = $_POST['imagem_atual'] ?? '';
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $nome_img = "slide_" . time() . ".jpg";
        processarSlide($_FILES['imagem']['tmp_name'], $uploadDir . $nome_img);
    }

    if ($_POST['action'] == 'create') {
        $sql = "INSERT INTO slides (titulo, subtitulo, imagem, link_botao, ordem, status) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$titulo, $subtitulo, $nome_img, $link, $ordem, $status];
    } else {
        $id = (int)$_POST['id'];
        $sql = "UPDATE slides SET titulo=?, subtitulo=?, imagem=?, link_botao=?, ordem=?, status=? WHERE id=?";
        $params = [$titulo, $subtitulo, $nome_img, $link, $ordem, $status, $id];
    }
    
    if ($db->prepare($sql)->execute($params)) {
        $msg = "<div class='alert alert-success shadow-sm'>✅ Banner salvo como <strong>" . strtoupper($status) . "</strong>!</div>";
    }
}

$slides = $db->query("SELECT * FROM slides ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Slides - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; } 
        body { background-color: #f4f6f9; color: #333; } 
        .img-preview { width: 150px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
        .table-light th { background-color: #f8f9fa; color: var(--h-dark); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="h3 fw-bold text-dark">Banners da Home</h1>
                <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSlide" onclick="novoSlide()">
                    <i class="bi bi-plus-lg me-1"></i> NOVO SLIDE
                </button>
            </div>
            
            <?= $msg ?>

            <div class="card border-0 shadow-sm">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Preview</th>
                            <th>Título</th>
                            <th>Ordem</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($slides as $s): ?>
                        <tr>
                            <td class="ps-3"><img src="uploads/<?= $s['imagem'] ?>" class="img-preview"></td>
                            <td><span class="fw-semibold"><?= htmlspecialchars($s['titulo']) ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= $s['ordem'] ?>º</span></td>
                            <td>
                                <span class="badge <?= $s['status'] == 'ativo' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= strtoupper($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-id="<?= $s['id'] ?>" 
                                    data-titulo="<?= htmlspecialchars($s['titulo']) ?>" 
                                    data-sub="<?= htmlspecialchars($s['subtitulo']) ?>" 
                                    data-link="<?= $s['link_botao'] ?>" 
                                    data-ordem="<?= $s['ordem'] ?>" 
                                    data-status="<?= $s['status'] ?>" 
                                    data-imagem="<?= $s['imagem'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if($_SESSION['usuario_nivel'] === 'admin'): ?>
                                <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover este banner permanentemente?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalSlide" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark">
        <form class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data" id="formSlide">
            <input type="hidden" name="action" id="sAction" value="create">
            <input type="hidden" name="id" id="sId">
            <input type="hidden" name="imagem_atual" id="sImg">
            
            <div class="modal-header bg-light">
                <h5 class="fw-bold m-0" id="modalTitle">Configurar Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-9">
                        <label class="fw-bold small mb-1">TÍTULO PRINCIPAL</label>
                        <input type="text" name="titulo" id="sTitulo" class="form-control" placeholder="Ex: Ação Social em Boa Vista">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small mb-1">ORDEM EXIBIÇÃO</label>
                        <input type="number" name="ordem" id="sOrdem" class="form-control" value="0">
                    </div>
                    <div class="col-md-9">
                        <label class="fw-bold small mb-1">SUBTÍTULO / DESCRIÇÃO</label>
                        <input type="text" name="subtitulo" id="sSub" class="form-control" placeholder="Texto que aparece abaixo do título">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="status_ativo" id="sStatus" checked>
                            <label class="form-check-label fw-bold small" for="sStatus">ATIVO</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold small mb-1">LINK DO BOTÃO (OPCIONAL)</label>
                        <input type="text" name="link_botao" id="sLink" class="form-control" placeholder="Ex: https://humanizarr.org/noticia-exemplo">
                    </div>
                    <div class="col-12">
                        <label class="fw-bold small mb-1">IMAGEM DO BANNER (IDEAL: 1920x600px)</label>
                        <input type="file" name="imagem" id="sInputFile" class="form-control" accept="image/*">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Imagens grandes serão redimensionadas automaticamente.</small>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-0">
                <button type="submit" class="btn btn-danger px-5 fw-bold shadow-sm">SALVAR BANNER</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalSlide'));
        document.getElementById('modalTitle').innerText = 'Editar Banner';
        document.getElementById('sAction').value = 'update';
        document.getElementById('sId').value = this.dataset.id;
        document.getElementById('sTitulo').value = this.dataset.titulo;
        document.getElementById('sSub').value = this.dataset.sub;
        document.getElementById('sLink').value = this.dataset.link;
        document.getElementById('sOrdem').value = this.dataset.ordem;
        document.getElementById('sImg').value = this.dataset.imagem;
        document.getElementById('sInputFile').required = false;
        
        // Aplica o status vindo do banco
        document.getElementById('sStatus').checked = (this.dataset.status === 'ativo');
        
        modal.show();
    });
});

function novoSlide() { 
    document.getElementById('modalTitle').innerText = 'Novo Banner';
    document.getElementById('formSlide').reset(); 
    document.getElementById('sAction').value = 'create'; 
    document.getElementById('sStatus').checked = true; // Garante que novo vem ativo
    document.getElementById('sInputFile').required = true;
}
</script>
</body>
</html>