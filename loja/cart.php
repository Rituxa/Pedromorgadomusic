<?php
session_start();

// Inicializar carrinho de eventos se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
// Inicializar carrinho de produtos genéricos se não existir
if (!isset($_SESSION['generic_cart'])) {
    $_SESSION['generic_cart'] = [];
}

// Processar dados do carrinho enviados via POST (JavaScript)
if (isset($_POST['cart_data'])) {
    $cartData = json_decode($_POST['cart_data'], true);
    if (is_array($cartData)) {
        foreach ($cartData as $item) {
            $name = $item['name'];
            $quantity = intval($item['qty']);
            $price = floatval($item['price']);
            // Tentar obter o ID do evento pelo nome
            $conn = new mysqli('localhost', 'root', 'Cv7ptcal%', 'pedro_morgado');
            $event_id = null;
            if (!$conn->connect_error) {
                $stmt = $conn->prepare("SELECT id FROM eventos WHERE titulo = ? LIMIT 1");
                $stmt->bind_param('s', $name);
                $stmt->execute();
                $stmt->bind_result($eid);
                if ($stmt->fetch()) {
                    $event_id = $eid;
                }
                $stmt->close();
                $conn->close();
            }
            if ($event_id) {
                // Se for um evento, adicionar ao carrinho de eventos
                if (isset($_SESSION['cart'][$event_id])) {
                    $_SESSION['cart'][$event_id] += $quantity;
                } else {
                    $_SESSION['cart'][$event_id] = $quantity;
                }
            } else {
                // Produto genérico (álbum, evento passado)
                $key = md5($name . $price);
                if (isset($_SESSION['generic_cart'][$key])) {
                    $_SESSION['generic_cart'][$key]['qty'] += $quantity;
                } else {
                    $_SESSION['generic_cart'][$key] = [
                        'name' => $name,
                        'qty' => $quantity,
                        'price' => $price
                    ];
                }
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// Adicionar evento ao carrinho via formulário (ex: página de evento)
if (isset($_POST['add']) && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    if (isset($_SESSION['cart'][$event_id])) {
        $_SESSION['cart'][$event_id] += $quantity;
    } else {
        $_SESSION['cart'][$event_id] = $quantity;
    }
    header('Location: cart.php');
    exit;
}

// Remover evento do carrinho
if (isset($_GET['remove'])) {
    $event_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$event_id]);
    header('Location: cart.php');
    exit;
}

// Remover produto genérico do carrinho
if (isset($_GET['remove_generic'])) {
    $key = $_GET['remove_generic'];
    unset($_SESSION['generic_cart'][$key]);
    header('Location: cart.php');
    exit;
}

// Obter detalhes dos eventos no carrinho
$events = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $conn = new mysqli('localhost', 'root', 'Cv7ptcal%', 'pedro_morgado');
    if (!$conn->connect_error) {
        $sql = "SELECT * FROM eventos WHERE id IN ($ids)";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $row['quantity'] = $_SESSION['cart'][$row['id']];
            $events[] = $row;
        }
        $conn->close();
    }
}

// Produtos genéricos (álbuns, eventos passados)
$generic_products = $_SESSION['generic_cart'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Roboto', 'Poppins', Arial, sans-serif;
            color: #222;
        }
        .cart-container {
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
        table {
            width: 100%;
            margin-bottom: 18px;
            background: #fff;
            color: #222;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dc3545;
            box-shadow: 0 2px 8px rgba(220,53,69,0.07);
        }
        th, td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #fff0f2;
            color: #dc3545;
            font-weight: 600;
            border-bottom: 2px solid #dc3545;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .btn-danger, .btn-outline-danger {
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-danger {
            background: #dc3545;
            border: 2px solid #dc3545;
        }
        .btn-outline-danger {
            border: 2px solid #dc3545;
            color: #dc3545;
        }
        .btn-outline-danger:hover {
            background: #dc3545;
            color: #fff;
        }
        .cart-message {
            margin-bottom: 18px;
            font-size: 1.1em;
        }
        .cart-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        @media (max-width: 600px) {
            .cart-container { padding: 16px 6px; }
            table, th, td { font-size: 0.98em; }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
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
                    <li class="nav-item"><a class="nav-link" href="../index.html">HOME</a></li>
                    <li class="nav-item"><a class="nav-link" href="../bio.html">BIOGRAPHY</a></li>
                    <li class="nav-item"><a class="nav-link" href="../discography.html">DISCOGRAPHY</a></li>
                    <li class="nav-item"><a class="nav-link" href="../gallery.html">GALLERY</a></li>
                    <li class="nav-item"><a class="nav-link" href="../dates.html">DATES</a></li>
                    <li class="nav-item"><a class="nav-link" href="../video.html">VIDEO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../shop.html">SHOP</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contacts.html">CONTACTS</a></li>
                </ul>
            </div>
        </div>
    </header>
    <main class="flex-fill">
        <div class="cart-container">
            <h1 class="mb-4 text-center text-danger" style="font-weight:700;letter-spacing:1px;">SHOPPING CART</h1>
            <?php 
            $showSuccess = false;
            if (isset($_SESSION['purchase_success']) && $_SESSION['purchase_success']) {
                echo '<div class="alert alert-success text-center" style="font-size:1.2em; margin-top:24px; border-radius:10px; border:2px solid #28a745; background:#eafaf1; color:#198754; font-weight:600;"><i class="fas fa-check-circle me-2"></i>Purchase completed successfully!</div>';
                unset($_SESSION['purchase_success']);
                // Esvaziar o carrinho após mostrar a mensagem
                $_SESSION['cart'] = [];
                $_SESSION['generic_cart'] = [];
                $showSuccess = true;
            }
            ?>
            <?php if($showSuccess): ?>
                <div class="alert alert-success text-center" style="font-size:1.2em; margin-top:24px; border-radius:10px; border:2px solid #28a745; background:#eafaf1; color:#198754; font-weight:600;">
                    <i class="fas fa-check-circle me-2"></i>Purchase completed successfully!
                </div>
            <?php elseif($events || $generic_products): ?>
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Product/Event</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($events as $ev): ?>
                        <tr style="color:#111;">
                            <td><?= htmlspecialchars($ev['titulo']) ?></td>
                            <td><?= $ev['quantity'] ?></td>
                            <td>€15.00</td>
                            <td>
                                <a href="cart.php?remove=<?= $ev['id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php foreach($generic_products as $key => $prod): ?>
                        <tr style="color:#111;">
                            <td><?= htmlspecialchars($prod['name']) ?></td>
                            <td><?= $prod['qty'] ?></td>
                            <td>€<?= number_format($prod['price'],2) ?></td>
                            <td>
                                <a href="cart.php?remove_generic=<?= $key ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-actions mt-3">
                    <a href="../shop.html" class="btn btn-outline-danger">Continue shopping</a>
                    <a href="checkout.php" class="btn btn-danger">Checkout</a>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center bg-white text-black border border-danger">Your cart is empty.</div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="../shop.html" class="btn btn-danger btn-lg" style="border-radius:10px; font-weight:600; padding:12px 36px; font-size:1.1em;"><i class="fas fa-arrow-left me-2"></i>Back to shop</a>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>