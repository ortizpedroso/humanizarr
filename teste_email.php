<?php
/**
 * DIAGNÓSTICO DE ENVIO DE E-MAIL - Instituto Humaniza RR
 * ------------------------------------------------------
 * Uso: https://humanizamais.org.br/teste_email.php?key=RHfT3xK9mPqL2wZ8&para=seu@email.com
 *
 * Mostra o estado real das extensões (openssl), do método de envio
 * disponível (SMTP autenticado ou mail()) e faz um teste prático.
 * Ao final, clique no botão para apagar este arquivo do servidor.
 */
header('Content-Type: text/html; charset=UTF-8');

$KEY_ESPERADA = 'RHfT3xK9mPqL2wZ8';
if (($_GET['key'] ?? '') !== $KEY_ESPERADA) {
    http_response_code(403);
    exit('Acesso negado. Use ?key=<chave>.');
}

require_once __DIR__ . '/config/ConfiguracaoEmail.php';

$para   = trim($_GET['para'] ?? '');
$envio  = null; // true | false | string(erro)
$resultado = '';

// Pré-requisitos reais do servidor
$exts = [
    'openssl' => extension_loaded('openssl'), // exigida pelo PHPMailer SMTPS/TLS
    'gd'      => extension_loaded('gd'),      // usada só se houver arte PNG/JPG no PDF
    'mbstring'=> extension_loaded('mbstring'),
];
$smtp_ok  = ConfiguracaoEmail::smtpConfigurado();
$mail_ok  = function_exists('mail') && stripos(PHP_SAPI, 'cli') === false;

if ($para !== '' && filter_var($para, FILTER_VALIDATE_EMAIL)) {
    $metodo_desc = $smtp_ok ? 'PHPMailer via SMTP autenticado' : 'mail() do servidor';
    $assunto = '=?UTF-8?B?' . base64_encode('[Diagnóstico] Teste de e-mail - Humaniza Mais') . '?=';
    $corpo   = "<p>Teste enviado em " . date('d/m/Y H:i:s') . " por teste_email.php.</p>"
             . "<p>Se você recebeu este e-mail, o método <strong>" . htmlspecialchars($metodo_desc) . "</strong> funciona neste servidor.</p>";
    if ($smtp_ok) {
        // Via PHPMailer (SMTP autenticado)
        try {
            require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = ConfiguracaoEmail::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = ConfiguracaoEmail::SMTP_USER;
            $mail->Password   = ConfiguracaoEmail::SMTP_PASS;
            $mail->SMTPSecure = ConfiguracaoEmail::SMTP_SECURE === 'tls'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = ConfiguracaoEmail::SMTP_PORT;
            $mail->setFrom(ConfiguracaoEmail::SMTP_USER, ConfiguracaoEmail::SITE_NAME);
            $mail->addAddress($para);
            $mail->isHTML(true);
            $mail->Subject = '[Diagnóstico] Teste de e-mail - Humaniza Mais';
            $mail->Body    = $corpo;
            $mail->send();
            $envio = true;
        } catch (\Throwable $e) {
            $envio = 'PHPMailer: ' . $e->getMessage();
        }
    } elseif ($mail_ok) {
        $mime = ConfiguracaoEmail::headersMail($corpo);
        $envio = @mail($para, $assunto, $mime['body'], $mime['headers'], '-f' . ConfiguracaoEmail::FROM_EMAIL);
        if ($envio === false) {
            $envio = 'mail() retornou false — confira SPF/PTR do domínio e a conta remetente no cPanel.';
        }
    } else {
        $envio = 'Nenhum método disponível (SMTP não configurado e mail() ausente).';
    }
    $resultado = $envio === true
        ? '<div class="ok">✔ E-mail aceito pelo servidor para <b>' . htmlspecialchars($para) . '</b>. Confira a caixa de entrada e o spam.</div>'
        : '<div class="erro">✘ Falha: ' . htmlspecialchars(is_string($envio) ? $envio : 'desconhecida') . '</div>';
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">
<title>Diagnóstico de E-mail</title>
<style>body{font-family:Arial,sans-serif;max-width:760px;margin:30px auto;padding:0 15px;color:#333}
h1{color:#E30613}.ok{background:#e6f6e6;border:1px solid #28a745;padding:12px;border-radius:8px}
.erro{background:#fdecea;border:1px solid #dc3545;padding:12px;border-radius:8px}
table{border-collapse:collapse;width:100%;margin:15px 0}td,th{border:1px solid #ddd;padding:8px;text-align:left}
.liga{color:#28a745;font-weight:bold}.desliga{color:#dc3545;font-weight:bold}
form{margin:20px 0}input[type=email]{padding:8px;width:320px}button{padding:8px 16px;background:#E30613;color:#fff;border:0;border-radius:6px;cursor:pointer}</style>
</head><body>
<h1>📧 Diagnóstico de envio de e-mail</h1>
<table>
<tr><th>Item</th><th>Estado</th></tr>
<tr><td>Extensão <b>openssl</b> (necessária p/ SMTP do PHPMailer)</td>
    <td class="<?= $exts['openssl'] ? 'liga' : 'desliga' ?>"><?= $exts['openssl'] ? 'ATIVADA ✔' : 'DESATIVADA ✘ — ative em hPanel > PHP & MySQL > PHP > Extensões' ?></td></tr>
<tr><td>Função <b>mail()</b> do servidor</td>
    <td class="<?= $mail_ok ? 'liga' : 'desliga' ?>"><?= $mail_ok ? 'DISPONÍVEL ✔' : 'INDISPONÍVEL ✘' ?></td></tr>
<tr><td>SMTP autenticado (ConfiguracaoEmail.php)</td>
    <td class="<?= $smtp_ok ? 'liga' : 'desliga' ?>"><?= $smtp_ok ? 'CONFIGURADO ✔ (' . htmlspecialchars(ConfiguracaoEmail::SMTP_HOST) . ')' : 'não configurado (_CHANGE_ME_) → usando fallback mail()' ?></td></tr>
<tr><td>Método que será usado pelos certificados</td>
    <td><b><?= $smtp_ok ? 'PHPMailer via SMTP' : ($mail_ok ? 'mail() (sendmail Hostinger)' : 'NENHUM ✘') ?></b></td></tr>
<tr><td>Remetente atual</td><td><?= htmlspecialchars(ConfiguracaoEmail::FROM_EMAIL) ?></td></tr>
</table>

<?= $resultado ?>

<form method="GET">
    <input type="hidden" name="key" value="<?= $KEY_ESPERADA ?>">
    <input type="email" name="para" placeholder="seu@email.com" required>
    <button type="submit">Enviar e-mail de teste</button>
</form>

<p><small>⚠️ Após o diagnóstico, <b>apague este arquivo</b> do servidor (Gerenciador de Arquivos da Hostinger).</small></p>
</body></html>
