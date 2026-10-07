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
    const SMTP_HOST   = 'smtp._CHANGE_ME_.com.br';
    const SMTP_USER   = 'eventos@_CHANGE_ME_.com.br';
    const SMTP_PASS   = '_CHANGE_ME_';
    const SMTP_PORT   = 465;          // 465 (SSL) ou 587 (TLS)
    const SMTP_SECURE = 'ssl';        // 'ssl' p/ 465, 'tls' p/ 587
    const SITE_NAME   = 'Instituto Humaniza RR';

    public static function smtpConfigurado()
    {
        return strpos(self::SMTP_HOST, '_CHANGE_ME_') === false
            && strpos(self::SMTP_USER, '_CHANGE_ME_') === false
            && self::SMTP_PASS !== '_CHANGE_ME_';
    }
}
