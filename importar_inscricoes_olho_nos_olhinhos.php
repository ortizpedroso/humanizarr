<?php
/**
 * Importação das inscrições da CAMPANHA "OLHO NOS OLHINHOS" (Acadêmicos de Medicina e Profissionais de Saúde)
 * ----------------------------------------------------------------------------
 * Script 100% orientado a objeto: reutiliza as classes Database, Curso e Inscricao
 * já existentes no projeto. Idempotente: pode ser executado mais de uma vez sem
 * duplicar pessoas nem inscrições (a classe Inscricao deduplica por e-mail).
 *
 * USO:
 *   1) Suba este arquivo na raiz do site (mesma pasta de index.php / config/).
 *   2) Acesse pelo navegador:  https://humanizamais.org.br/importar_inscricoes_olho_nos_olhinhos.php?key=RHfT3xK9mPqL2wZ8
 *   3) Ao final aparece o botão "Remover este arquivo do servidor agora" — clique nele.
 *      (ou apague manualmente via Gerenciador de Arquivos da Hostinger)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Chave simples para evitar execução acidental por terceiros.
$CHAVE_ACESSO = 'RHfT3xK9mPqL2wZ8';
if (($_GET['key'] ?? '') !== $CHAVE_ACESSO) {
    http_response_code(403);
    die('<h3>Acesso negado.</h3><p>Use: <code>?key=CHAVE</code> definida dentro deste arquivo.</p>');
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Curso.php';
require_once __DIR__ . '/models/Inscricao.php';

/* ===================== DADOS DA PLANILHA ===================== */

$CURSO_NOME = 'Campanha de Olho nos Olhinhos';
$CURSO_DESCRICAO = 'Campanha de Olho nos Olhinhos – inscrições de Acadêmicos de Medicina e Profissionais de Saúde (importação da lista oficial).';

