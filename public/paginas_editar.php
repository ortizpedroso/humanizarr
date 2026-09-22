<?php
/**
 * EDITOR DE PÁGINA - INSTITUTO HUMANIZA RR
 * Versão: Edição de Blocos no mesmo formulário
 */
session_start();
require_once __DIR__ . '/../config/Database.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$database = new Database();
$db = $database->getConnection();
$id_pagina = isset($_GET['id']) ? (int)$_GET['id'] : null;
$mensagem_feedback = "";

// 1. AÇÃO: EXCLUIR BLOCO
if (isset($_GET['del_secao'])) {
    $id_sec = (int)$_GET['del_secao'];
    $stmt = $db->prepare("SELECT imagem FROM secoes WHERE id = ? AND pagina_id = ?");
    $stmt->execute([$id_sec, $id_pagina]);
    $sec = $stmt->fetch();
    if ($sec && !empty($sec['imagem'])) { @unlink(__DIR__ . '/uploads/' . $sec['imagem']); }
    $db->prepare("DELETE FROM secoes WHERE id = ?")->execute([$id_sec]);
    header("Location: paginas_editar.php?id=$id_pagina&msg=bloco_removido");
    exit;
}

// 2. AÇÃO: SALVAR CONFIGURAÇÕES DA PÁGINA (TOP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao_pagina'])) {
    $titulo = $_POST['titulo'];
    $ordem = (int)$_POST['ordem'];
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo));
    $no_menu = isset($_POST['no_menu']) ? 1 : 0;

    if ($id_pagina) {
        $stmt = $db->prepare("UPDATE paginas SET titulo = ?, slug = ?, ordem = ?, no_menu = ? WHERE id = ?");
        $stmt->execute([$titulo, $slug, $ordem, $no_menu, $id_pagina]);
        header("Location: paginas_editar.php?id=$id_pagina&msg=config_salva");
    } else {
        $stmt = $db->prepare("INSERT INTO paginas (titulo, slug, ordem, no_menu) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $slug, $ordem, $no_menu]);
        $id_pagina = $db->lastInsertId();
        header("Location: paginas_editar.php?id=$id_pagina&msg=pagina_criada");
    }
    exit;
}

// 3. AÇÃO: INSERIR OU ATUALIZAR BLOCO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_secao'])) {
    $id_sec = isset($_POST['id_secao']) ? (int)$_POST['id_secao'] : null;
    $tipo = $_POST['tipo'];
    $posicao = $_POST['posicao_texto'];
    $texto = $_POST['texto'];
    $imagem_nome = $_POST['imagem_atual'] ?? "";

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $novo_nome = "sec_" . time() . ".jpg";
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], __DIR__ . '/uploads/' . $novo_nome)) {
            if (!empty($imagem_nome)) { @unlink(__DIR__ . '/uploads/' . $imagem_nome); }
            $imagem_nome = $novo_nome;
        }
    }

    if ($id_sec) {
        $stmt = $db->prepare("UPDATE secoes SET tipo=?, posicao_texto=?, texto=?, imagem=? WHERE id=? AND pagina_id=?");
        $stmt->execute([$tipo, $posicao, $texto, $imagem_nome, $id_sec, $id_pagina]);
        $msg = "bloco_atualizado";
    } else {
        $stmt = $db->prepare("INSERT INTO secoes (pagina_id, tipo, posicao_texto, texto, imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_pagina, $tipo, $posicao, $texto, $imagem_nome]);
        $msg = "bloco_adicionado";
    }
    header("Location: paginas_editar.php?id=$id_pagina&msg=$msg");
    exit;
}

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'config_salva': $mensagem_feedback = "Configurações salvas!"; break;
        case 'bloco_adicionado': $mensagem_feedback = "Novo bloco inserido!"; break;
        case 'bloco_atualizado': $mensagem_feedback = "Bloco atualizado com sucesso!"; break;
        case 'bloco_removido': $mensagem_feedback = "Bloco removido!"; break;
    }
}

