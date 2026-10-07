<?php
/**
 * VITRINE PÚBLICA DE CURSOS E EVENTOS - INSTITUTO HUMANIZA RR (controller)
 * --------------------------------------------------------------------------
 * Lista os eventos/cursos publicados em cards. O usuário clica no card e é
 * direcionado para a ação correta:
 *   - Inscrições abertas  -> public/inscricao.php?id=N
 *   - Evento encerrado com certificado habilitado -> public/certificado.php?id=N
 *   - Demais casos -> página de inscrições (que exibe o status adequado)
 */

session_start();

require_once '../config/Database.php';
require_once '../services/CertificadoService.php';

$database = new Database();
$db = $database->getConnection();
if (!$db) {
    exit('Erro de conexão com o banco de dados.');
}

$certificado = new CertificadoService($db);
$cursos = $certificado->listarCursosParaVitrine();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos e Eventos - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh; }
        .hero-section { background: linear-gradient(135deg, #E30613 0%, #c41c26 100%); color: white; padding: 50px 0; margin-bottom: 40px; }
        .card-evento { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; transition: transform .2s, box-shadow .2s; height: 100%; }
        .card-evento:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .card-evento .banner { height: 170px; background: linear-gradient(135deg,#E30613,#7a0a10); background-size: cover; background-position: center; position: relative; }
        .badge-status { position: absolute; top: 12px; right: 12px; }
        .btn-humaniza { background-color: #E30613; color: white; font-weight: 600; }
        .btn-humaniza:hover { background-color: #c41c26; color: white; }
        .text-humaniza { color: #E30613; }
    </style>
</head>
<body>
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold"><i class="bi bi-calendar-event me-2"></i>Cursos e Eventos</h1>
            <p class="mb-0">Participe dos nossos eventos ou emita o certificado das suas participações</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (empty($cursos)): ?>
            <div class="alert alert-light border text-center">
                <i class="bi bi-inbox fs-3 d-block mb-2 text-muted"></i>
                Nenhum evento publicado no momento.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($cursos as $c):
                    $id = (int)$c['id'];
                    $aberto = $c['status'] === 'Aberto' && \Curso::inscricoesAbertasPorData($c);
                    $fim_ts = $certificado->dataTerminoEvento($id);
                    $encerrado = $fim_ts !== null && $fim_ts < time();
                    $emitir = !empty($c['emitir_certificado']);

                    if ($aberto) {
                        $cor_status = 'success'; $rotulo = 'Inscrições abertas';
                        $acao_url = 'inscricao.php?id=' . $id; $acao_txt = 'Inscrever-se';
                    } elseif ($encerrado && $emitir) {
                        $cor_status = 'primary'; $rotulo = 'Evento realizado';
                        $acao_url = 'certificado.php?id=' . $id; $acao_txt = 'Buscar meu certificado';
                    } elseif ($encerrado) {
                        $cor_status = 'secondary'; $rotulo = 'Evento encerrado';
                        $acao_url = 'inscricao.php?id=' . $id; $acao_txt = 'Ver detalhes';
                    } else {
                        $cor_status = 'warning text-dark'; $rotulo = 'Em breve';
                        $acao_url = 'inscricao.php?id=' . $id; $acao_txt = 'Ver detalhes';
                    }

                    $banner = !empty($c['banner_imagem']) ? 'uploads/' . htmlspecialchars($c['banner_imagem']) : '';
                    $data_fmt = $certificado->formatarDataEvento(['data_evento' => $c['data_evento'], 'id_curso' => $id]);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="<?= $acao_url ?>" class="text-decoration-none text-dark">
                            <div class="card card-evento">
                                <div class="banner" <?= $banner ? 'style="background-image:url(' . $banner . ')"' : '' ?>>
                                    <span class="badge badge-status bg-<?= $cor_status ?>"><?= $rotulo ?></span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold"><?= htmlspecialchars($c['nome']) ?></h5>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?= htmlspecialchars(mb_strimwidth(strip_tags($c['descricao'] ?? ''), 0, 120, '...')) ?>
                                    </p>
                                    <ul class="list-unstyled small mb-3">
                                        <li><i class="bi bi-calendar3 text-humaniza me-2"></i><?= $data_fmt ?: 'A definir' ?></li>
                                        <?php if (!empty($c['duracao'])): ?>
                                            <li><i class="bi bi-clock text-humaniza me-2"></i><?= htmlspecialchars($c['duracao']) ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($c['local'])): ?>
                                            <li><i class="bi bi-geo-alt text-humaniza me-2"></i><?= htmlspecialchars($c['local']) ?></li>
                                        <?php endif; ?>
                                    </ul>
                                    <span class="btn btn-humaniza btn-sm w-100">
                                        <?php if ($acao_url === 'certificado.php?id=' . $id): ?>
                                            <i class="bi bi-patch-check me-1"></i><?= $acao_txt ?>
                                        <?php else: ?>
                                            <i class="bi bi-arrow-right me-1"></i><?= $acao_txt ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="certificado.php" class="btn btn-outline-danger">
                <i class="bi bi-search me-2"></i>Já participou? Buscar meu certificado por e-mail
            </a>
        </div>

        <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.8rem;">
            &copy; <?= date('Y') ?> Instituto Humaniza RR ·
            <a href="../index.php" class="text-decoration-none">Voltar ao site</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
