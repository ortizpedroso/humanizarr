<?php
/**
 * LISTA DE PÁGINAS - PAINEL ADMINISTRATIVO
 * Localização: src/public/paginas_lista.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$database = new Database();
$db = $database->getConnection();

// --- LÓGICA PARA EXCLUIR PÁGINA ---
if (isset($_GET['delete']) && $_SESSION['usuario_nivel'] === 'admin') {
    $id_del = (int)$_GET['delete'];
    
    // 1. Opcional: Eliminar imagens das seções desta página antes de apagar (para não lixar o servidor)
    $stmt_img = $db->prepare("SELECT imagem FROM secoes WHERE pagina_id = ?");
    $stmt_img->execute([$id_del]);
    while ($img = $stmt_img->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($img['imagem'])) { @unlink(__DIR__ . '/uploads/' . $img['imagem']); }
    }

    // 2. Apagar as seções da página
    $db->prepare("DELETE FROM secoes WHERE pagina_id = ?")->execute([$id_del]);

    // 3. Apagar a página
    if ($db->prepare("DELETE FROM paginas WHERE id = ?")->execute([$id_del])) {
        header("Location: paginas_lista.php?status=deleted");
        exit;
    }
}

// Busca páginas ordenadas pela ordem definida
$paginas = $db->query("SELECT * FROM paginas ORDER BY ordem ASC, titulo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Páginas - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-size: 0.9rem; }
        .table thead { background-color: #2D2D2D; color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Páginas do Site</h2>
                    <a href="paginas_editar.php" class="btn btn-danger fw-bold shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Nova Página
                    </a>
                </div>

                <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                    <div class="alert alert-success">Página eliminada com sucesso!</div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="80" class="ps-3">Ordem</th>
                                    <th>Título</th>
                                    <th>Link</th>
                                    <th class="text-center">No Menu?</th>
                                    <th class="text-end pe-3">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($paginas as $p): ?>
                                <tr>
                                    <td class="ps-3"><span class="badge bg-secondary"><?= $p['ordem'] ?>º</span></td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['titulo']) ?></td>
                                    <td><small class="text-muted">/pagina.php?slug=<?= $p['slug'] ?></small></td>
                                    <td class="text-center">
                                        <?= $p['no_menu'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <a href="paginas_editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            
                                            <?php if($_SESSION['usuario_nivel'] === 'admin'): ?>
                                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Tem certeza que deseja apagar esta página? Todas as seções de conteúdo também serão apagadas.')">
                                                <i class="bi bi-trash"></i> Excluir
                                            </a>
                                            <?php endif; ?>
                                        </div>
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
</body>
</html>