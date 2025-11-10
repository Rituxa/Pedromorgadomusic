<?php

require_once "../config/database.php";

$lang = $_POST['lang'] ?? 'pt';
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';
$erros = [];

// Validação dos campos obrigatórios
if (!$nome || !$email || !$password || !$confirmar) {
    $erros[] = $lang=='pt' ? 'Preencha todos os campos.' : 'Please fill all fields.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = $lang=='pt' ? 'Email inválido.' : 'Invalid email.';
}
if ($password !== $confirmar) {
    $erros[] = $lang=='pt' ? 'As palavras-passe não coincidem.' : 'Passwords do not match.';
}
if (strlen($password) < 6) {
    $erros[] = $lang=='pt' ? 'A palavra-passe deve ter pelo menos 6 caracteres.' : 'Password must be at least 6 characters.';
}

// Verifica se o email já existe
$stmt = $conn->prepare("SELECT `email` FROM utilizadores WHERE `email`=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $erros[] = $lang=='pt' ? 'Email já registado.' : 'Email already registered.';
}
$stmt->close();
if ($erros) {
    echo '<div class="alert alert-danger">'.implode('<br>', $erros).'</div>';
    echo '<a href="register.html" class="btn btn-outline-danger mt-3">'.($lang=='pt'?'Voltar':'Back').'</a>';
    exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO utilizadores (nome, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nome, $email, $hash);
if ($stmt->execute()) {
    // Redirecionar para login após registo com sucesso
    header('Location: login.php');
    exit;
} else {
    echo '<div class="alert alert-danger">'.($lang=='pt'?'Erro ao registar.':'Registration error.').'</div>';
    echo '<a href="register.html" class="btn btn-outline-danger mt-3">'.($lang=='pt'?'Voltar':'Back').'</a>';
}
$stmt->close();
$conn->close();
