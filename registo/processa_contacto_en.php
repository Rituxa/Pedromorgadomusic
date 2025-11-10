<?php
// processa_contacto_en.php
// Processa o formulário de contacto (EN), guarda na BD e envia email ao administrador
require_once 'base dados/db.php';
// Garantir que a ligação $conn está definida
if (!isset($conn) || !$conn) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'pedro_morgado';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_errno) {
        die('Erro de ligação à base de dados.');
    }
}

function getAdminEmail($conn) {
    $sql = "SELECT valor FROM config WHERE chave='admin_email' LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return $row['valor'];
    }
    return 'pmorgado77@gmail.com';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $apelido = trim($_POST['apelido'] ?? '');
    $dataNascimento = trim($_POST['dataNascimento'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $data_envio = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO mensagens (nome, apelido, data_nascimento, email, telefone, mensagem, data_envio, respondido) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param('sssssss', $nome, $apelido, $dataNascimento, $email, $telefone, $mensagem, $data_envio);
    $stmt->execute();
    $stmt->close();

    $adminEmail = getAdminEmail($conn);
    $subject = "New contact message from the website";
    $body = "You have received a new contact message from the website.\n\n" .
        "Name: $nome $apelido\n" .
        "Date of Birth: $dataNascimento\n" .
        "Email: $email\n" .
        "Phone: $telefone\n" .
        "Message: $mensagem\n";
    @mail($adminEmail, $subject, $body, "From: $email");

    header('Location: contacts.html?success=1');
    exit;
}
header('Location: contacts.html');
exit;
