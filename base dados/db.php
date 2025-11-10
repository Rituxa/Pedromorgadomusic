<?php
$host = "localhost";
$user = "root";
$pass = "Cv7ptcal%";
$dbname = "pedro_morgado";

// Compatibilidade para mysqli
$servername = $host;
$username = $user;
$password = $pass;

// Ligação MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro de ligação à base de dados: " . $conn->connect_error);
}
$conn->set_charset("utf8");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>