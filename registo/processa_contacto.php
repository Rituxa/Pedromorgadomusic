<?php
// processa_contacto.php
// Processa o formulário de contacto, guarda na BD e envia email ao administrador

// Incluir ligação à base de dados
require_once 'base dados/db.php';

// Obter email do administrador da BD (tabela config ou semelhante)
function getAdminEmail($conn) {
    $sql = "SELECT valor FROM config WHERE chave='admin_email' LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return $row['valor'];
    }
    return 'pmorgado77@gmail.com'; // fallback
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $apelido = trim($_POST['apelido'] ?? '');
    $dataNascimento = trim($_POST['dataNascimento'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $data_envio = date('Y-m-d H:i:s');

    // Guardar mensagem na BD
    $stmt = $conn->prepare("INSERT INTO mensagens (nome, apelido, data_nascimento, email, telefone, mensagem, data_envio, respondido) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param('sssssss', $nome, $apelido, $dataNascimento, $email, $telefone, $mensagem, $data_envio);
    $stmt->execute();
    $stmt->close();

    // Enviar email ao administrador
    $adminEmail = getAdminEmail($conn);
    $assunto = "Nova mensagem de contacto do site";
    $corpo = "Recebeu uma nova mensagem de contacto do site.\n\n" .
        "Nome: $nome $apelido\n" .
        "Data de Nascimento: $dataNascimento\n" .
        "Email: $email\n" .
        "Telefone: $telefone\n" .
        "Mensagem: $mensagem\n";
    @mail($adminEmail, $assunto, $corpo, "From: $email");

    // Redirecionar para página de sucesso ou mostrar mensagem
    header('Location: contacts-pt.html?sucesso=1');
    exit;
}
// Se não for POST, redirecionar para contactos
header('Location: contacts-pt.html');
exit;
