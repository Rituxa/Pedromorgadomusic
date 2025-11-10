<?php
// Inicia a sessão
session_start();
// Liga à base de dados
require_once "../config/database.php";

// Apenas o admin pode aceder
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin@admin.com') {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$msg = '';
if ($id <= 0) {
    header('Location: admin.php');
    exit;
}

// Atualiza os dados do utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    if ($nome && $email) {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome=?, email=?, is_admin=? WHERE idutilizadores=?");
        $stmt->bind_param("ssii", $nome, $email, $is_admin, $id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Utilizador atualizado com sucesso.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar utilizador.</div>';
        }
        $stmt->close();
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos.</div>';
    }
}

// Busca os dados do utilizador
$stmt = $conn->prepare("SELECT nome, email, is_admin FROM utilizadores WHERE idutilizadores=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome, $email, $is_admin);
$stmt->fetch();
$stmt->close();

// Fecha a ligação à base de dados
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Utilizador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
<header class="bg-dark text-white py-3 mb-4">
    <div class="container">
        <h1 class="text-danger text-center">Editar Utilizador</h1>
    </div>
</header>
<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bg-white border border-danger border-2 rounded-3 shadow p-4">
                <?= $msg ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control border-danger" value="<?= htmlspecialchars($nome) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control border-danger" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" <?= $is_admin ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_admin">Administrador</label>
                    </div>
                    <button type="submit" class="btn btn-danger">Guardar Alterações</button>
                    <a href="admin.php" class="btn btn-secondary ms-2">Voltar</a>
                </form>
            </div>
        </div>
    </div>
</main>
<footer class="bg-dark text-light text-center py-3 mt-auto">
    <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
