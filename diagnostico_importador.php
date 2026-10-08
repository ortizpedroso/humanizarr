<?php
/**
 * Diagnóstico do importador — use apenas temporariamente e apague depois.
 *
 * Como usar:
 *   1) Suba este arquivo na MESMA pasta onde está importar_inscricoes_olho_nos_olhinhos.php
 *      (normalmente public_html/).
 *   2) Acesse: https://humanizamais.org.br/diagnostico_importador.php?key=RHfT3xK9mPqL2wZ8
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$CHAVE = 'RHfT3xK9mPqL2wZ8';
if (($_GET['key'] ?? '') !== $CHAVE) {
    http_response_code(403);
    die('Chave incorreta.');
}

echo "<h2>Diagnóstico do importador</h2>";

// 1) O importador existe nesta pasta?
$alvo = __DIR__ . '/importar_inscricoes_olho_nos_olhinhos.php';
echo "<p><b>1. Arquivo do importador:</b> ";
if (!is_file($alvo)) {
    echo "<span style='color:red'>NÃO encontrado em " . htmlspecialchars(__DIR__) . "</span></p>";
    echo "<p>→ Você subiu o arquivo para outra pasta. Confira no Gerenciador de Arquivos onde ele está.</p>";
} else {
    echo "<span style='color:green'>encontrado ✔</span> (" . filesize($alvo) . " bytes)</p>";

    // 2) A chave DENTRO do arquivo é a mesma?
    $conteudo = file_get_contents($alvo);
    if (preg_match("/\\\$CHAVE_ACESSO\s*=\s*'([^']*)'/", $conteudo, $m)) {
        echo "<p><b>2. Chave definida dentro do arquivo no servidor:</b> <code>" . htmlspecialchars($m[1]) . "</code></p>";
        if ($m[1] === $CHAVE) {
            echo "<p style='color:green'>✔ Chave confere. Use exatamente: ?key=" . htmlspecialchars($CHAVE) . "</p>";
        } else {
            echo "<p style='color:red'>✘ DIFERE da esperada! Use a chave mostrada acima na URL.</p>";
        }
    } else {
        echo "<p style='color:red'>✘ Este arquivo NO SERVIDOR não contém a variável \$CHAVE_ACESSO — "
           . "significa que o importador hospedado é uma VERSÃO ANTIGA/DIFERENTE. "
           . "Reenvie a versão atual do GitHub (git pull / deploy por Git).</p>";
    }
}

// 3) Como o navegador enviou a URL?
echo "<p><b>3. Parâmetros recebidos pelo servidor:</b> <code>" . htmlspecialchars(print_r($_GET, true)) . "</code></p>";
echo "<p><b>URL pedida:</b> <code>" . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '?') . "</code></p>";

echo "<hr><p><b>Link pronto para testar agora:</b><br><a href='/importar_inscricoes_olho_nos_olhinhos.php?key=$CHAVE'>"
   . "/importar_inscricoes_olho_nos_olhinhos.php?key=$CHAVE</a></p>";
echo "<p style='color:red'><b>⚠ Apague este arquivo (diagnostico_importador.php) após o uso!</b></p>";
