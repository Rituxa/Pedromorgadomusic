<?php
// Inicia a sessão
session_start();
// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_email'])) {
    // Redireciona para o registo/login se não autenticado
    header('Location: ../registo/register.html');
    exit;
}

$email = $_SESSION['user_email'];
require_once "../config/database.php";

// Obtém dados do utilizador
$stmt = $conn->prepare("SELECT id, nome, email FROM utilizadores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// Atualiza dados do utilizador
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {
    $novo_nome = trim($_POST['nome'] ?? '');
    if ($novo_nome) {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome = ? WHERE email = ?");
        $stmt->bind_param("ss", $novo_nome, $email);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Dados atualizados com sucesso!</div>';
            $user['nome'] = $novo_nome;
        } else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar dados.</div>';
        }
        $stmt->close();
    }
}

// Obtém histórico de compras do utilizador
$stmt = $conn->prepare("SELECT b.id, b.quantidade, b.comprado_em, e.titulo, e.data, e.local FROM bilhetes b JOIN eventos e ON b.evento_id = e.id WHERE b.email = ? ORDER BY b.comprado_em DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$compras = $stmt->get_result();
$stmt->close();

// Fecha a ligação à base de dados
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Utilizador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <header class="bg-dark text-white py-3 mb-4 position-relative">
        <div class="container position-relative">
            <div class="d-flex justify-content-between align-items-center" style="position:relative;">
                <div>
                    <nav class="navbar navbar-dark p-0">
                        <button class="navbar-toggler ms-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </nav>
                </div>
                <h1 class="mb-2 text-center flex-grow-1 text-danger" style="margin-bottom:0!important;">PEDRO MORGADO</h1>
                <div></div>
            </div>
            <div class="collapse navbar-collapse position-absolute" id="mainNavbar" style="top:60px;left:0;z-index:30;background:#222;border-radius:0 0 10px 0;min-width:220px;">
                <ul class="navbar-nav gap-2 p-3">
                    <li class="nav-item"><a class="nav-link" href="../index-pt.html">INÍCIO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../bio-pt.html">BIOGRAFIA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../discography-pt.html">DISCOGRAFIA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../gallery-pt.html">GALERIA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../dates-pt.html">DATAS</a></li>
                    <li class="nav-item"><a class="nav-link" href="../video-pt.html">VÍDEO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../shop-pt.html">LOJA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contacts-pt.html">CONTACTOS</a></li>
                </ul>
            </div>
        </div>
    </header>
    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="bg-white border border-danger border-2 rounded-3 shadow p-4 mb-4" style="box-shadow:0 0 16px 2px #dc3545 !important; color:#111;">
                    <h2 class="text-black mb-4 border-bottom pb-2" style="border-color:#dc3545!important;">Perfil do Utilizador</h2>
                    <?= $msg ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label text-black">Nome</label>
                            <input type="text" name="nome" class="form-control border-danger" value="<?= htmlspecialchars($user['nome']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-black">Email</label>
                            <input type="email" class="form-control border-danger" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>
                        <button type="submit" name="atualizar" class="btn btn-danger">Atualizar Dados</button>
                    </form>
                </div>
                <div class="bg-white border border-danger border-2 rounded-3 shadow p-4" style="box-shadow:0 0 16px 2px #dc3545 !important; color:#111;">
                    <h3 class="text-black mb-4 border-bottom pb-2" style="border-color:#dc3545!important;">Histórico de Compras</h3>
                    <div class="table-responsive">
                        <table class="table align-middle bg-white border border-danger border-2" style="box-shadow:0 0 8px 1px #dc3545 !important; color:#111;">
                            <thead class="bg-white text-black" style="border-bottom:2px solid #dc3545;">
                                <tr>
                                    <th style="border-right:2px solid #111;">Evento</th>
                                    <th style="border-right:2px solid #111;">Data do Evento</th>
                                    <th style="border-right:2px solid #111;">Local</th>
                                    <th style="border-right:2px solid #111;">Quantidade</th>
                                    <th>Data da Compra</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($row = $compras->fetch_assoc()): ?>
                                <tr style="color:#111;">
                                    <td style="border-right:2px solid #111;"><?= htmlspecialchars($row['titulo']) ?></td>
                                    <td style="border-right:2px solid #111;"><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                                    <td style="border-right:2px solid #111;"><?= htmlspecialchars($row['local']) ?></td>
                                    <td style="border-right:2px solid #111;"><?= $row['quantidade'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['comprado_em'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="bg-dark text-light text-center py-3 mt-auto">
        <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
        <div>
            <a href="https://www.facebook.com/pedro.morgado.9"><i class="fab fa-facebook-f text-danger border border-dark rounded-circle"></i></a>
            <a href="https://www.instagram.com/_pedro_morgado_/"><i class="fab fa-instagram text-danger border border-dark rounded-circle"></i></a>
            <a href="https://www.youtube.com/playlist?list=OLAK5uy_llGenPOe9vHI7BsTAKVT3AvjtIyCQd_IE"><i class="fab fa-youtube text-danger border border-dark rounded-circle"></i></a>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
