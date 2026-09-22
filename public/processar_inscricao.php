<?php
session_start();

require_once '../config/Database.php';
require_once '../models/Curso.php';
require_once '../models/Inscricao.php';

$database = new Database();
$db = $database->getConnection();

$curso_model = new Curso($db);
$inscricao_model = new Inscricao($db);

// Verifica se ID do curso foi passado
if (!isset($_GET['id_curso']) || empty($_GET['id_curso'])) {
    $_SESSION['erro_inscricao'] = "Curso não informado.";
    header("Location: inscricao.php");
    exit;
}

$id_curso = (int)$_GET['id_curso'];

// Verifica se o curso existe e está aberto
$curso = $curso_model->lerPorId($id_curso);
if (!$curso || $curso['status'] != 'Aberto') {
    $_SESSION['erro_inscricao'] = "Inscrições encerradas para este curso.";
    header("Location: inscricao.php?id=" . $id_curso);
    exit;
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação dos campos obrigatórios
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['telefone']) || 
        empty($_POST['tipo']) || empty($_POST['instituicao']) || empty($_POST['semestre_atuacao'])) {
        $_SESSION['erro_inscricao'] = "Preencha todos os campos obrigatórios.";
        header("Location: inscricao.php?id=" . $id_curso);
        exit;
    }

    // Valida e-mail
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_inscricao'] = "Informe um e-mail válido.";
        header("Location: inscricao.php?id=" . $id_curso);
        exit;
    }

    // Prepara dados
    $dados = [
        'nome' => $_POST['nome'],
        'email' => $_POST['email'],
        'telefone' => $_POST['telefone'],
        'tipo' => $_POST['tipo'],
        'instituicao' => $_POST['instituicao'],
        'semestre_atuacao' => $_POST['semestre_atuacao']
    ];

    // Processa a inscrição
    if ($inscricao_model->processarInscricao($dados, $id_curso)) {
        // SUCESSO - Enviar e-mail de confirmação
        
        // =========================================================================
        // CONFIGURAÇÃO DO PHPMAILER - ALTERE AQUI COM SEUS DADOS DA HOSTINGER
        // =========================================================================
        
        /* Descomente e configure as linhas abaixo para enviar e-mails:
        
        require_once '../PHPMailer/src/PHPMailer.php';
        require_once '../PHPMailer/src/SMTP.php';
        require_once '../PHPMailer/src/Exception.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        
        $mail = new PHPMailer(true);
        
        try {
            // Configurações do servidor SMTP (Hostinger)
            $mail->isSMTP();
            $mail->Host       = 'smtp.seudominio.com.br';  // ALTERE: Seu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'eventos@seudominio.com.br';  // ALTERE: E-mail profissional criado no cPanel
            $mail->Password   = 'sua_senha_aqui';             // ALTERE: Senha do e-mail acima
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;                          // 465 (SSL) ou 587 (TLS)
            
            // Destinatários
            $mail->setFrom('eventos@seudominio.com.br', 'Instituto Humaniza RR');
            $mail->addAddress($dados['email'], $dados['nome']);
            
            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Inscrição - ' . htmlspecialchars($curso['nome']);
            $mail->Body    = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #E30613; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                        .info-box { background: white; border-left: 4px solid #E30613; padding: 15px; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>Instituto Humaniza RR</h1>
                            <p>Inscrição Confirmada!</p>
                        </div>
                        <div class="content">
                            <p>Olá, <strong>' . htmlspecialchars($dados['nome']) . '</strong>!</p>
                            
                            <p>Sua inscrição no evento abaixo foi confirmada com sucesso:</p>
                            
                            <div class="info-box">
                                <h3 style="margin-top: 0;">' . htmlspecialchars($curso['nome']) . '</h3>
                                <p><strong>📅 Data:</strong> ' . date('d/m/Y \à\s H:i', strtotime($curso['data_evento'])) . 'h</p>
                                <p><strong>⏰ Duração:</strong> ' . htmlspecialchars($curso['duracao']) . '</p>
                                <p><strong>📍 Local:</strong> ' . htmlspecialchars($curso['local']) . '</p>
                                <p><strong>🎤 Palestrante:</strong> ' . htmlspecialchars($curso['palestrante']) . '</p>
                            </div>
                            
                            <p>Contamos com sua presença! Chegue com 15 minutos de antecedência.</p>
                            
                            <p>Atenciosamente,<br>
                            <strong>Equipe Instituto Humaniza RR</strong></p>
                        </div>
                        <div class="footer">
                            <p>&copy; 2025 Instituto Humaniza RR. Todos os direitos reservados.</p>
                            <p>Presidente: ' . htmlspecialchars($curso['presidente']) . ' | Vice: ' . htmlspecialchars($curso['vice_presidente']) . '</p>
                        </div>
                    </div>
                </body>
                </html>
            ';
            
            // Envia o e-mail
            $mail->send();
            
        } catch (Exception $e) {
            // Erro ao enviar e-mail, mas a inscrição foi salva
            error_log("Erro ao enviar e-mail de confirmação: {$mail->ErrorInfo}");
            // Não interrompe o fluxo, apenas loga o erro
        }
        
        */
        // =========================================================================
        // FIM DA CONFIGURAÇÃO DO PHPMAILER
        // =========================================================================

        $_SESSION['sucesso_inscricao'] = true;
        header("Location: inscricao.php?id=" . $id_curso);
        exit;
        
    } else {
        // Erro ao processar inscrição (provavelmente já inscrito)
        $_SESSION['erro_inscricao'] = "Este e-mail já está inscrito neste curso. Verifique sua caixa de entrada.";
        header("Location: inscricao.php?id=" . $id_curso);
        exit;
    }
}

// Se chegou aqui, redireciona
header("Location: inscricao.php?id=" . $id_curso);
exit;
?>