$pagina = $id_pagina ? $db->query("SELECT * FROM paginas WHERE id = $id_pagina")->fetch() : null;
$secoes = $id_pagina ? $db->query("SELECT * FROM secoes WHERE pagina_id = $id_pagina ORDER BY id ASC")->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editor - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f9fa; }
        .img-mini { width: 100px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .alert-floating { position: fixed; top: 20px; right: 20px; z-index: 1060; }
        .card { border: none; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                
                <?php if ($mensagem_feedback): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow alert-floating" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= $mensagem_feedback ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-4">
                    <a href="paginas_lista.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                    <h2 class="fw-bold mb-0"><?= $id_pagina ? 'Editando: '.$pagina['titulo'] : 'Nova Página' ?></h2>
                </div>

                <div class="card shadow-sm p-4 mb-4 bg-white">
                    <form method="POST">
                        <input type="hidden" name="acao_pagina" value="1">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="small fw-bold mb-1">Título da Página</label>
                                <input type="text" name="titulo" class="form-control" value="<?= $pagina['titulo'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">Ordem</label>
                                <input type="number" name="ordem" class="form-control" value="<?= $pagina['ordem'] ?? 0 ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch pb-2">
                                    <input class="form-check-input" type="checkbox" name="no_menu" <?= ($pagina['no_menu'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-bold">No Menu</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger w-100 fw-bold">SALVAR</button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($id_pagina): ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card shadow-sm p-4 sticky-top" style="top: 20px; z-index: 1;">
                            <h5 class="fw-bold mb-3" id="form-title">Novo Bloco</h5>
                            <form method="POST" enctype="multipart/form-data" id="form-bloco">
                                <input type="hidden" name="add_secao" value="1">
                                <input type="hidden" name="id_secao" id="id_secao" value="">
                                <input type="hidden" name="imagem_atual" id="imagem_atual" value="">

                                <div class="mb-3">
                                    <label class="small fw-bold">Tipo</label>
                                    <select name="tipo" id="tipo" class="form-select">
                                        <option value="texto_imagem">Texto e Imagem</option>
                                        <option value="texto">Só Texto</option>
                                        <option value="imagem">Só Imagem</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold">Posição do Texto</label>
                                    <select name="posicao_texto" id="posicao_texto" class="form-select">
                                        <option value="esquerda">Esquerda</option>
                                        <option value="direita">Direita</option>
                                        <option value="cima">Cima</option>
                                        <option value="baixo">Baixo</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold">Texto</label>
                                    <textarea name="texto" id="texto" class="form-control" rows="6"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold">Imagem</label>
                                    <input type="file" name="imagem" class="form-control">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary fw-bold" id="btn-submit">INSERIR BLOCO</button>
                                    <button type="button" class="btn btn-link btn-sm text-muted d-none" id="btn-cancelar" onclick="limparFormulario()">Cancelar Edição</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <h5 class="fw-bold mb-3">Conteúdo Atual</h5>
                        <?php foreach($secoes as $s): ?>
                            <div class="card shadow-sm mb-3 bg-white border-start border-4 border-danger">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img src="<?= $s['imagem'] ? 'uploads/'.$s['imagem'] : 'https://via.placeholder.com/100x70' ?>" class="img-mini">
                                        </div>
                                        <div class="col">
                                            <span class="badge bg-dark mb-1"><?= strtoupper($s['tipo']) ?></span>
                                            <div class="small text-muted text-truncate" style="max-width: 400px;"><?= strip_tags($s['texto']) ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='carregarParaEditar(<?= json_encode($s) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?id=<?= $id_pagina ?>&del_secao=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function carregarParaEditar(dados) {
            // Rola a página para o formulário
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Altera visual do formulário para Edição
            document.getElementById('form-title').innerText = "Editando Bloco";
            document.getElementById('btn-submit').innerText = "ATUALIZAR BLOCO";
            document.getElementById('btn-submit').classList.replace('btn-primary', 'btn-warning');
            document.getElementById('btn-cancelar').classList.remove('d-none');

            // Preenche os campos
            document.getElementById('id_secao').value = dados.id;
            document.getElementById('imagem_atual').value = dados.imagem;
            document.getElementById('tipo').value = dados.tipo;
            document.getElementById('posicao_texto').value = dados.posicao_texto;
            document.getElementById('texto').value = dados.texto;
        }

        function limparFormulario() {
            document.getElementById('form-bloco').reset();
            document.getElementById('id_secao').value = "";
            document.getElementById('imagem_atual').value = "";
            document.getElementById('form-title').innerText = "Novo Bloco";
            document.getElementById('btn-submit').innerText = "INSERIR BLOCO";
            document.getElementById('btn-submit').classList.replace('btn-warning', 'btn-primary');
            document.getElementById('btn-cancelar').classList.add('d-none');
        }

        setTimeout(() => { if(document.querySelector('.alert-floating')) document.querySelector('.alert-floating').remove(); }, 3000);
    </script>
</body>
</html>