// Acadêmicos: [nome, telefone, email, instituicao, semestre]
$ACADEMICOS = [
    ['Anne Jiulaina de Oliveira Soares', '95 99153-2454', 'Annejiulaina2002@gmail.com', 'Universidade Federal de Roraima', '6° semestre'],
    ['Gabriel Veras Coêlho Guimarães', '95981292329', 'gveras1961@gmail.com', 'UFRR', '6º Período'],
    ['Walterlan Marques do Nascimento', '959981297392', 'waltermarqs@gmail.com', 'UFRR', '2 semestre'],
    ['Valéria Dias Santos Alves', '95991442220', 'valeria.ds.alves@gmail.com', 'UFRR', '2'],
    ['Marcus Vinicius Freitas Caldas Silva', '18996448371', 'mv5757409@gmail.com', 'FACES - Cathedral', '4° semestre'],
    ['Maria Eduarda Vieira Martins Inocêncio', '83998412000', 'meduinocencio@gmail.com', 'Faculdade Cathedral', '4º Período'],
    ['Emily Merco do Nascimento e Silva', '95 981078451', 'emily.merco26@gmail.com', 'Faculdade Cathedral', '4° semestre'],
    ['Gleison Sousa da Silva', '11984206755', 'g7eison@gmail.com', 'CATHEDRAL RR', '1'],
    ['Sarah Jordão Hemerly', '28999182561', 'jordaosarah770@gmail.com', 'Cathedral', '4º período'],
    ['Mariana Carvalho Lima', '62999206059', 'marianacarvalholima@hotmail.com', 'Cathedral', '2'],
    ['Carlos Rafael Boritza Gama', '95991649949', 'carlos.gama@alunos.uerr.edu.br', 'UERR', '8º'],
    ['Maria Eduarda Vieira Martins Inocêncio', '83998412000', 'meduinocencio@gmail.com', 'Faculdade Cathedral', '4º período'], // duplicado nº12
    ['Ana Karolina de Oliveira Gonçalves', '92984536953', 'karolgonufrr@gmail.com', 'Universidade Federal de Roraima', '8* período'],
    ['Julia Santos', '95981100197', 'juliavitoriasaantos@gmail.com', 'UFRR', '6° semestre'],
    ['Erick Maysonnave Baraúna Magalhães', '(95) 99162-0521', 'erickmbm@gmail.com', 'UFRR', '2º semestre'],
    ['Wine carioca de Albuquerque', '98984097675', 'wincarioca@outlook.com', 'UFRR', '6'],
    ['Anne Jiulaina de Oliveira Soares', '95 99153-2454', 'Annejiulaina2002@gmail.com', 'Universidade Federal de Roraima', '6° Semestre'], // duplicado nº17
    ['Gabriel Veras Coêlho Guimarães', '95981292329', 'gveras1961@gmail.com', 'UFRR', '6 semestre'], // duplicado nº18
    ['Lila santos mello', '95981243323', 'lilasantos29@gmail.com', 'UFRR', '8'],
    ['Vitória Esterfannya Cavalcante Gurgel', '95999616660', 'esterfannya2017@gmail.com', 'FST', '4º período'],
    ['Jaques Sonntag', '95991172800', 'jaquescolorado@hotmail.com', 'Cathedral', '4º semestre'],
    ['Laura Carelli Hermes', '67984498281', 'laurahermes2207@gmail.com', 'UFRR', '8º semestre'],
    ['Thaís Adrielly Souza da Silva Xavier', '95 984073950', 'thais.adriellyxavier@gmail.com', 'Faculdade Cathedral', 'Módulo 2.2 (Sistema renal), 2° Semestre'],
    ['Isabelle Moraes de Araújo', '95991753000', 'isabellemoraes252@gmail.com', 'Cathedral', '4º semestre'],
    ['João Victor Pereira da Costa', '95991440208', 'joaovictorcostapereira19@gmail.com', 'UFRR', '4 semestre'],
    ['Maria Jade Aparecida Figueredo Sanches', '95 981214252', 'mjprincesas2@gmail.com', 'Faculdade Santa Teresa', '2'],
    ['Bianca Cavalcante Aguiar', '95981216121', 'bianca.medicina26@gmail.com', 'Universidade Federal de Roraima', '2º semestre'],
    ['daniela buregio dias', '95981268643', 'daniburegio@gmail.com', 'Faculdade santa teresa', '2 semestre'],
    ['Milena Cristina Ketzer', '42 984332218', 'milenaketzer9@gmail.com', 'Faculdade Santa Teresa', '2º semestre'],
    ['Mariana Nicole Cavalcante de melo', '95991723032', 'mariananicole.cdm@gmail.com', 'Santa Tereza', '2 período'],
    ['Ezequiel de Jesus Oliveira Francisco', '95991569906', 'ezequieldejesusoli@gmail.com', 'UFRR', '2° semestre do 1° ano de medicina.'],
    ['maria eduarda soares santana', '95991614523', 'mary_adraude@hotmail.com', 'faculdade santa teresa', '2'],
    ['Rodrigo Emanuel Sá Freire de Lima Santos', '95991573943', 'rodrigoemanuelsa@gmail.com', 'Ufrr', 'Segundo semestre/Primeiro ano'],
    ['Jose eugenio romano thome', '95981028623', 'jose.thome@alunos.uerr.edu.br', 'UERR', '6 periodo'],
    ['Lucas Tietz Valério', '66 99632-4427', 'lucastietz7@gmail.com', 'UFRR', '2⁰'],
    ['João Vitor da Silva Almeida', '95991689849', 'alcmepmrralmeida@gmail.com', 'UFRR', '2 período'],
    ['Paulo Henrique Araújo da Silveira', '95 981034251', 'Pauloaraujocraft@gmail.com', 'Ufrr', '2 semestre'],
    ['Jackeline Carter de Souza e Souza', '95981255376', 'carterjackeline3@hotmail.com', 'Cathedral', '4 semestre'],
    ['Maria Eduarda Maia Sales', '(95)99167-3307', 'maiasalesm@gmail.com', 'Faculdade Santa Teresa', '2 semestre'],
    ['Vitoria Apinagés Duo', '95991738659', 'vitoriaapinages1@gmail.com', 'Faculdade Santa Teresa', 'Segundo semestre'],
    ['Juliana Sousa lima', '95991257964', 'julimsousa112006@gmail.com', 'Santa Teresa', '2º semestre'],
    ['Vanessa Xaud wanderley', '95981170527', 'vanessa_xaud@hotmail.com', 'Faculdade Santa Teresa', '4 período'],
    ['Weslley Lopes Soares Costa', '95991562880', 'weslleylopesceo@gmail.com', 'UFRR', 'Quarto'],
    ['Stefany Amorim Melo Alencar', '95984056000', 'stefanyalencar2007@gmail.com', 'FST', '4º período'],
    ['keven gabriel s rodrigues', '95991657366', 'az09keven@gmail.com', 'cathedral', '5'],
    ['Rafaela Monteiro Costa', '95981122320', 'Rafaelabaruk21@gmail.com', 'Faculdade Cathedral', '2º semestre'],
    ['Tiago Wanderley Gama', '95991478611', 'tiagowgama@gmail.com', 'UERR', '8º'],
    ['Lucas Cauã Martins Barreto', '95991536517', 'lucascauamartinsbarreto091@gmail.com', 'UFRR', '2º semestre'],
    ['João Vitor Araújo Casarin', '95991521971', 'megjoaovitor@gmail.com', 'Universidade Federal de Roraima', '2'],
    ['Iarley de Almeida Coelho', '95991747605', 'iarledealmeida@gmail.com', 'UFRR', '2°'],
    ['Mariana Bueno de Melo', '95981079285', 'marianabuenodemelo6@gmail.com', 'Ufrr', '2 semestre'],
    ['Larissa Gabriele Paixão Fontes', '(95)99164-4564', 'larissagabrieledjj@gmail.com', 'UFRR', '6º semestre'],
    ['Leonardo Bessa de Azevedo', '95991185798', 'leo13deazevedo@gmail.com', 'UFRR', '4° Semestre e 2° Período'],
    ['Bianca Hevilly de Carvalho Melo', '95999629087', 'biancahevilly@gmail.com', 'Ufrr', '8 semestre'],
    ['Maria Eduarda Vieira Martins Inocêncio', '83998412000', 'meduinocencio@gmail.com', 'Faculdade Cathedral', '4º Período'], // duplicado nº55
    ['Isabelle Moraes de Araújo', '95991753000', 'isabellemoraes252@gmail.com', 'Cathedral', '4º semestre'], // duplicado nº56
];

