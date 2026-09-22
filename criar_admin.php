<?php
/**
 * Script temporário para criar usuário Admin
 * Acesse: http://localhost/criar_admin.php
 */

// Ajuste o caminho conforme necessário para encontrar o Database.php
require_once __DIR__ . '/src/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Credenciais que serão criadas
$nome  = "Administrador";
$email = "admin@humanizarr.org";
$senha = "admin123"; // Senha que você usará para entrar

// Gera o hash seguro da senha (obrigatório pelo sistema)
$senha_hash = password_hash($senha, PASSWORD_BCRYPT);

try {
    $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (:nome, :email, :senha, 'admin')";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senha_hash
    ]);
    echo "<h1>Sucesso!</h1><p>Usuário criado.</p><p>Login: <b>$email</b><br>Senha: <b>$senha</b></p>";
} catch (PDOException $e) {
    echo "<h1>Erro</h1><p>Erro ao criar (talvez o email já exista): " . $e->getMessage() . "</p>";
}
