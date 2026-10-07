<?php
/**
 * CertificadoService - Emissão de Certificados Digitais (Instituto Humaniza RR)
 * -----------------------------------------------------------------------------
 * Camada de SERVIÇO da arquitetura orientada a objetos do projeto:
 *   config/     -> conexão e configuração
 *   models/     -> acesso a dados (PDO)
 *   services/   -> regras de negócio (este arquivo)
 *   public/     -> controllers finos (HTML + orquestração)
 *   lib/fpdf    -> biblioteca de PDF
 *
 * Responsabilidades:
 *  - Validar se um certificado pode ser emitido (habilitação, status, período);
 *  - Montar e gerar o PDF (FPDF), com arte enviada no painel ou layout padrão;
 *  - Gerar/validar o código público de autenticação do certificado;
 *  - Enviar o certificado por e-mail (PHPMailer) com agradecimento + anexo.
 */

require_once __DIR__ . '/../lib/fpdf/fpdf.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Inscricao.php';

class CertificadoService
{
    /** Vermelho institucional #E30613 */
    private const COR_PRIMARIA = [227, 6, 19];

    /** @var PDO */
    private $db;

    /** @var Curso */
    private $cursoModel;

    /** @var Inscricao */
    private $inscricaoModel;

    public function __construct(PDO $db)
    {
        $this->db             = $db;
        $this->cursoModel     = new Curso($db);
        $this->inscricaoModel = new Inscricao($db);
    }

    // =====================================================================
    // CONSULTAS / REGRAS DE NEGÓCIO
    // =====================================================================

    /** Busca uma inscrição para o certificado (delegação ao model). */
    public function buscarInscricao(array $filtros)
    {
        return $this->inscricaoModel->buscarInscricaoParaCertificado($filtros);
    }

    /**
     * Regra central: o certificado pode ser emitido?
     * @return string|null null = pode emitir; string = mensagem de bloqueio
     */
    public function motivoBloqueio(array $inscricao)
    {
        if (empty($inscricao['emitir_certificado'])) {
            return 'A emissão de certificados deste evento ainda não foi habilitada pela organização.';
        }
        if (($inscricao['status_inscricao'] ?? '') !== 'Confirmada') {
            return 'Esta inscrição não está confirmada. Entre em contato com a organização.';
        }
        $fim = $this->dataTerminoEvento((int)($inscricao['id_curso'] ?? 0));
        if ($fim !== null && $fim > time()) {
            return 'O certificado fica disponível após o encerramento do evento ('
                 . date('d/m/Y', $fim) . ').';
        }
        return null;
    }

    public function podeEmitir(array $inscricao)
    {
        return $this->motivoBloqueio($inscricao) === null;
    }

    /** Timestamp do término do evento (data_fim_evento, senão data_evento). */
    public function dataTerminoEvento(int $id_curso)
    {
        if ($id_curso <= 0) {
            return null;
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(data_fim_evento, data_evento) AS fim FROM cursos WHERE id = :id"
            );
            $stmt->bindValue(':id', $id_curso, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($row && !empty($row['fim'])) ? strtotime($row['fim']) : null;
        } catch (\Throwable $e) {
            error_log('CertificadoService::dataTerminoEvento: ' . $e->getMessage());
            return null;
        }
    }

    /** Cursos publicados para a vitrine pública (delegação ao model). */
    public function listarCursosParaVitrine()
    {
        return $this->cursoModel->lerParaVitrine();
    }

