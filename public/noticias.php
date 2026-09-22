<?php
/**
 * Gestão de Notícias - Humaniza RR
 * Versão Final: Com Redimensionamento Automático e Blindagem de Banco
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
$nivel_usuario = $_SESSION['usuario_nivel'];

/**
 * FUNÇÃO DE REDIMENSIONAMENTO DE IMAGEM
 */
function processarImagemNoticia($origem, $destino) {
    // Se a biblioteca GD não estiver ativa, apenas move o arquivo
    if (!function_exists('imagecreatetruecolor')) {
        return move_uploaded_file($origem, $destino);
    }

    list($w_orig, $h_orig, $tipo) = getimagesize($origem);
    $w_alvo = 1200; // Largura ideal para banners de notícias

    if ($w_orig < $w_alvo) {
        $w_alvo = $w_orig;
    }

    $fator = $w_alvo / $w_orig;
    $h_alvo = (int)($h_orig * $fator); // (int) corrige erro de precisão do PHP 8.3

    $canvas = imagecreatetruecolor($w_alvo, $h_alvo);

    // Identifica o formato original
    switch ($tipo) {
        case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($origem); break;
        case IMAGETYPE_PNG:  
            $img = imagecreatefrompng($origem);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            break;
        case IMAGETYPE_WEBP: $img = imagecreatefromwebp($origem); break;
        default: return move_uploaded_file($origem, $destino);
    }

    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w_alvo, $h_alvo, $w_orig, $h_orig);
    
    // Salva como JPG com 85% de qualidade (Equilíbrio perfeito peso/visual)
    imagejpeg($canvas, $destino, 85);
    
    imagedestroy($canvas);
    imagedestroy($img);
    return true;
}

/**
 * FUNÇÃO PARA GERAR SLUG (URLs AMIGÁVEIS)
 */
function gerarSlug($texto) {
    $slug = mb_strtolower($texto, 'UTF-8');
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    return trim($slug, '-');
}

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['delete']) && $nivel_usuario === 'admin') {
    $id_del = (int)$_GET['delete'];
    $stmt_img = $db->prepare("SELECT imagem_destaque FROM noticias WHERE id = ?");
    $stmt_img->execute([$id_del]);
    $noticia = $stmt_img->fetch(PDO::FETCH_ASSOC);

    if ($noticia) {
        $del = $db->prepare("DELETE FROM noticias WHERE id = ?");
        if ($del->execute([$id_del])) {
            $path = __DIR__ . '/uploads/' . $noticia['imagem_destaque'];
            if ($noticia['imagem_destaque'] && file_exists($path)) @unlink($path);
            header("Location: noticias.php?status=deleted");
            exit;
        }
    }
}

// --- LÓGICA DE SALVAMENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $titulo   = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $conteudo = $_POST['conteudo'];
    $slug     = gerarSlug($_POST['titulo']);
    $autor_id = (int)$_SESSION['usuario_id'];
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    // Tratamento do status (Evita erro de truncamento)
    $status = (isset($_POST['status'])) ? 'ativo' : 'inativo';

    if ($_POST['action'] == 'create' || $_POST['action'] == 'update') {
        $nome_img = $_POST['imagem_atual'] ?? 'news_default.jpg';

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
            $nome_img = "news_" . time() . ".jpg";
            // Chama a função de redimensionamento
            processarImagemNoticia($_FILES['imagem']['tmp_name'], $uploadDir . $nome_img);
        }

        try {
            if ($_POST['action'] == 'create') {
                $sql = "INSERT INTO noticias (titulo, slug, conteudo, imagem_destaque, autor_id, status) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$titulo, $slug, $conteudo, $nome_img, $autor_id, $status];
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE noticias SET titulo = ?, slug = ?, conteudo = ?, imagem_destaque = ?, status = ? WHERE id = ?";
                $params = [$titulo, $slug, $conteudo, $nome_img, $status, $id];
            }

            $stmt = $db->prepare($sql);
            if ($stmt->execute($params)) {
                $msg = "<div class='alert alert-success shadow-sm border-0'>✅ Dados salvos e imagem otimizada!</div>";
            }
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger'>Erro crítico: " . $e->getMessage() . "</div>";
        }
    }
}

