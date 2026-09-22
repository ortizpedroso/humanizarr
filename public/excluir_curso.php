<?php
session_start();

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/Database.php';
require_once '../models/Curso.php';

$database = new Database();
$db = $database->getConnection();
$curso = new Curso($db);

// Verifica se ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['msg_curso'] = "ID do curso não informado.";
    $_SESSION['tipo_msg_curso'] = 'erro';
    header("Location: lista_cursos.php");
    exit;
}

$id_curso = (int)$_GET['id'];

// Tenta excluir o curso (CASCADE apagará as inscrições automaticamente)
if ($curso->excluir($id_curso)) {
    $_SESSION['msg_curso'] = "Curso excluído com sucesso!";
    $_SESSION['tipo_msg_curso'] = 'sucesso';
} else {
    $_SESSION['msg_curso'] = "Erro ao excluir curso.";
    $_SESSION['tipo_msg_curso'] = 'erro';
}

header("Location: lista_cursos.php");
exit;
?>