    /** Data do evento formatada (suporta período início → fim). */
    public function formatarDataEvento($dados)
    {
        $ini = !empty($dados['data_evento']) ? strtotime($dados['data_evento']) : null;
        if (!$ini) {
            return '';
        }
        $fim_ts = null;
        if (!empty($dados['id_curso'])) {
            try {
                $stmt = $this->db->prepare("SELECT data_fim_evento FROM cursos WHERE id = :id");
                $stmt->bindValue(':id', (int)$dados['id_curso'], PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['data_fim_evento'])) {
                    $fim_ts = strtotime($row['data_fim_evento']);
                }
            } catch (\Throwable $e) { /* curso sem registro no banco (ex.: período não cadastrado) */ }
        }
        if ($fim_ts && $fim_ts > $ini) {
            if (date('d/m/Y', $ini) === date('d/m/Y', $fim_ts)) {
                return date('d/m/Y', $ini);
            }
            return date('d/m/Y', $ini) . ' a ' . date('d/m/Y', $fim_ts);
        }
        return date('d/m/Y', $ini);
    }

    // =====================================================================
    // AUTENTICAÇÃO PÚBLICA DO CERTIFICADO
    // =====================================================================

    public static function gerarCodigoValidacao($id_inscricao, $email)
    {
        return strtoupper(substr(
            hash('sha256', 'HUMANIZA|' . (int)$id_inscricao . '|' . strtolower(trim((string)$email))),
            0,
            12
        ));
    }

    /**
     * Mascara um e-mail para exibição segura (ex.: jo***@dominio.com).
     */
    public static function mascararEmail($email)
    {
        $email = trim((string)$email);
        if (strpos($email, '@') === false) {
            return $email;
        }
        list($local, $dominio) = explode('@', $email, 2);
        $len = strlen($local);
        $visivel = substr($local, 0, min(2, $len));
        return $visivel . str_repeat('*', max(3, $len - 2)) . '@' . $dominio;
    }

    /**
     * Valida um código público de certificado.
     * @return array|false ['id_inscricao' => int, 'email' => string] ou false
     */
    public function validarCodigo($codigo)
    {
        $codigo = strtoupper(trim((string)$codigo));
        if ($codigo === '') {
            return false;
        }
        $stmt = $this->db->query(
            "SELECT insc.id AS id_inscricao, i.email
             FROM inscricoes insc
             INNER JOIN inscritos i ON insc.id_inscrito = i.id
             WHERE insc.status = 'Confirmada'"
        );
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (self::gerarCodigoValidacao($row['id_inscricao'], $row['email']) === $codigo) {
                return $row;
            }
        }
        return false;
    }

    // =====================================================================
    // GERAÇÃO DO PDF
    // =====================================================================

    /** Converte UTF-8 para latin1 sem usar utf8_decode (deprecado no PHP 8.2). */
    private static function txt($s)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', (string)$s);
    }

    /** Nome padronizado do arquivo PDF. */
    public static function nomeArquivo($nome)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', (string)$nome);
        return 'certificado-' . strtolower(trim($slug, '-')) . '.pdf';
    }

    /** URL pública base (detecta o host atual). */
    private function urlBase()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'humanizarr.org.br';
        $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/public')), '/');
        return $scheme . '://' . $host . $dir;
    }

    /**
     * Monta o objeto FPDF do certificado (não envia ao browser).
     * @param array $dados Inscrição vinda de buscarInscricao()
     */
    public function montarPdf(array $dados)
    {
        $pdf = new class('L', 'mm', 'A4') extends FPDF {
            public $arte_fundo = null;
            public $url_valida = '';

            public function Header()
            {
                if ($this->arte_fundo) {
                    $this->Image($this->arte_fundo, 0, 0, 297, 210);
                } else {
                    $this->SetFillColor(227, 6, 19);
                    $this->Rect(0, 8, 297, 2, 'F');
                    $this->Rect(0, 200, 297, 2, 'F');
                    $this->SetFont('Helvetica', 'B', 20);
                    $this->SetTextColor(45, 45, 45);
                    $this->Cell(0, 10, 'INSTITUTO HUMANIZA RR', 0, 1, 'C');
                }
            }
            public function Footer()
            {
                if (!$this->arte_fundo) {
                    $this->SetY(-20);
                    $this->SetFont('Helvetica', '', 8);
                    $this->SetTextColor(120, 120, 120);
                    $this->Cell(0, 5, 'Documento gerado eletronicamente - valide em: ' . $this->url_valida, 0, 0, 'C');
                }
            }
        };

        // Arte de fundo enviada no painel (somente imagens)
        $arte = null;
        if (!empty($dados['certificado_arquivo'])) {
            $caminho = __DIR__ . '/../public/uploads/' . basename($dados['certificado_arquivo']);
            $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
            if (file_exists($caminho) && in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $arte = $caminho;
            }
        }
        $pdf->arte_fundo = $arte;

        $nome       = html_entity_decode($dados['nome'], ENT_QUOTES, 'UTF-8');
        $evento     = html_entity_decode($dados['curso_nome'], ENT_QUOTES, 'UTF-8');
        $duracao    = html_entity_decode($dados['duracao'] ?? '', ENT_QUOTES, 'UTF-8');
        $local      = html_entity_decode($dados['local'] ?? '', ENT_QUOTES, 'UTF-8');
        $presidente = html_entity_decode($dados['presidente'] ?? '', ENT_QUOTES, 'UTF-8');
        $vice       = html_entity_decode($dados['vice_presidente'] ?? '', ENT_QUOTES, 'UTF-8');
        $data_fmt   = $this->formatarDataEvento($dados);
        $hash       = self::gerarCodigoValidacao($dados['id_inscricao'], $dados['email']);

        $pdf->url_valida = $this->urlBase() . '/certificado.php?validar=' . $hash;

        $frase = 'concluiu com sucesso sua participação no evento "' . $evento . '"'
               . ($data_fmt ? ', realizado em ' . $data_fmt : '')
               . ($duracao ? ', com carga horária de ' . $duracao : '')
               . ($local ? ', no local: ' . $local : '') . '.';

        $pdf->AddPage();
        $pdf->SetTextColor(45, 45, 45);

        if ($arte) {
            $pdf->SetFont('Times', 'B', 14);
            $pdf->SetXY(30, 95);
            $pdf->Cell(237, 8, self::txt('CERTIFICADO'), 0, 1, 'C');

            $pdf->SetFont('Times', 'B', 22);
            $pdf->SetXY(30, 108);
            $pdf->Cell(237, 10, self::txt($nome), 0, 1, 'C');

            $pdf->SetFont('Times', '', 13);
            $pdf->SetXY(30, 122);
            $pdf->MultiCell(237, 7, self::txt($frase), 0, 'C');
        } else {
            $pdf->SetFont('Times', 'B', 26);
            $pdf->SetY(45);
            $pdf->Cell(0, 12, self::txt('CERTIFICADO'), 0, 1, 'C');

            $pdf->SetFont('Times', '', 14);
            $pdf->SetY(65);
            $pdf->Cell(0, 8, self::txt('O Instituto Humaniza RR certifica que'), 0, 1, 'C');

            $pdf->SetFont('Times', 'B', 22);
            $pdf->SetY(80);
            $pdf->Cell(0, 10, self::txt($nome), 0, 1, 'C');

            $pdf->SetDrawColor(...self::COR_PRIMARIA);
            $pdf->SetLineWidth(0.4);
            $pdf->Line(60, 93, 237, 93);

            $pdf->SetFont('Times', '', 14);
            $pdf->SetY(100);
            $pdf->MultiCell(0, 8, self::txt($frase), 0, 'C');

            $pdf->SetY(148);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Cell(148, 6, self::txt($presidente), 0, 1, 'C');
            $pdf->SetFont('Times', '', 11);
            $pdf->Cell(148, 6, self::txt('Presidente'), 0, 1, 'C');

            $pdf->SetY(168);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Cell(148, 6, self::txt($vice), 0, 1, 'C');
            $pdf->SetFont('Times', '', 11);
            $pdf->Cell(148, 6, self::txt('Vice-Presidente'), 0, 1, 'C');

            $pdf->SetY(186);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->Cell(0, 6, self::txt('Código de validação: ' . $hash), 0, 1, 'C');
        }

        return $pdf;
    }

    /**
     * Envia o PDF ao navegador.
     * @param string $modo 'D' = download | 'I' = inline (impressão em nova aba)
     */
    public function enviarPdf(array $dados, $modo = 'D')
    {
        $this->montarPdf($dados)->Output($modo, self::nomeArquivo($dados['nome']));
    }

    /** Conteúdo binário do PDF (para anexo de e-mail). */
    public function gerarPdfString(array $dados)
    {
        return $this->montarPdf($dados)->Output('S');
    }

    // =====================================================================
    // ENVIO POR E-MAIL (PHPMailer)
    // =====================================================================

    /**
     * Envia o certificado em PDF para o e-mail da inscrição, com mensagem
     * de agradecimento e link de validação.
     * @return bool|null true = enviado; false = falhou; null = SMTP não configurado
     */
    public function enviarEmail(array $inscricao)
    {
        require_once __DIR__ . '/../config/ConfiguracaoEmail.php';

        // 1) SMTP autenticado configurado -> PHPMailer
        if (ConfiguracaoEmail::smtpConfigurado()) {
            return $this->enviarEmailSmtp($inscricao);
        }

        // 2) Fallback: mail() do servidor (sendmail da Hostinger),
        //    com PDF em anexo via MIME multipart montado por ConfiguracaoEmail.
        if (function_exists('mail')) {
            try {
                $pdf      = $this->gerarPdfString($inscricao);
                $arquivo  = self::nomeArquivo($inscricao['nome']);
                $assunto  = 'Seu certificado - '
                          . html_entity_decode($inscricao['curso_nome'], ENT_QUOTES, 'UTF-8');
                $mime     = ConfiguracaoEmail::headersMail(
                    $this->corpoEmail($inscricao), $pdf, $arquivo
                );
                // encodeURI do assunto para acentos corretos
                $assuntoEnc = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
                $ok = @mail(
                    $inscricao['email'],
                    $assuntoEnc,
                    $mime['body'],
                    $mime['headers'],
                    '-f' . ConfiguracaoEmail::FROM_EMAIL
                );
                if ($ok) {
                    return true;
                }
                error_log('CertificadoService: mail() retornou false (verifique SPF/PTR do domínio).');
                return false;
            } catch (\Throwable $e) {
                error_log('Erro ao enviar certificado via mail(): ' . $e->getMessage());
                return false;
            }
        }

        error_log('CertificadoService: nenhum método de envio disponível (SMTP e mail() ausentes).');
        return null;
    }

    /** Envio via SMTP autenticado (PHPMailer). */
    private function enviarEmailSmtp(array $inscricao)
    {
        try {
            require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
            require_once __DIR__ . '/../PHPMailer/src/Exception.php';

            $pdf_string = $this->gerarPdfString($inscricao);
            $filename   = self::nomeArquivo($inscricao['nome']);

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
            $mail->addAddress($inscricao['email'], $inscricao['nome']);
            $mail->isHTML(true);
            $mail->Subject = 'Seu certificado - '
                           . html_entity_decode($inscricao['curso_nome'], ENT_QUOTES, 'UTF-8');
            $mail->addStringAttachment($pdf_string, $filename, 'base64', 'application/pdf');
            $mail->Body = $this->corpoEmail($inscricao);
            $mail->send();

            return true;
        } catch (\Throwable $e) {
            error_log('Erro ao enviar certificado por e-mail: ' . $e->getMessage());
            return false;
        }
    }

    /** Template HTML do e-mail de agradecimento com o certificado. */
    private function corpoEmail(array $inscricao)
    {
        $h = function ($s) {
            return htmlspecialchars(html_entity_decode((string)$s, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        };

        $nome   = $h($inscricao['nome']);
        $evento = $h($inscricao['curso_nome']);
        $data   = $h($this->formatarDataEvento($inscricao));
        $carga  = $h($inscricao['duracao'] ?? '');
        $hash   = self::gerarCodigoValidacao($inscricao['id_inscricao'], $inscricao['email']);
        $base   = $this->urlBase();
        $site   = $h(ConfiguracaoEmail::SITE_NAME);

        $link_cert  = $base . '/certificado.php?inscricao=' . (int)$inscricao['id_inscricao'];
        $link_valid = $base . '/certificado.php?validar=' . $hash;

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>'
            . '<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;">'
            . '<div style="max-width:600px;margin:0 auto;">'
            . '<div style="background:#E30613;color:#fff;padding:30px;text-align:center;border-radius:8px 8px 0 0;">'
            . '<h1 style="margin:0;font-size:24px;">' . $site . '</h1>'
            . '<p style="margin:6px 0 0;">Muito obrigado por fazer parte deste evento!</p></div>'
            . '<div style="background:#f9f9f9;padding:30px;border-radius:0 0 8px 8px;">'
            . '<p>Olá, <strong>' . $nome . '</strong>!</p>'
            . '<p>Agradecemos imensamente pela sua participação no evento <strong>"' . $evento . '"</strong>'
            . ($data ? ', realizado em ' . $data : '') . ($carga ? ', com carga horária de ' . $carga : '')
            . '. Sua presença fez a diferença na nossa missão de humanizar a saúde.</p>'
            . '<p>Segue em anexo o seu <strong>Certificado Digital em PDF</strong>. '
            . 'Você pode imprimi-lo diretamente ou salvá-lo onde quiser.</p>'
            . '<div style="background:#fff;border-left:4px solid #E30613;padding:15px;margin:20px 0;">'
            . '<p style="margin:0;"><strong>Código de validação:</strong> ' . $hash . '<br>'
            . '<a href="' . $link_valid . '">Verificar autenticidade do certificado</a></p></div>'
            . '<p style="text-align:center;margin:25px 0;">'
            . '<a href="' . $link_cert . '" style="background:#E30613;color:#fff;padding:12px 25px;'
            . 'border-radius:50px;text-decoration:none;font-weight:bold;">Acessar meu certificado online</a></p>'
            . '<p>Atenciosamente,<br><strong>Equipe ' . $site . '</strong></p></div>'
            . '<div style="text-align:center;padding:20px;color:#666;font-size:12px;">'
            . '&copy; ' . date('Y') . ' ' . $site
            . ' · Este certificado foi gerado eletronicamente.</div>'
            . '</div></body></html>';
    }
}