$noticias = $db->query("SELECT n.*, u.nome as autor_nome FROM noticias n LEFT JOIN usuarios u ON n.autor_id = u.id ORDER BY n.data_publicacao DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Notícias - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; }
        body { background-color: #f4f6f9; color: #333; }
        .sidebar { min-height: 100vh; background: var(--h-dark); color: white; }
        .img-news { width: 80px; height: 50px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-h { background: var(--h-red); color: white; border: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid text-dark">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="h3 fw-bold">Gestão de Notícias</h1>
                <button class="btn btn-h px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNews" onclick="prepararCadastro()">
                    <i class="bi bi-newspaper me-2"></i>NOVA NOTÍCIA
                </button>
            </div>

            <?= $msg ?>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Capa</th>
                                <th>Título / Autor</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($noticias as $n): ?>
                            <tr>
                                <td class="ps-4"><img src="uploads/<?= $n['imagem_destaque'] ?>" class="img-news" onerror="this.src='https://via.placeholder.com/80x50'"></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($n['titulo']) ?></div>
                                    <small class="text-muted">Por: <?= $n['autor_nome'] ?: 'Admin' ?></small>
                                </td>
                                <td><span class="badge <?= $n['status'] == 'ativo' ? 'bg-success' : 'bg-secondary' ?>"><?= strtoupper($n['status']) ?></span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary btn-editar" 
                                            data-id="<?= $n['id'] ?>" 
                                            data-titulo="<?= htmlspecialchars($n['titulo']) ?>" 
                                            data-conteudo="<?= htmlspecialchars($n['conteudo']) ?>" 
                                            data-status="<?= $n['status'] ?>" 
                                            data-imagem="<?= $n['imagem_destaque'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if($nivel_usuario === 'admin'): ?>
                                        <a href="?delete=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir esta notícia?')">
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

<div class="modal fade" id="modalNews" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark">
        <form class="modal-content border-0" method="POST" enctype="multipart/form-data" id="formNews">
            <input type="hidden" name="action" id="nAction" value="create">
            <input type="hidden" name="id" id="nId">
            <input type="hidden" name="imagem_atual" id="nImg">
            <div class="modal-header bg-light border-0">
                <h5 class="fw-bold m-0">Editor de Notícia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-dark">
                <div class="mb-3">
                    <label class="form-label small fw-bold">TÍTULO DA NOTÍCIA</label>
                    <input type="text" name="titulo" id="nTitulo" class="form-control text-dark" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">CONTEÚDO</label>
                    <textarea name="conteudo" id="nConteudo" class="form-control text-dark" rows="8" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label small fw-bold">IMAGEM DESTAQUE</label>
                        <input type="file" name="imagem" class="form-control" accept="image/*">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Imagens grandes serão redimensionadas automaticamente.</small>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="status" id="nStatus" checked>
                            <label class="form-check-label small fw-bold" for="nStatus">VISÍVEL NO SITE</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="submit" class="btn btn-h px-5 shadow-sm text-white">SALVAR NOTÍCIA</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalNews'));
        document.getElementById('nAction').value = 'update';
        document.getElementById('nId').value = this.dataset.id;
        document.getElementById('nTitulo').value = this.dataset.titulo;
        document.getElementById('nConteudo').value = this.dataset.conteudo;
        document.getElementById('nImg').value = this.dataset.imagem;
        document.getElementById('nStatus').checked = (this.dataset.status === 'ativo');
        modal.show();
    });
});

function prepararCadastro() {
    document.getElementById('nAction').value = 'create';
    document.getElementById('formNews').reset();
    document.getElementById('nStatus').checked = true;
}
</script>
</body>
</html>