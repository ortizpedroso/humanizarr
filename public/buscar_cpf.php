<?php
session_start();
header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../models/Inscrito.php';

$database = new Database();
$db = $database->getConnection();
$inscrito = new Inscrito($db);

if (!isset($_GET['cpf']) || empty($_GET['cpf'])) {
    echo json_encode(['encontrado' => false, 'erro' => 'CPF não informado']);
    exit;
}

$cpf = $_GET['cpf'];
$dados = $inscrito->buscarPorCpf($cpf);

if ($dados) {
    echo json_encode([
        'encontrado' => true,
        'nome' => $dados['nome'],
        'email' => $dados['email'],
        'telefone' => $dados['telefone'],
        'tipo' => $dados['tipo'],
        'instituicao' => $dados['instituicao'],
        'semestre_atuacao' => $dados['semestre_atuacao']
    ]);
} else {
    echo json_encode(['encontrado' => false]);
}
?>
