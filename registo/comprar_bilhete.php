<?php
// Liga à base de dados
require_once "../config/database.php";

// Obtém o evento pelo ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $evento = $res->fetch_assoc();
    $stmt->close();
}

// Processa a compra do bilhete
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evento) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 1);
    if ($nome && $email && $quantidade > 0) {
        $stmt = $conn->prepare("INSERT INTO bilhetes (evento_id, nome, email, quantidade) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $id, $nome, $email, $quantidade);
        if ($stmt->execute()) {
            $mensagem = '<div class="alert alert-success">Compra efetuada com sucesso!</div>';
        } else {
            $mensagem = '<div class="alert alert-danger">Erro ao processar a compra.</div>';
        }
        $stmt->close();
    } else {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos corretamente.</div>';
    }
}

// Fecha a ligação à base de dados
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Bilhete</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <header class="bg-dark text-white py-3 mb-4 position-relative">
        <!-- ...header igual ao site... -->
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
        <div class="mx-auto" style="max-width:500px;">
            <div class="bg-white border border-danger border-2 rounded-3 shadow p-4" style="box-shadow:0 0 16px 2px #dc3545 !important; color:#111;">
                <h2 class="text-black mb-4 border-bottom pb-2" style="border-color:#dc3545!important;">Comprar Bilhete</h2>
                <?php if($evento): ?>
                    <p class="mb-2"><span class="fw-bold text-danger">Evento:</span> <span class="text-black fw-bold"><?= htmlspecialchars($evento['titulo']) ?></span></p>
                    <p class="mb-4"><span class="fw-bold text-danger">Data:</span> <span class="text-black fw-bold"><?= date('d/m/Y', strtotime($evento['data'])) ?></span></p>
                    <?= $mensagem ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label text-black">Nome</label>
                            <input type="text" name="nome" class="form-control border-danger" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-black">Email</label>
                            <input type="email" name="email" class="form-control border-danger" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-black">Quantidade</label>
                            <input type="number" name="quantidade" class="form-control border-danger" min="1" value="1" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Comprar</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-danger text-center bg-white text-black border border-danger">Evento não encontrado.</div>
                <?php endif; ?>
                <div class="mt-3 text-end">
                    <a href="admin.php#eventos" class="btn btn-outline-danger">Voltar à lista</a>
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
