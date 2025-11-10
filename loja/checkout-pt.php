<?php
// Inicia a sessão e valida se o utilizador está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../registo/login.php");
    exit;
}

require_once("../config/database.php");

$user_id = $_SESSION['user_id'];

// Se o carrinho estiver vazio, mostra mensagem e termina
if ((empty($_SESSION['carrinho']) && empty($_SESSION['carrinho_generico'])) || (!isset($_SESSION['carrinho']) && !isset($_SESSION['carrinho_generico']))) {
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Finalizar Compra</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap">
        <style>
            body {
                background: #f8f9fa;
                font-family: 'Roboto', 'Poppins', Arial, sans-serif;
                color: #222;
            }
            .checkout-container {
                max-width: 700px;
                margin: 60px auto;
                background: #fff;
                border: 2px solid #dc3545;
                border-radius: 16px;
                padding: 32px 28px 24px 28px;
                box-shadow: 0 4px 24px rgba(220,53,69,0.13);
            }
            h2 {
                color: #dc3545;
                margin-bottom: 24px;
                text-align: center;
                font-weight: 700;
                letter-spacing: 1px;
            }
            .checkout-container a.btn {
                display: block;
                width: 100%;
                margin-top: 18px;
                border-radius: 8px;
                font-weight: 600;
                background: #dc3545;
                color: #fff;
                border: 2px solid #dc3545;
                padding: 10px 0;
                text-decoration: none;
                transition: background 0.3s, color 0.3s;
            }
            .checkout-container a.btn:hover {
                background: #ffcc00;
                color: #222;
            }
        </style>
    </head>
    <body class="d-flex flex-column min-vh-100">
        <header class="bg-dark text-white py-3 mb-4">
            <div class="container">
                <nav class="navbar">
                    <!-- Removed 'Voltar à Loja' button from header -->
                </nav>
            </div>
        </header>
        <main class="flex-fill">
            <div class="checkout-container">
                <h2>O carrinho está vazio.</h2>
                <p><a href="../shop-pt.html" class="btn btn-loja">Voltar à loja</a></p>
            </div>
        </main>
        <footer class="bg-dark text-light text-center py-3 mt-auto">
            <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
            <div>
                <a href="https://www.facebook.com/pedro.morgado.9"><i class="fab fa-facebook-f text-danger border border-dark rounded-circle"></i></a>
                <a href="https://www.instagram.com/_pedro_morgado_/" ><i class="fab fa-instagram text-danger border border-dark rounded-circle"></i></a>
                <a href="https://www.youtube.com/playlist?list=OLAK5uy_llGenPOe9vHI7BsTAKVT3AvjtIyCQd_IE"><i class="fab fa-youtube text-danger border border-dark rounded-circle"></i></a>
            </div>
        </footer>
    </body>
    </html>
    <?php
    exit();
}

try {
    // Inicia transação para registar a encomenda
    $pdo->beginTransaction();

    // Cria a encomenda
    $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $order_id = $pdo->lastInsertId();

    // Adiciona apenas eventos válidos à tabela order_items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    if (!empty($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $key => $item) {
            // Só insere se o id existir na tabela eventos
            $check = $pdo->prepare("SELECT COUNT(*) FROM eventos WHERE id = ?");
            $check->execute([$key]);
            if ($check->fetchColumn() > 0) {
                $stmt->execute([$order_id, $key, $item]);
            }
        }
    }
    // Produtos genéricos não são inseridos em order_items devido à restrição da foreign key

    $pdo->commit();
    $_SESSION['carrinho'] = [];
    $_SESSION['carrinho_generico'] = [];
    $_SESSION['compra_sucesso'] = true;
    header('Location: carrinho.php');
    exit;
} catch (Exception $e) {
    // Em caso de erro, faz rollback e mostra mensagem
    $pdo->rollBack();
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Finalizar Compra</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap">
        <style>
            body {
                background: #f8f9fa;
                font-family: 'Roboto', 'Poppins', Arial, sans-serif;
                color: #222;
            }
            .checkout-container {
                max-width: 700px;
                margin: 60px auto;
                background: #fff;
                border: 2px solid #dc3545;
                border-radius: 16px;
                padding: 32px 28px 24px 28px;
                box-shadow: 0 4px 24px rgba(220,53,69,0.13);
            }
            h2 {
                color: #dc3545;
                margin-bottom: 24px;
                text-align: center;
                font-weight: 700;
                letter-spacing: 1px;
            }
            .checkout-container a.btn {
                display: block;
                width: 100%;
                margin-top: 18px;
                border-radius: 8px;
                font-weight: 600;
                background: #dc3545;
                color: #fff;
                border: 2px solid #dc3545;
                padding: 10px 0;
                text-decoration: none;
                transition: background 0.3s, color 0.3s;
            }
            .checkout-container a.btn:hover {
                background: #ffcc00;
                color: #222;
            }
        </style>
    </head>
    <body class="d-flex flex-column min-vh-100">
        <header class="bg-dark text-white py-3 mb-4">
            <div class="container">
                <nav class="navbar">
                    <!-- Removed 'Voltar à Loja' button from header -->
                </nav>
            </div>
        </header>
        <main class="flex-fill">
            <div class="checkout-container">
                <h2>Erro ao finalizar pedido</h2>
                <p><?= htmlspecialchars($e->getMessage()) ?></p>
                <p><a href="../loja.html" class="btn btn-loja">Voltar à loja</a></p>
            </div>
        </main>
        <footer class="bg-dark text-light text-center py-3 mt-auto">
            <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
            <div>
                <a href="https://www.facebook.com/pedro.morgado.9"><i class="fab fa-facebook-f text-danger border border-dark rounded-circle"></i></a>
                <a href="https://www.instagram.com/_pedro_morgado_/" ><i class="fab fa-instagram text-danger border border-dark rounded-circle"></i></a>
                <a href="https://www.youtube.com/playlist?list=OLAK5uy_llGenPOe9vHI7BsTAKVT3AvjtIyCQd_IE"><i class="fab fa-youtube text-danger border border-dark rounded-circle"></i></a>
            </div>
        </footer>
    </body>
    </html>
    <?php
}
