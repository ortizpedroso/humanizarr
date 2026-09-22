<?php
/**
 * GERENCIAMENTO DE CONFIGURAÇÕES - INSTITUTO HUMANIZA RR
 * Localização: src/public/configuracoes.php
 */
session_start();
require_once __DIR__ . '/../config/Database.php';

// Proteção de acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$mensagem = "";

// PROCESSAR ATUALIZAÇÃO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar'])) {
    try {
        // 1. Atualizar campos de texto (WhatsApp, Redes, Institucional, Endereço e Email)
        foreach ($_POST['config'] as $chave => $valor) {
            $stmt = $db->prepare("UPDATE configuracoes SET valor = :valor WHERE chave = :chave");
            $stmt->bindParam(':valor', $valor);
            $stmt->bindParam(':chave', $chave);
            $stmt->execute();
        }

        // 2. Processar Uploads de Imagens
        $uploadDir = __DIR__ . '/uploads/';
        $arquivos = [
            'file_logo' => 'logo',
            'file_favicon' => 'favicon',
            'file_institucional' => 'institucional'
        ];

        foreach ($arquivos as $inputName => $chave) {
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === 0) {
                $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
                $novoNome = $chave . "_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadDir . $novoNome)) {
                    $stmtImg = $db->prepare("UPDATE configuracoes SET imagem = :imagem WHERE chave = :chave");
                    $stmtImg->bindParam(':imagem', $novoNome);
                    $stmtImg->bindParam(':chave', $chave);
                    $stmtImg->execute();
                }
            }
        }

        $mensagem = "<div class='alert alert-success shadow-sm'><i class='bi bi-check-circle-fill me-2'></i>Configurações atualizadas com sucesso!</div>";
    } catch (Exception $e) {
        $mensagem = "<div class='alert alert-danger shadow-sm'>Erro ao atualizar: " . $e->getMessage() . "</div>";
    }
}

// BUSCAR CONFIGURAÇÕES ATUAIS
$configs_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['chave']] = [
        'valor' => $c['valor'],
        'imagem' => $c['imagem']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações - Painel Humaniza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --h-red: #E30613; --h-dark: #2D2D2D; }
        body { font-family: 'Montserrat', sans-serif; }
        .btn-save { background-color: var(--h-red); color: white; border: none; font-weight: bold; }
        .btn-save:hover { background-color: #b3050f; color: white; }
        .preview-img { height: 50px; object-fit: contain; border: 1px solid #dee2e6; padding: 5px; border-radius: 5px; background: #f8f9fa; }
        .preview-inst { width: 100%; max-height: 150px; object-fit: cover; border-radius: 10px; border: 1px solid #dee2e6; }
        .card { border-radius: 15px; }
        .card-header { border-top-left-radius: 15px !important; border-top-right-radius: 15px !important; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2"><i class="bi bi-gear-fill me-2"></i> Configurações do Site</h1>
            </div>

            <?= $mensagem ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Identidade Visual</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Logo do Site</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="uploads/<?= $configs['logo']['imagem'] ?? '' ?>" class="preview-img">
                                    <input type="file" name="file_logo" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Favicon (Ícone)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="uploads/<?= $configs['favicon']['imagem'] ?? '' ?>" class="preview-img" style="width: 50px;">
                                    <input type="file" name="file_favicon" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Seção: Conheça o Instituto</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <label class="form-label fw-bold small text-uppercase">Texto Institucional</label>
                                <textarea name="config[institucional]" class="form-control" rows="8"><?= htmlspecialchars($configs['institucional']['valor'] ?? '') ?></textarea>
                            </div>
                            <div class="col-lg-4 text-center">
                                <label class="form-label fw-bold small text-uppercase d-block text-start">Imagem da Seção</label>
                                <img src="uploads/<?= $configs['institucional']['imagem'] ?? '' ?>" class="preview-inst mb-2">
                                <input type="file" name="file_institucional" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Informações de Contato e Localização</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">E-mail Oficial</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="config[email]" class="form-control" value="<?= htmlspecialchars($configs['email']['valor'] ?? '') ?>" placeholder="contato@humanizarr.org">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">WhatsApp de Contato</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                                    <input type="text" name="config[whatsapp]" class="form-control" value="<?= htmlspecialchars($configs['whatsapp']['valor'] ?? '') ?>" placeholder="(95) 99999-9999">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase">Endereço Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="config[endereco]" class="form-control" value="<?= htmlspecialchars($configs['endereco']['valor'] ?? '') ?>" placeholder="Rua, Número, Bairro, Cidade - RR">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Redes Sociais</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="bi bi-instagram"></i></span>
                                    <input type="url" name="config[instagram]" class="form-control" value="<?= htmlspecialchars($configs['instagram']['valor'] ?? '') ?>" placeholder="https://instagram.com/perfil">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Facebook</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="bi bi-facebook"></i></span>
                                    <input type="url" name="config[facebook]" class="form-control" value="<?= htmlspecialchars($configs['facebook']['valor'] ?? '') ?>" placeholder="https://facebook.com/pagina">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">YouTube</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="bi bi-youtube"></i></span>
                                    <input type="url" name="config[youtube]" class="form-control" value="<?= htmlspecialchars($configs['youtube']['valor'] ?? '') ?>" placeholder="https://youtube.com/canal">
                                </div>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" name="atualizar" class="btn btn-save px-5 py-3 rounded-pill shadow-sm">
                                    <i class="bi bi-save me-2"></i> SALVAR TODAS AS CONFIGURAÇÕES
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>