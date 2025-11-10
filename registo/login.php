<?php
// Inicia a sessão e liga à base de dados
session_start();
require_once "../config/database.php";

$msg = '';
// Processa o login do utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $conn->prepare("SELECT idutilizadores, nome, password, is_admin FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $nome, $hash, $is_admin);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            // Guarda dados do utilizador na sessão
            $_SESSION['user_email'] = $email;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['is_admin'] = $is_admin;
            $stmt->close();
            // Redireciona para a página anterior, se existir
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            if ($is_admin) {
                header('Location: admin.php');
                exit;
            } elseif ($redirect) {
                header('Location: ' . $redirect);
                exit;
            } else {
                header('Location: ../shop-pt.html');
                exit;
            }
        } else {
            $msg = '<div class="alert alert-danger">Email ou palavra-passe incorretos.</div>';
        }
        $stmt->close();
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos.</div>';
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
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <header class="bg-dark text-white py-3 mb-4">
        <div class="container">
            <h1 class="text-danger text-center">PEDRO MORGADO</h1>
        </div>
    </header>
    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="bg-white border border-danger border-2 rounded-3 shadow p-4" style="box-shadow:0 0 16px 2px #dc3545 !important; color:#111;">
                    <h2 class="text-black mb-4 border-bottom pb-2" style="border-color:#dc3545!important;">Login</h2>
                    <?= $msg ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label text-black">Email</label>
                            <input type="email" name="email" class="form-control border-danger" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-black">Palavra-passe</label>
                            <input type="password" name="password" class="form-control border-danger" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Entrar</button>
                    </form>
                    <div class="mt-3">
                        <a href="register.html">Ainda não tem conta? Registe-se</a>
                    </div>
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
