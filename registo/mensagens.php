<?php
// mensagens.php - Gestão de mensagens de contacto para o administrador
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../base dados/db.php';

// Responder a uma mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_id'])) {
    $id = intval($_POST['responder_id']);
    $resposta = trim($_POST['resposta']);
    $email_utilizador = trim($_POST['email_utilizador']);
    // Atualizar BD
    $stmt = $conn->prepare('UPDATE mensagens SET resposta=?, respondido=1, data_resposta=NOW() WHERE id=?');
    $stmt->bind_param('si', $resposta, $id);
    $stmt->execute();
    $stmt->close();
    // Enviar email ao utilizador
    $assunto = 'Resposta à sua mensagem de contacto';
    $corpo = "Obrigado pelo seu contacto. Resposta do administrador:\n\n$resposta";
    @mail($email_utilizador, $assunto, $corpo, "From: admin@pedromorgado.pt");
    $msg = 'Resposta enviada com sucesso!';
}
// Buscar mensagens
$res = $conn->query('SELECT * FROM mensagens ORDER BY data_envio DESC');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Mensagens de Contacto</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2 class="mb-4 text-danger">Mensagens de Contacto</h2>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Mensagem</th>
                <th>Data</th>
                <th>Respondido</th>
                <th>Resposta</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nome'] . ' ' . $row['apelido']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['telefone']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['mensagem'])) ?></td>
                <td><?= htmlspecialchars($row['data_envio']) ?></td>
                <td><?= $row['respondido'] ? 'Sim' : 'Não' ?></td>
                <td><?= nl2br(htmlspecialchars($row['resposta'] ?? '')) ?></td>
                <td>
                    <?php if (!$row['respondido']): ?>
                    <form method="post" class="d-flex flex-column gap-2">
                        <input type="hidden" name="responder_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="email_utilizador" value="<?= htmlspecialchars($row['email']) ?>">
                        <textarea name="resposta" class="form-control" required placeholder="Escreva a resposta..."></textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Responder</button>
                    </form>
                    <?php else: ?>
                        <span class="text-success">Respondido</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