// Profissionais: [nome, telefone, email, instituicao, atuacao]
$PROFISSIONAIS = [
    ['Laryssa Helena de Oliveira Bessa', '095 9 8121 2336', 'laryssabessa@hotmail.com', 'Pérola Materna - serviços pediátricos', 'Pediatra'],
    ['Eloisa Klein Lopes', '95991702345', 'eloisakl@gmail.com', 'UFRR', 'Docente'],
    ['Roger Malacarne Caleffi', '95 981145566', 'rogercaleffi@gmail.com', 'Consultório Particular', ''],
    ['Joyce Maciel Rolim', '95981170790', 'joyce_rolim@hotmail.com', 'HMI NSN', 'Médica Pediatra Neonatologista'],
    ['MARCELO MOREIRA DE OLIVEIRA', '95991757077', 'drmarcelomoreira@gmail.com', 'UNIG', 'concluido em 2008'],
    ['Bruna Messias Jacques de Moraes', '95981102115', 'bruunajacques@hotmail.com', 'Hospital de Amor Infantojuvenil de Barretos/SP', 'R4 de Oncologia Pediátrica'],
    ['Henrique Braga Jacques de Moraes', '95981151602', 'moraespkf@hotmail.com', 'Hospital Ville Roy', 'Médico Cardiologista'],
    ['ortiz marcos martins pedroso', '95991161058', 'ortizpedroso@hotmail.com', 'MPERR', 'Promotoria de Saúde'],
];

/* ===================== EXECUÇÃO ===================== */

echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><title>Importação – Olho nos Olhinhos</title>';
echo '<style>body{font-family:Arial,sans-serif;max-width:900px;margin:30px auto;padding:0 15px}table{border-collapse:collapse;width:100%;font-size:13px}td,th{border:1px solid #ccc;padding:5px 8px}tr.dup{background:#fff3cd}tr.err{background:#f8d7da}.ok{color:green}</style></head><body>';
echo '<h2>📥 Importação – Inscrições "Campanha de Olho nos Olhinhos"</h2>';

