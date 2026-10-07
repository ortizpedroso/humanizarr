<?php
/**
 * ConfiguracaoEmail - Credenciais SMTP centrais (Instituto Humaniza RR)
 * ---------------------------------------------------------------------
 * Preencha os dados criados no cPanel da Hostinger (Contas E-mail).
 * Enquanto as constantes contiverem '_CHANGE_ME_', o envio de e-mails
 * é automaticamente desativado (o certificado continua disponível
 * para impressão/download na página pública).
 */

class ConfiguracaoEmail
{
    /**
     * Domínio do site (usado para montar o SMTP padrão da Hostinger
     * e o remetente fallback). Ajuste se o domínio mudar.
     */
    const SITE_DOMAIN = 'humanizamais.org.br';
    const SITE_NAME   = 'Instituto Humaniza RR';

    /*
     * OPÇÃO RECOMENDADA: crie a conta no cPanel da Hostinger
     * (Emails -> Contas de Email) e preencha as três constantes abaixo.
     * Em quanto tiverem '_CHANGE_ME_', o sistema tenta enviar pela
     * função mail() do servidor (sendmail da Hostinger) com remetente
     * automatico abaixo.
     */
    /*
     * Hostinger: smtp.hostinger.com funciona nas duas portas abaixo.
     * Para ativar o envio por SMTP autenticado, basta preencher
     * SMTP_USER e SMTP_PASS com a conta criada no cPanel
     * (Emails -> Contas de Email). O HOST já está correto.
     */
    const SMTP_HOST   = 'smtp.hostinger.com';
    const SMTP_USER   = '_CHANGE_ME_'; // ex.: 'no-reply@humanizamais.org.br'
    const SMTP_PASS   = '_CHANGE_ME_'; // senha da conta criada no cPanel
    const SMTP_PORT   = 465;           // 465 (SSL/TLS) ou 587 (STARTTLS/TLS)
    const SMTP_SECURE = 'ssl';         // 'ssl' p/ porta 465, 'tls' p/ porta 587

    /** Remetente padrão (fallback quando SMTP não está configurado). */
    const FROM_EMAIL = 'no-reply@humanizamais.org.br';
    const FROM_NAME  = 'Instituto Humaniza RR';

    /** true = usar SMTP autenticado; false = tentar via mail() do servidor. */
    public static function smtpConfigurado()
    {
        return strpos(self::SMTP_HOST, '_CHANGE_ME_') === false
            && strpos(self::SMTP_USER, '_CHANGE_ME_') === false
            && self::SMTP_PASS !== '_CHANGE_ME_';
    }

    /** Porta/segurança alternativos para STARTTLS (caso precise trocar). */
    public static function usarStartTls()
    {
        // Helper opcional: retorna ['port' => 587, 'secure' => 'tls'].
        return ['port' => 587, 'secure' => 'tls'];
    }

    /**
     * true = há alguma forma de envio disponível (SMTP OU mail()).
     * Na Hostinger a função mail() funciona para domínios com SPF/PTR
     * configurados (padrão nos planos), então o envio nunca fica bloqueado.
     */
    public static function qualquerEnvioDisponivel()
    {
        return self::smtpConfigurado() || function_exists('mail');
    }

    /** Monta os cabeçalhos MIME multipart para envio via mail(). */
    public static function headersMail($htmlBody, $pdfAnexo = null, $nomeArquivo = null)
    {
        $boundary = 'bnd_' . bin2hex(random_bytes(16));
        $from     = self::FROM_EMAIL;
        $sitename = self::SITE_NAME;

        $h  = "From: \"{$sitename}\" <{$from}>\r\n";
        $h .= "Reply-To: {$from}\r\n";
        $h .= "Return-Path: {$from}\r\n";
        $h .= "Sender: {$from}\r\n";
        $h .= "MIME-Version: 1.0\r\n";
        $h .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        $h .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $corpo  = "--{$boundary}\r\n";
        $corpo .= "Content-Type: text/html; charset=UTF-8\r\n";
        $corpo .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $corpo .= chunk_split(base64_encode($htmlBody)) . "\r\n";

        if ($pdfAnexo !== null && $nomeArquivo !== null) {
            $corpo .= "--{$boundary}\r\n";
            $corpo .= "Content-Type: application/pdf; name=\"{$nomeArquivo}\"\r\n";
            $corpo .= "Content-Transfer-Encoding: base64\r\n";
            $corpo .= "Content-Disposition: attachment; filename=\"{$nomeArquivo}\"\r\n\r\n";
            $corpo .= chunk_split(base64_encode($pdfAnexo)) . "\r\n";
        }
        $corpo .= "--{$boundary}--\r\n";

        return ['headers' => $h, 'body' => $corpo];
    }
}
