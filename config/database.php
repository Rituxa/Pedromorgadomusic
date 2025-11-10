<?php


$host = "localhost";
$user = "root";
$pass = "Cv7ptcal%";
$db = "pedro_morgado";

// Função para criar conexão mysqli
function getDbConnection() {
    global $host, $user, $pass, $db;
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Erro de ligação: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

// Compatibilidade com código existente
$conn = getDbConnection();

// Para PDO (
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
