<?php
// Inclui ligação à base de dados
require_once "../config/database.php";

// Obter parâmetros de pesquisa e filtro
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

// Se pedido via AJAX (Accept: application/json ou ?ajax=1), devolve JSON
if (
    (isset($_GET['ajax']) && $_GET['ajax'] == '1') ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($eventos);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
<header class="bg-dark text-white py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="text-danger mb-0">Eventos</h1>
        <a href="../index.html" class="btn btn-outline-light">Voltar ao Início</a>
    </div>
</header>
<main class="container flex-fill mb-5">
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="q" class="form-control" placeholder="Pesquisar eventos..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="data_especifica" class="form-control" placeholder="Data específica" value="<?= htmlspecialchars($data_especifica) ?>">
        </div>
        <div class="col-md-3">
            <select name="filtro" class="form-select">
                <option value="futuros" <?= $filtro==='futuros'?'selected':'' ?>>Futuros</option>
                <option value="passados" <?= $filtro==='passados'?'selected':'' ?>>Passados</option>
                <option value="todos" <?= $filtro==='todos'?'selected':'' ?>>Todos</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-danger w-100">Pesquisar</button>
            <a href="eventos.php" class="btn btn-outline-secondary w-100 mt-1">Limpar</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-danger">
                <tr>
                    <th>Título</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Local</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($eventos) === 0): ?>
                <tr><td colspan="6" class="text-center">Nenhum evento encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($eventos as $evento): ?>
                <tr>
                    <td><?= htmlspecialchars($evento['titulo']) ?></td>
                    <td><?= date('d/m/Y', strtotime($evento['data'])) ?></td>
                    <td><?= htmlspecialchars($evento['hora']) ?></td>
                    <td><?= htmlspecialchars($evento['local']) ?></td>
                    <td>
                        <a href="detalhes_evento.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-primary mb-1">Ver detalhes</a>
                        <a href="comprar_bilhete.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-success mb-1">Comprar Bilhete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<footer class="bg-dark text-light text-center py-3 mt-auto">
    <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
