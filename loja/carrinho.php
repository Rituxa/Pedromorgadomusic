<?php
// carrinho.php unificado: suporta eventos e produtos genéricos (álbuns, etc.)
session_start();

// Inicializa carrinhos se não existirem
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['carrinho_generico'])) {
    $_SESSION['carrinho_generico'] = [];
}

// Adiciona itens ao carrinho via AJAX/JSON (ex: loja)
if (isset($_POST['cart_data'])) {
    $cartData = json_decode($_POST['cart_data'], true);
    if (is_array($cartData)) {
        foreach ($cartData as $item) {
            $nome = $item['name'];
            $quantidade = intval($item['qty']);
            $preco = floatval($item['price']);
            // Tenta obter o id do evento pelo nome
            $conn = new mysqli('localhost', 'root', 'Cv7ptcal%', 'pedro_morgado');
            $evento_id = null;
            if (!$conn->connect_error) {
                $stmt = $conn->prepare("SELECT id FROM eventos WHERE titulo = ? LIMIT 1");
                $stmt->bind_param('s', $nome);
                $stmt->execute();
                $stmt->bind_result($eid);
                if ($stmt->fetch()) {
                    $evento_id = $eid;
                }
                $stmt->close();
                $conn->close();
            }
            if ($evento_id) {
                // Se for evento, adiciona ao carrinho de eventos
                if (isset($_SESSION['carrinho'][$evento_id])) {
                    $_SESSION['carrinho'][$evento_id] += $quantidade;
                } else {
                    $_SESSION['carrinho'][$evento_id] = $quantidade;
                }
            } else {
                // Produto genérico (álbum, evento passado)
                $key = md5($nome . $preco);
                if (isset($_SESSION['carrinho_generico'][$key])) {
                    $_SESSION['carrinho_generico'][$key]['qty'] += $quantidade;
                } else {
                    $_SESSION['carrinho_generico'][$key] = [
                        'name' => $nome,
                        'qty' => $quantidade,
                        'price' => $preco
                    ];
                }
            }
        }
    }
    header('Location: carrinho.php');
    exit;
}

// Adiciona evento ao carrinho via formulário (ex: página de evento)
if (isset($_POST['add']) && isset($_POST['evento_id'])) {
    $evento_id = intval($_POST['evento_id']);
    $quantidade = max(1, intval($_POST['quantidade'] ?? 1));
    if (isset($_SESSION['carrinho'][$evento_id])) {
        $_SESSION['carrinho'][$evento_id] += $quantidade;
    } else {
        $_SESSION['carrinho'][$evento_id] = $quantidade;
    }
    header('Location: carrinho.php');
    exit;
}

// Remover evento do carrinho
if (isset($_GET['remover'])) {
    $evento_id = intval($_GET['remover']);
    unset($_SESSION['carrinho'][$evento_id]);
    header('Location: carrinho.php');
    exit;
}

// Remover produto genérico do carrinho
if (isset($_GET['remover_generico'])) {
    $key = $_GET['remover_generico'];
    unset($_SESSION['carrinho_generico'][$key]);
    header('Location: carrinho.php');
    exit;
}

// Obter detalhes dos eventos no carrinho
$eventos = [];
if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
    $conn = new mysqli('localhost', 'root', 'Cv7ptcal%', 'pedro_morgado');
    if (!$conn->connect_error) {
        $sql = "SELECT * FROM eventos WHERE id IN ($ids)";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $row['quantidade'] = $_SESSION['carrinho'][$row['id']];
            $eventos[] = $row;
        }
        $conn->close();
    }
}

// Produtos genéricos (álbuns, eventos passados)
$produtos_genericos = $_SESSION['carrinho_generico'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
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
    <main class="flex-fill">
        <div class="cart-container">
            <h1 class="mb-4 text-center text-danger" style="font-weight:700;letter-spacing:1px;">CARRINHO DE COMPRAS</h1>
            <?php 
            if (isset($_SESSION['compra_sucesso']) && $_SESSION['compra_sucesso']) {
                echo '<div class="alert alert-success text-center" style="font-size:1.2em; margin-top:24px; border-radius:10px; border:2px solid #28a745; background:#eafaf1; color:#198754; font-weight:600;"><i class="fas fa-check-circle me-2"></i>Compra realizada com sucesso!</div>';
                unset($_SESSION['compra_sucesso']);
                // Esvazia o carrinho após mostrar a mensagem
                $_SESSION['carrinho'] = [];
                $_SESSION['carrinho_generico'] = [];
            }
            ?>
            <?php if($eventos || $produtos_genericos): ?>
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Produto/Evento</th>
                            <th>Quantidade</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($eventos as $ev): ?>
                        <tr style="color:#111;">
                            <td><?= htmlspecialchars($ev['titulo']) ?></td>
                            <td><?= $ev['quantidade'] ?></td>
                            <td>€15.00</td>
                            <td>
                                <a href="carrinho.php?remover=<?= $ev['id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php foreach($produtos_genericos as $key => $prod): ?>
                        <tr style="color:#111;">
                            <td><?= htmlspecialchars($prod['name']) ?></td>
                            <td><?= $prod['qty'] ?></td>
                            <td>€<?= number_format($prod['price'],2) ?></td>
                            <td>
                                <a href="carrinho.php?remover_generico=<?= $key ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-actions mt-3">
                    <a href="../shop-pt.html" class="btn btn-outline-danger">Continuar a comprar</a>
                    <a href="checkout-pt.php" class="btn btn-danger">Finalizar compra</a>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center bg-white text-black border border-danger">O seu carrinho está vazio.</div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="../shop-pt.html" class="btn btn-danger btn-lg" style="border-radius:10px; font-weight:600; padding:12px 36px; font-size:1.1em;"><i class="fas fa-arrow-left me-2"></i>Voltar à loja</a>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
