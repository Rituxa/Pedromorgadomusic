<?php
// Iniciar sessão
session_start();

// Inicializar variáveis
$nome = '';
$email = '';
$temSessao = false;

// Verificar se existe sessão de utilizador
if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];
    $temSessao = true;
    // Tentar obter o nome da sessão
    $nome = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : '';
    if (!$nome) {
        // Se o nome não estiver em sessão, buscar à base de dados
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'pedro_morgado';
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn && !$conn->connect_errno) {
            $stmt = $conn->prepare('SELECT nome FROM utilizadores WHERE email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($nome);
            $stmt->fetch();
            $stmt->close();
            $conn->close();
            // Guardar o nome na sessão para uso futuro
            $_SESSION['user_nome'] = $nome;
        }
    }
}

// Devolver resposta em JSON com o estado da sessão
header('Content-Type: application/json');
echo json_encode([
    'temSessao' => $temSessao,
    'nome' => $nome,
    'email' => $email
]);
