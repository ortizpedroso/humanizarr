<?php
/**
 * Gestão de Galeria - Humaniza RR
 * Versão: 2.2 - Com Preview, Exclusão e Tratamento de Imagem
 * Localização: src/public/galeria.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

// Proteção de acesso: redireciona se não estiver logado
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$database = new Database();
$db = $database->getConnection();
$msg = "";

// --- 1. LÓGICA DE EXCLUSÃO ---
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    
    // Busca o nome da imagem antes de deletar para remover o arquivo da pasta
    $stmt = $db->prepare("SELECT imagem FROM galeria WHERE id = ?");
    $stmt->execute([$id_excluir]);
    $foto_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($foto_data) {
        $caminho_arquivo = __DIR__ . '/uploads/' . $foto_data['imagem'];
        
        // Deleta o arquivo físico do servidor
        if (file_exists($caminho_arquivo)) { 
            unlink($caminho_arquivo); 
        }
        
        // Deleta o registo no banco de dados
        $db->prepare("DELETE FROM galeria WHERE id = ?")->execute([$id_excluir]);
        $msg = "<div class='alert alert-warning border-0 shadow-sm'>Foto removida com sucesso!</div>";
    }
}

// --- 2. FUNÇÃO DE TRATAMENTO DE IMAGEM (REDIMENSIONAR E COMPRIMIR) ---
function processarGaleria($origem, $destino) {
    list($w_orig, $h_orig, $tipo) = getimagesize($origem);
    
    $w_alvo = 800; // Largura padrão otimizada
    $fator = $w_alvo / $w_orig;
    $h_alvo = (int)($h_orig * $fator);
    
    $canvas = imagecreatetruecolor($w_alvo, $h_alvo);
    
    // Mantém transparência se for PNG, senão trata como JPEG
    if ($tipo == IMAGETYPE_PNG) {
        $img = imagecreatefrompng($origem);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
    } elseif ($tipo == IMAGETYPE_JPEG) {
        $img = imagecreatefromjpeg($origem);
    } else {
        // Se for outro formato, apenas move sem tratar
        return move_uploaded_file($origem, $destino);
    }

    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w_alvo, $h_alvo, $w_orig, $h_orig);
    
    // Salva sempre como JPG com 75% de qualidade para o site ficar leve
    $sucesso = imagejpeg($canvas, $destino, 75);
    
    imagedestroy($canvas);
    imagedestroy($img);
    
    return $sucesso;
}

// --- 3. LÓGICA DE UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $legenda = filter_input(INPUT_POST, 'legenda', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if ($_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = "galeria_" . time() . ".jpg"; // Padroniza como JPG
        
        if (processarGaleria($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/' . $nome_arquivo)) {
            $stmt = $db->prepare("INSERT INTO galeria (imagem, legenda) VALUES (?, ?)");
            $stmt->execute([$nome_arquivo, $legenda]);
            $msg = "<div class='alert alert-success border-0 shadow-sm'>Nova foto adicionada à galeria!</div>";
        } else {
            $msg = "<div class='alert alert-danger border-0 shadow-sm'>Erro ao processar imagem.</div>";
        }
    }
}

// --- 4. BUSCA AS FOTOS PARA LISTAGEM ---
$fotos = $db->query("SELECT * FROM galeria ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria - Painel Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .foto-card { position: relative; transition: all 0.3s ease; }
        .foto-card:hover { transform: translateY(-3px); }
        .btn-delete {
            position: absolute; top: 10px; right: 10px;
            background: rgba(227, 6, 19, 0.9); color: white;
            border: none; border-radius: 8px; padding: 4px 8px;
            opacity: 0; transition: 0.3s;
        }
        .foto-card:hover .btn-delete { opacity: 1; }
        #imgPreview { max-height: 150px; display: none; border-radius: 10px; margin-top: 10px; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold m-0">Galeria de Eventos</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Galeria</li>
                    </ol>
                </nav>
            </div>

            <?= $msg ?>

            <div class="card border-0 shadow-sm p-4 mb-4">
                <form method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="fw-bold small mb-2 text-uppercase">Selecionar Foto</label>
                        <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*" required>
                        <img id="imgPreview" class="img-thumbnail shadow-sm">
                    </div>
                    <div class="col-md-5">
                        <label class="fw-bold small mb-2 text-uppercase">Legenda / Nome do Evento</label>
                        <input type="text" name="legenda" class="form-control" placeholder="Ex: Ação Social no bairro Centro">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                            <i class="bi bi-cloud-arrow-up me-2"></i>SUBIR
                        </button>
                    </div>
                </form>
            </div>

            <div class="row g-3">
                <?php if(empty($fotos)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-image h1 d-block mb-3"></i>
                        Nenhuma foto cadastrada na galeria.
                    </div>
                <?php endif; ?>

                <?php foreach($fotos as $f): ?>
                <div class="col-md-3 col-lg-2 col-6">
                    <div class="foto-card bg-white shadow-sm rounded-4 overflow-hidden border">
                        <img src="uploads/<?= $f['imagem'] ?>" class="img-fluid" style="height:150px; object-fit:cover; width:100%;">
                        
                        <a href="?excluir=<?= $f['id'] ?>" 
                           class="btn-delete shadow" 
                           onclick="return confirm('Tens a certeza que desejas excluir esta foto permanentemente?')">
                            <i class="bi bi-trash"></i>
                        </a>

                        <div class="p-2 border-top">
                            <small class="text-muted d-block text-truncate fw-bold" title="<?= $f['legenda'] ?>">
                                <?= $f['legenda'] ?: 'Sem legenda' ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<script>
    // Script para mostrar o preview da imagem selecionada
    document.getElementById('fotoInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('imgPreview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>