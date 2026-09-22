<?php
/**
 * PÁGINA DE CONTATO - INSTITUTO HUMANIZA RR
 * Configuração: SMTP Autenticado (Hostinger)
 * Versão: Final com Resposta Automática + Fade Out da Mensagem
 */

// --- CARREGAMENTO DO PHPMAILER ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega os arquivos da biblioteca
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// --- CONEXÃO COM BANCO DE DADOS ---
require_once __DIR__ . '/config/Database.php'; 

date_default_timezone_set('America/Boa_Vista');

$database = new Database();
$db = $database->getConnection();
$msg_feedback = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Sanitização
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email_cliente = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $assunto = filter_input(INPUT_POST, 'assunto', FILTER_SANITIZE_SPECIAL_CHARS);
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($nome && $email_cliente && $mensagem) {
        
        $mail = new PHPMailer(true);

        try {
            // ==========================================
            // 1. CONFIGURAÇÃO DO SERVIDOR (Geral)
            // ==========================================
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contato@humanizamais.org.br'; 
            $mail->Password   = 'H@i2026*#';                // <--- SUA SENHA
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;                            
            $mail->CharSet    = 'UTF-8';
            $mail->isHTML(true);

            // ==========================================
            // 2. ENVIO 1: PARA O INSTITUTO (Admin)
            // ==========================================
            
            // Quem envia: O sistema
            $mail->setFrom('contato@humanizamais.org.br', 'Instituto Humaniza RR');
            
            // Quem recebe: A administração
            $mail->addAddress('contato@humanizamais.org.br', 'Administração');
            
            // Se o admin clicar em "Responder", vai para o cliente
            $mail->addReplyTo($email_cliente, $nome);

            $mail->Subject = "Site: $assunto";
            
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>
                <h3 style='color: #d9534f; border-bottom: 2px solid #d9534f; padding-bottom: 10px;'>Nova Mensagem do Site</h3>
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>E-mail:</strong> $email_cliente</p>
                <p><strong>Assunto:</strong> $assunto</p>
                <div style='background-color: #f9f9f9; padding: 15px; border-left: 4px solid #d9534f; margin: 20px 0;'>
                    <strong>Mensagem:</strong><br>$mensagem
                </div>
                <p style='font-size: 12px; color: #777;'>Enviado em: " . date('d/m/Y H:i') . "</p>
            </div>";
            
            $mail->AltBody = "Nome: $nome\nE-mail: $email_cliente\nMensagem: $mensagem";

            // Envia o primeiro e-mail
            $mail->send();


            // ==========================================
            // 3. ENVIO 2: PARA O CLIENTE (Confirmação)
            // ==========================================
            
            // Limpa os destinatários anteriores (Admin) e o ReplyTo anterior
            $mail->clearAddresses();
            $mail->clearReplyTos();

            // Configura para o Cliente
            $mail->addAddress($email_cliente, $nome); // Vai para quem preencheu o form
            $mail->addReplyTo('contato@humanizamais.org.br', 'Instituto Humaniza RR'); // Se o cliente responder, volta pro instituto

            $mail->Subject = "Recebemos seu contato - Instituto Humaniza RR";

            // Lógica de saudação
            $hora = date('H');
            $saudacao = ($hora < 12) ? "Bom dia" : (($hora < 18) ? "Boa tarde" : "Boa noite");

            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>
                <h3 style='color: #d9534f;'>Olá, $nome!</h3>
                <p>$saudacao.</p>
                <p>Agradecemos o seu contato com o <strong>Instituto Humaniza RR</strong>.</p>
                <p>Recebemos sua mensagem sobre '<strong>$assunto</strong>' e em breve nossa equipe retornará.</p>
                <br>
                <p>Atenciosamente,<br><strong>Equipe Instituto Humaniza RR</strong></p>
            </div>";

            $mail->AltBody = "Olá $nome. Recebemos seu contato sobre '$assunto' e retornaremos em breve.\nEquipe Instituto Humaniza RR.";

            // Envia o segundo e-mail
            $mail->send();
            
            // ==========================================
            // FIM DO PROCESSO
            // ==========================================

            // Adicionei a classe 'fade-msg' para identificar no Javascript
            $msg_feedback = "<div class='alert alert-success shadow-sm text-center mb-4 fade-msg'><i class='bi bi-check-circle-fill me-2'></i>Sua mensagem foi enviada com sucesso! Verifique seu e-mail.</div>";

        } catch (Exception $e) {
            $msg_feedback = "<div class='alert alert-danger shadow-sm text-center mb-4 fade-msg'><i class='bi bi-exclamation-triangle-fill me-2'></i>Erro ao enviar. Tente novamente mais tarde.</div>";
        }
    }
}