try {
    $database = new Database();
    $db = $database->getConnection();

    /* --- Passo 1: garantir que o evento/curso existe (idempotente) --- */
    $cursoModel = new Curso($db);
    $stmt = $db->prepare("SELECT id FROM cursos WHERE nome = :nome LIMIT 1");
    $stmt->execute([':nome' => $CURSO_NOME]);
    $cursoExiste = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cursoExiste) {
        $id_curso = (int)$cursoExiste['id'];
        echo "<p>✔ Evento já existia no banco: <strong>{$CURSO_NOME}</strong> (ID {$id_curso}).</p>";
    } else {
        $criado = $cursoModel->criar([
            'nome'              => $CURSO_NOME,
            'descricao'         => $CURSO_DESCRICAO,
            'duracao'           => '4 horas',
            'local'             => 'Boa Vista - RR',
            'palestrante'       => '',
            'presidente'        => '',
            'vice_presidente'   => '',
            'data_evento'       => null,
            'data_fim_evento'   => null,
            'horario_inicio'    => null,
            'inscricao_inicio'  => null,
            'inscricao_fim'     => null,
            'vagas_limite'      => 0,
            'status'            => 'Activo',
            'banner_imagem'     => null,
            'certificado_arquivo' => null,
            'emitir_certificado'  => 0,
        ]);
        if (!$criado) {
            throw new Exception('Falha ao criar o curso "' . $CURSO_NOME . '". Verifique as colunas da tabela cursos (migração).');
        }
        $id_curso = (int)$db->lastInsertId();
        echo "<p class='ok'>✔ Evento criado: <strong>{$CURSO_NOME}</strong> (ID {$id_curso}). Edite no painel para colocar data, local e habilitar certificados se desejar.</p>";
    }

    /* --- Passo 2: importar pessoas + inscrições (dedupe por e-mail) --- */
    $inscricaoModel = new Inscricao($db);
    $totalOk = 0; $totalDuplicado = 0; $totalErro = 0;

    echo '<h3>Registros importados</h3><table><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Situação</th></tr>';

    $registrar = function (array $lista, string $tipo) use ($db, $inscricaoModel, $id_curso, &$totalOk, &$totalDuplicado, &$totalErro) {
        foreach ($lista as $i => $p) {
            [$nome, $telefone, $email, $instituicao, $semestre] = $p;
            $dados = [
                'nome'             => $nome,
                'email'            => $email,
                'telefone'         => $telefone,
                'tipo'             => $tipo,
                'instituicao'      => $instituicao,
                'semestre_atuacao' => $semestre,
            ];

            // Verifica se a pessoa JÁ está inscrita neste curso (dedupe da planilha/reexecução)
            $q = $db->prepare("SELECT i.id FROM inscritos i
                               JOIN inscricoes ins ON ins.id_inscrito = i.id
                               WHERE LOWER(i.email) = LOWER(:email) AND ins.id_curso = :id_curso");
            $q->execute([':email' => $email, ':id_curso' => $id_curso]);
            if ($q->fetch(PDO::FETCH_ASSOC)) {
                $totalDuplicado++;
                echo "<tr class='dup'><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($nome) . "</td><td>" . htmlspecialchars($email) . "</td><td>$tipo</td><td>Já inscrito (duplicado na planilha) — ignorado</td></tr>";
                continue;
            }

            $resultado = $inscricaoModel->processarInscricao($dados, $id_curso);
            if ($resultado === true) {
                $totalOk++;
                echo "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($nome) . "</td><td>" . htmlspecialchars($email) . "</td><td>$tipo</td><td class='ok'>✔ Importado</td></tr>";
            } else {
                $totalErro++;
                echo "<tr class='err'><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($nome) . "</td><td>" . htmlspecialchars($email) . "</td><td>$tipo</td><td>✖ Já inscrito ou e-mail inválido</td></tr>";
            }
        }
    };

    $registrar($ACADEMICOS, 'Acadêmico');
    $registrar($PROFISSIONAIS, 'Profissional de Saúde');

    echo '</table>';
    echo "<h3>Resumo</h3><ul>
            <li>✅ Importados com sucesso: <strong>$totalOk</strong></li>
            <li>⚠️ Duplicados ignorados (mesmo e-mail): <strong>$totalDuplicado</strong></li>
            <li>❌ Erros: <strong>$totalErro</strong></li>
          </ul>";

    /* --- Passo 3: status das inscrições p/ certificado --- */
    // O modelo novo não grava status; garantimos 'Confirmada' para permitir emissão futura.
    try {
        $db->prepare("UPDATE inscricoes SET status = 'Confirmada' WHERE id_curso = :c AND (status IS NULL OR status = '')")
           ->execute([':c' => $id_curso]);
        echo "<p class='ok'>✔ Inscrições marcadas como <strong>Confirmada</strong> (necessário para gerar certificado).</p>";
    } catch (PDOException $e) {
        echo "<p style='color:#b00'>⚠ Não foi possível atualizar o status (coluna 'status'?): " . $e->getMessage() . "</p>";
    }

    echo '<hr><h3>🔒 Finalização obrigatória</h3>
          <p>Esta é uma página de manutenção. <strong>Apague este arquivo agora</strong> para ninguém mais executá-lo:</p>
          <form method="post">
            <button type="submit" name="apagar" style="background:#E30613;color:#fff;border:none;padding:12px 20px;border-radius:8px;cursor:pointer;font-size:15px">
              🗑 Remover este arquivo do servidor agora
            </button>
          </form>';

    if (isset($_POST['apagar'])) {
        @unlink(__FILE__);
        if (!file_exists(__FILE__)) {
            echo '<h3 class="ok">✔ Arquivo removido com sucesso. Importação concluída!</h3>';
        } else {
            echo '<h3 style="color:#b00">Não foi possível apagar automaticamente (permissão). Exclua pelo Gerenciador de Arquivos da Hostinger.</h3>';
        }
    }

} catch (Throwable $e) {
    echo '<h3 style="color:#b00">Erro fatal: ' . htmlspecialchars($e->getMessage()) . '</h3>';
}

echo '</body></html>';
