<?php
// API endpoint para eventos (usado pelas páginas de loja)
include_once "../base dados/db.php";

// Obtem parâmetros de pesquisa e filtro
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$data_especifica = isset($_GET['data_especifica']) ? trim($_GET['data_especifica']) : '';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'futuros';

// Constroi query base para eventos
$sql = "SELECT * FROM eventos WHERE 1";
$params = [];
$types = '';

// Filtro de pesquisa por texto (título ou local)
if ($q !== '') {
    $sql .= " AND (titulo LIKE ? OR local LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= 'ss';
}

// Filtro por data específica
if ($data_especifica !== '') {
    $sql .= " AND data = ?";
    $params[] = $data_especifica;
    $types .= 's';
}

// Filtro de data (futuros, passados ou todos)
$hoje = date('Y-m-d');
if ($filtro === 'futuros') {
    $sql .= " AND data >= ?";
    $params[] = $hoje;
    $types .= 's';
} elseif ($filtro === 'passados') {
    $sql .= " AND data < ?";
    $params[] = $hoje;
    $types .= 's';
}
$sql .= " ORDER BY data DESC";

// Preparar e executar query
$stmt = $pdo->prepare($sql);
if ($params) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

// Buscar eventos da base de dados
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorna sempre JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($eventos);
exit;
?>