// --- BUSCA DADOS DO RODAPÉ ---
try {
    $redes_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
    $redes = [];
    foreach($redes_raw as $r) { 
        $redes[$r['chave']] = $r['valor']; 
    }
} catch(Exception $e) { 
    $redes = []; 
}

include __DIR__ . '/public/includes/header.php'; 
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2">Fale Conosco</h1>
        <p class="text-white-50 mb-0 fs-5">Estamos prontos para ouvir você, tirar dúvidas ou receber sua doação</p>
    </div>
</div>

<section class="bg-light py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <h3 class="fw-bold mb-4" style="color: var(--h-red);">Informações de Contato</h3>
                    
                    <div class="d-flex mb-4">
                        <div class="icon-box bg-danger bg-opacity-10 p-3 rounded-3 me-3 text-danger" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Endereço</h6>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($redes['endereco'] ?? 'Roraima, Brasil') ?>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="icon-box bg-danger bg-opacity-10 p-3 rounded-3 me-3 text-danger" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-whatsapp fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">WhatsApp</h6>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($redes['whatsapp'] ?? '(95) 99999-9999') ?>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="icon-box bg-danger bg-opacity-10 p-3 rounded-3 me-3 text-danger" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-envelope fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">E-mail</h6>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($redes['email'] ?? 'contato@humanizamais.org.br') ?>
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Redes Sociais</h5>
                    <div class="d-flex gap-3">
                        <?php if(!empty($redes['facebook'])): ?>
                            <a href="<?= $redes['facebook'] ?>" target="_blank" class="btn btn-outline-danger border-2 rounded-circle"><i class="bi bi-facebook"></i></a>
                        <?php endif; ?>
                        
                        <?php if(!empty($redes['instagram'])): ?>
                            <a href="<?= $redes['instagram'] ?>" target="_blank" class="btn btn-outline-danger border-2 rounded-circle"><i class="bi bi-instagram"></i></a>
                        <?php endif; ?>
                        
                        <?php if(!empty($redes['youtube'])): ?>
                            <a href="<?= $redes['youtube'] ?>" target="_blank" class="btn btn-outline-danger border-2 rounded-circle"><i class="bi bi-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">
                    <h3 class="fw-bold mb-4">Envie uma Mensagem</h3>
                    
                    <div id="feedback-container">
                        <?= $msg_feedback ?>
                    </div>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">NOME COMPLETO</label>
                                <input type="text" name="nome" class="form-control py-2" placeholder="Seu nome" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">E-MAIL</label>
                                <input type="email" name="email" class="form-control py-2" placeholder="seu@email.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">ASSUNTO</label>
                                <input type="text" name="assunto" class="form-control py-2" placeholder="Ex: Doações, Voluntariado..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">MENSAGEM</label>
                                <textarea name="mensagem" class="form-control" rows="5" placeholder="Como podemos ajudar?" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow">ENVIAR AGORA</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Seleciona o elemento de alerta
        var alertBox = document.querySelector(".fade-msg");
        
        // Se a mensagem existir na tela
        if (alertBox) {
            // Espera 5 segundos (5000 milissegundos)
            setTimeout(function() {
                // Adiciona transição suave
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.style.opacity = "0";

                // Remove do HTML após a transição visual terminar
                setTimeout(function() {
                    alertBox.remove();
                }, 500); // 0.5s para bater com a transição
            }, 5000);
        }
    });
</script>

<?php include __DIR__ . '/public/includes/footer.php'; ?>