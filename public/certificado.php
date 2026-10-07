<?php
/**
 * EMISSÃO DE CERTIFICADO DIGITAL - INSTITUTO HUMANIZA RR (controller público)
 * ----------------------------------------------------------------------------
 * Página pública onde o participante consulta e emite o certificado.
 * Toda a regra de negócio está em services/CertificadoService.php (OO).
 *
 * Fluxo oficial:
 * 1. O participante chega pelo card do evento (vitrine cursos.php) ou acessa
 *    direto com ?id=CURSO. Digita o E-MAIL usado na inscrição
 *    (este projeto não coleta CPF — a pesquisa é por e-mail).
 * 2. Se a inscrição existir, estiver confirmada e o evento já tiver
 *    encerrado, o sistema exibe os dados e libera a geração.
 * 3. Ao clicar em "Confirmar e gerar", o PDF é enviado para o e-mail
 *    cadastrado (agradecimento + anexo) e liberado para impressão/download.
 *
 * Também suporta:
 *  - Link direto por inscrição:  certificado.php?inscricao=ID
 *  - Validação pública:          certificado.php?validar=CODIGO
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

$erro  = '';
$aviso = '';
$id_curso   = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$email_query = trim($_POST['email'] ?? $_GET['email'] ?? '');
$etapa = 'buscar'; // buscar -> confirmar_email -> emitido

// -------------------------------------------------------------------
// MODO 1: VALIDAÇÃO PÚBLICA POR CÓDIGO (?validar=CODIGO)
// -------------------------------------------------------------------
$validacao_exibida = null; // null = não consultada; false = inválido; string = código válido
if (!empty($_GET['validar'])) {
    $achado = $certificado->validarCodigo($_GET['validar']);
    $validacao_exibida = $achado ? strtoupper(trim($_GET['validar'])) : false;
}

// -------------------------------------------------------------------
// MODO 2: LINK DIRETO (?inscricao=ID) — usado nos e-mails
// -------------------------------------------------------------------
$inscricao = false;
if ($validacao_exibida === null && !empty($_GET['inscricao'])) {
    $inscricao = $certificado->buscarInscricao(['id_inscricao' => (int)$_GET['inscricao']]);
    if ($inscricao) {
        $id_curso = (int)$inscricao['id_curso'];
        $etapa = 'confirmar_email';
    }
}

// -------------------------------------------------------------------
// DOWNLOAD / IMPRESSÃO (?download=1 | ?imprimir=1 + ?inscricao=ID)
// A opção ?imprimir=1 abre o PDF inline em nova aba (Ctrl+P / salvar).
// -------------------------------------------------------------------
if ((isset($_GET['download']) || isset($_GET['imprimir'])) && !empty($_GET['inscricao'])) {
    $dados = $certificado->buscarInscricao(['id_inscricao' => (int)$_GET['inscricao']]);
    if (!$dados) {
        http_response_code(404);
        exit('Inscrição não encontrada.');
    }
    $motivo = $certificado->motivoBloqueio($dados);
    if ($motivo !== null) {
        http_response_code(403);
        exit($motivo);
    }
    $certificado->enviarPdf($dados, isset($_GET['imprimir']) ? 'I' : 'D');
    exit;
}

// -------------------------------------------------------------------
// ETAPA A: BUSCA POR E-MAIL (em um curso específico ou em todos)
// OBS: este projeto não coleta CPF; a pesquisa oficial é por e-mail.
// -------------------------------------------------------------------
if (!$inscricao && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['buscar_email'])) {
    $email_digitado = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email_digitado, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } else {
        $filtros = ['email' => $email_digitado];
        if ($id_curso > 0) {
            $filtros['id_curso'] = $id_curso;
        }
        $resultado = $certificado->buscarInscricao($filtros);

        if (!$resultado) {
            $erro = 'Nenhuma inscrição encontrada para este e-mail'
                  . ($id_curso > 0 ? ' neste evento.' : ' em nossos eventos.')
                  . ' Verifique se foi exatamente este e-mail que você usou na inscrição.';
        } else {
            $motivo = $certificado->motivoBloqueio($resultado);
            if ($motivo !== null) {
                $erro = $motivo;
            } else {
                $inscricao = $resultado;
                $etapa = 'confirmar_email';
            }
        }
    }
    $email_query = $_POST['email'] ?? '';
}

// -------------------------------------------------------------------
// ETAPA B: CONFIRMAÇÃO DE E-MAIL -> gera certificado + envia por e-mail
// -------------------------------------------------------------------
if ($inscricao && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['confirmar_email'])) {
    $email_confirmado = strtolower(trim($_POST['email_confirmado'] ?? ''));

    if (!filter_var($email_confirmado, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strtolower(trim($inscricao['email'])) !== $email_confirmado) {
        $erro = 'O e-mail informado não confere com o cadastrado na inscrição. '
              . 'Utilize exatamente o e-mail que você usou ao se inscrever.';
    } elseif (!$certificado->podeEmitir($inscricao)) {
        $erro = 'O certificado ainda não está disponível para esta inscrição.';
        $inscricao = false;
        $etapa = 'buscar';
    } else {
        $envio_ok = $certificado->enviarEmail($inscricao); // true | false | null
        $etapa = 'emitido';
        if ($envio_ok === true) {
            $aviso = 'Certificado gerado com sucesso! Ele também foi enviado em anexo para <strong>'
                   . htmlspecialchars($inscricao['email'], ENT_QUOTES, 'UTF-8')
                   . '</strong> (confira também a caixa de spam).';
        } else {
            $aviso = 'Certificado gerado com sucesso! O envio automático por e-mail não foi possível '
                   . 'no momento, mas você pode imprimir ou baixar o PDF abaixo.';
        }
    }
}

// Dados do curso para exibir no cabeçalho quando ?id= presente
$curso_atual = null;
if ($id_curso > 0) {
    foreach ($certificado->listarCursosParaVitrine() as $c) {
        if ((int)$c['id'] === $id_curso) { $curso_atual = $c; break; }
    }
}
$codigo_validacao = $inscricao
    ? \CertificadoService::gerarCodigoValidacao($inscricao['id_inscricao'], $inscricao['email'])
    : '';
$data_evento_fmt = $inscricao ? $certificado->formatarDataEvento($inscricao) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificados - Humaniza RR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh; }
        .hero-section { background: linear-gradient(135deg, #E30613 0%, #c41c26 100%); color: white; padding: 50px 0; margin-bottom: 40px; }
        .card-cert { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .btn-humaniza { background-color: #E30613; color: white; font-weight: 600; }
        .btn-humaniza:hover { background-color: #c41c26; color: white; }
    </style>
</head>
<body>
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold"><i class="bi bi-patch-check me-2"></i>Certificados Digitais</h1>
            <p class="mb-0">Consulte, imprima e receba por e-mail o certificado de participação dos eventos do Instituto Humaniza RR</p>
        </div>
    </div>

    <div class="container pb-5" style="max-width: 720px;">

        <?php if ($validacao_exibida !== null): ?>
            <div class="alert alert-<?= $validacao_exibida ? 'success' : 'danger' ?>">
                <?php if ($validacao_exibida): ?>
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Certificado VÁLIDO.</strong> Código <?= htmlspecialchars($validacao_exibida) ?> autenticado na base do Instituto Humaniza RR.
                <?php else: ?>
                    <i class="bi bi-x-octagon me-2"></i>
                    <strong>Código inválido.</strong> Este código de validação não existe em nossa base.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $erro ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($etapa === 'emitido' && $inscricao): ?>
            <!-- ETAPA 3: CERTIFICADO EMITIDO -->
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i><?= $aviso ?>
            </div>
            <div class="card card-cert p-4 mb-4 text-center">
                <i class="bi bi-mortarboard-fill" style="font-size:3rem;color:#E30613;"></i>
                <h4 class="fw-bold mt-3"><?= htmlspecialchars($inscricao['curso_nome']) ?></h4>
                <p class="text-muted mb-1"><?= htmlspecialchars($inscricao['nome']) ?></p>
                <p class="mb-4">
                    <span class="badge bg-secondary">Código de validação: <?= htmlspecialchars($codigo_validacao) ?></span>
                </p>
                <div class="d-grid gap-2">
                    <a href="certificado.php?imprimir=1&amp;inscricao=<?= (int)$inscricao['id_inscricao'] ?>"
                       target="_blank" rel="noopener" class="btn btn-humaniza btn-lg">
                        <i class="bi bi-printer me-2"></i>Gerar Certificado (abre para impressão/PDF)
                    </a>
                    <a href="certificado.php?download=1&amp;inscricao=<?= (int)$inscricao['id_inscricao'] ?>"
                       class="btn btn-outline-danger">
                        <i class="bi bi-download me-2"></i>Baixar o PDF
                    </a>
                </div>
                <small class="text-muted mt-3 d-block">
                    O PDF abre em uma nova aba pronto para imprimir (Ctrl+P) ou salvar como PDF.
                </small>
            </div>

        <?php elseif ($etapa === 'confirmar_email' && $inscricao): ?>
            <!-- ETAPA 2: CONFIRMAR E-MAIL -->
            <div class="card card-cert p-4 mb-4">
                <h4 class="fw-bold text-success mb-3"><i class="bi bi-person-check me-2"></i>Inscrição localizada!</h4>
                <table class="table table-borderless mb-3">
                    <tr><th style="width:160px;">Participante</th><td><?= htmlspecialchars($inscricao['nome']) ?></td></tr>
                    <tr><th>E-mail</th><td><?= htmlspecialchars($inscricao['email']) ?></td></tr>
                    <tr><th>Evento</th><td><?= htmlspecialchars($inscricao['curso_nome']) ?></td></tr>
                    <tr><th>Data</th><td><?= htmlspecialchars($data_evento_fmt) ?></td></tr>
                    <tr><th>Carga horária</th><td><?= htmlspecialchars($inscricao['duracao']) ?></td></tr>
                    <tr><th>Status</th><td><span class="badge bg-success"><?= htmlspecialchars($inscricao['status_inscricao']) ?></span></td></tr>
                </table>
                <hr>
                <h5 class="fw-bold mb-2"><i class="bi bi-envelope-at me-2"></i>Confirme seu e-mail para receber o certificado</h5>
                <p class="text-muted">Digite novamente o e-mail da inscrição (<strong><?= htmlspecialchars(\CertificadoService::mascararEmail($inscricao['email'])) ?></strong>) para liberar a geração. Enviaremos o certificado em anexo para ele.</p>
                <form method="POST" action="certificado.php<?= $id_curso ? '?id=' . $id_curso : '' ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($inscricao['email']) ?>">
                    <div class="mb-3">
                        <input type="email" name="email_confirmado" id="email_confirmado" class="form-control form-control-lg"
                               placeholder="confirme-seu@email.com" required autofocus autocomplete="off">
                    </div>
                    <button type="submit" name="confirmar_email" value="1" class="btn btn-humaniza w-100 py-2 btn-lg">
                        <i class="bi bi-file-earmark-arrow-right me-2"></i>Confirmar e gerar meu certificado
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- ETAPA 1: BUSCAR POR E-MAIL -->
            <div class="card card-cert p-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-search me-2"></i>Buscar meu certificado por e-mail</h5>
                <p class="text-muted mb-3">
                    Informe o <strong>e-mail usado na inscrição</strong><?= $curso_atual ? ' para o evento <strong>' . htmlspecialchars($curso_atual['nome']) . '</strong>' : '' ?>.
                </p>
                <form method="POST" action="certificado.php">
                    <?php if ($id_curso > 0): ?>
                        <input type="hidden" name="id" value="<?= $id_curso ?>">
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="select_curso" class="form-label fw-semibold">Evento (opcional)</label>
                            <select id="select_curso" class="form-select" onchange="document.getElementById('id_curso_hidden').value=this.value">
                                <option value="">Todos os eventos</option>
                                <?php foreach ($certificado->listarCursosParaVitrine() as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" <?= $id_curso == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?> (<?= date('d/m/Y', strtotime($c['data_evento'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="id" id="id_curso_hidden" value="<?= $id_curso ?>">
                            <small class="text-muted">Se não souber o evento, deixe em "Todos" — buscamos em todo o seu histórico.</small>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mail da inscrição *</label>
                        <input type="email" name="email" id="email" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($email_query) ?>" placeholder="voce@exemplo.com"
                               required autocomplete="email">
                        <small class="text-muted mt-1 d-block">Use exatamente o e-mail que você cadastrou ao se inscrever no evento.</small>
                    </div>
                    <button type="submit" name="buscar_email" value="1" class="btn btn-humaniza w-100 py-2 btn-lg">
                        <i class="bi bi-envelope-search me-2"></i>Buscar meu certificado
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.8rem;">
            &copy; <?= date('Y') ?> Instituto Humaniza RR ·
            <a href="../politica-privacidade.php" class="text-decoration-none">Política de Privacidade</a> ·
            <a href="cursos.php" class="text-decoration-none">Cursos e Eventos</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
