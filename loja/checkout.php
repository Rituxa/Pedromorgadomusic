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
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Checkout</title>
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
                    <!-- Removed 'Back to Shop' button from header -->
                </nav>
            </div>
        </header>
        <main class="flex-fill">
            <div class="checkout-container">
                <h2>The cart is empty.</h2>
                <p><a href="../shop.html" class="btn btn-shop">Back to shop</a></p>
            </div>
        </main>
        <footer class="bg-dark text-light text-center py-3 mt-auto">
            <p>© 2025 Pedro Morgado. All rights reserved. Developed by Rita Nunes</p>
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

    // Adiciona os itens à encomenda
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    foreach ($_SESSION['cart'] as $key => $item) {
        if (is_array($item) && isset($item['id']) && isset($item['quantity'])) {
            // Carrinho no formato array de arrays
            $stmt->execute([$order_id, $item['id'], $item['quantity']]);
        } else {
            // Carrinho no formato [id => quantidade]
            $stmt->execute([$order_id, $key, $item]);
        }
    }

    $pdo->commit();
    $_SESSION['cart'] = [];
    $_SESSION['order_success'] = true;
    header('Location: cart.php');
    exit;
} catch (Exception $e) {
    // Em caso de erro, faz mostra mensagem
    $pdo->rollBack();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Checkout</title>
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
                    <a href="../shop.html" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Shop
                    </a>
                </nav>
            </div>
        </header>
        <main class="flex-fill">
            <div class="checkout-container">
                <h2>Error placing order</h2>
                <p><?= htmlspecialchars($e->getMessage()) ?></p>
                <p><a href="../shop.html" class="btn btn-shop">Back to shop</a></p>
            </div>
        </main>
        <footer class="bg-dark text-light text-center py-3 mt-auto">
            <p>© 2025 Pedro Morgado. All rights reserved. Developed by Rita Nunes</p>
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