<?php
// Inicia a sessão e liga à base de dados
session_start();
require_once "../base dados/db.php";

// Verifica se o utilizador tem perfil de administrador (is_admin=1)
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}
$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT is_admin FROM utilizadores WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($is_admin);
if (!$stmt->fetch() || $is_admin != 1) {
    $stmt->close();
    header('Location: login.php');
    exit;
}
$stmt->close();
$msg = '';

// Elimina utilizador
if (isset($_GET['del_user'])) {
    $id = intval($_GET['del_user']);
    $stmt = $conn->prepare("DELETE FROM utilizadores WHERE idutilizadores = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Utilizador eliminado.</div>';
}

// Elimina evento
if (isset($_GET['del_evento'])) {
    $id = intval($_GET['del_evento']);
    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Evento eliminado.</div>';
}

// Elimina compra
if (isset($_GET['del_compra'])) {
    $id = intval($_GET['del_compra']);
    $stmt = $conn->prepare("DELETE FROM bilhetes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Compra eliminada.</div>';
}

// Elimina mensagem
if (isset($_GET['del_mensagem'])) {
    $id = intval($_GET['del_mensagem']);
    $stmt = $conn->prepare("DELETE FROM mensagens WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Mensagem eliminada.</div>';
}

// Criar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_evento'])) {
    $titulo = trim($_POST['titulo']);
    $data = trim($_POST['data']);
    $hora = trim($_POST['hora'] ?? '');
    $local = trim($_POST['local'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    
    if ($titulo && $data && $hora && $local) {
        $stmt = $conn->prepare("INSERT INTO eventos (titulo, data, hora, local, descricao) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $titulo, $data, $hora, $local, $descricao);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Evento criado com sucesso.</div>';
            header('Location: admin.php#eventos');
            exit;
        } else {
            $msg = '<div class="alert alert-danger">Erro ao criar evento.</div>';
        }
        $stmt->close();
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos obrigatórios.</div>';
    }
}

// Atualizar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_evento'])) {
    $evento_id = intval($_POST['evento_id']);
    $titulo = trim($_POST['titulo']);
    $data = trim($_POST['data']);
    $hora = trim($_POST['hora'] ?? '');
    $local = trim($_POST['local'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    
    if ($titulo && $data && $hora && $local) {
        $stmt = $conn->prepare("UPDATE eventos SET titulo=?, data=?, hora=?, local=?, descricao=? WHERE id=?");
        $stmt->bind_param("sssssi", $titulo, $data, $hora, $local, $descricao, $evento_id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Evento atualizado com sucesso.</div>';
            header('Location: admin.php#eventos');
            exit;
        } else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar evento.</div>';
        }
        $stmt->close();
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos obrigatórios.</div>';
    }
}

// Adiciona evento (compatibilidade com formulário antigo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['data']) && !isset($_POST['create_evento']) && !isset($_POST['update_evento'])) {
    $titulo = trim($_POST['titulo']);
    $data = trim($_POST['data']);
    $hora = trim($_POST['hora'] ?? '');
    $local = trim($_POST['local'] ?? '');
    if ($titulo && $data && $hora && $local) {
        $conn2 = new mysqli($host, $user, $pass, $db);
        if (!$conn2->connect_errno) {
            $stmt = $conn2->prepare("INSERT INTO eventos (titulo, data, hora, local) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titulo, $data, $hora, $local);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">Evento criado com sucesso.</div>';
                header('Location: admin.php');
                exit;
            } else {
                $msg = '<div class="alert alert-danger">Erro ao criar evento.</div>';
            }
            $stmt->close();
        }
        $conn2->close();
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos.</div>';
    }
}

// Regista o acesso do utilizador
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    $access_time = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO user_access_log (email, access_time) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_email, $access_time);
    $stmt->execute();
    $stmt->close();
}

// Conta acessos diários
$today = date('Y-m-d');
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM user_access_log WHERE DATE(access_time) = ?");
$stmt_count->bind_param("s", $today);
$stmt_count->execute();
$daily_access_count = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$stmt_details = $conn->prepare("SELECT email, access_time FROM user_access_log WHERE DATE(access_time) = ? ORDER BY access_time DESC");
$stmt_details->bind_param("s", $today);
$stmt_details->execute();
$daily_access_details = $stmt_details->get_result();

// Lista utilizadores
$users = $conn->query("SELECT idutilizadores, nome, email FROM utilizadores");
// Lista eventos com funcionalidade de pesquisa
$q_eventos = isset($_GET['q_eventos']) ? trim($_GET['q_eventos']) : '';
$data_especifica = isset($_GET['data_especifica']) ? trim($_GET['data_especifica']) : '';
$filtro_eventos = isset($_GET['filtro_eventos']) ? $_GET['filtro_eventos'] : 'todos';

$sql_eventos = "SELECT * FROM eventos WHERE 1";
$params_eventos = [];

// Filtro de pesquisa por texto (título ou local)
if ($q_eventos !== '') {
    $sql_eventos .= " AND (titulo LIKE ? OR local LIKE ?)";
    $params_eventos[] = "%$q_eventos%";
    $params_eventos[] = "%$q_eventos%";
}

// Filtro por data específica
if ($data_especifica !== '') {
    $sql_eventos .= " AND data = ?";
    $params_eventos[] = $data_especifica;
}

// Filtro de data (futuros, passados ou todos)
$hoje = date('Y-m-d');
if ($filtro_eventos === 'futuros') {
    $sql_eventos .= " AND data >= ?";
    $params_eventos[] = $hoje;
} elseif ($filtro_eventos === 'passados') {
    $sql_eventos .= " AND data < ?";
    $params_eventos[] = $hoje;
}
$sql_eventos .= " ORDER BY data DESC";

$stmt_eventos = $conn->prepare($sql_eventos);
if ($params_eventos) {
    $stmt_eventos->execute($params_eventos);
} else {
    $stmt_eventos->execute();
}
$eventos_completos = $stmt_eventos->get_result();

// Buscar dados do evento para edição
$evento_edit = null;
if (isset($_GET['edit_evento'])) {
    $edit_id = intval($_GET['edit_evento']);
    $stmt_edit = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $evento_edit = $stmt_edit->get_result()->fetch_assoc();
    $stmt_edit->close();
}

// Lista eventos simples para o painel principal (mantido para compatibilidade)
$eventos = $conn->query("SELECT * FROM eventos ORDER BY data DESC");
// Lista compras
$compras = $conn->query("SELECT b.id, b.email, b.quantidade, b.comprado_em, b.status, e.titulo FROM bilhetes b JOIN eventos e ON b.evento_id = e.id ORDER BY b.comprado_em DESC");
// Lista mensagens enviadas pelo formulário de contatos
$mensagens = $conn->query("SELECT id, nome, apelido, email, telefone, mensagem, data_envio FROM mensagens ORDER BY data_envio DESC");

// Atualiza o status da compra
if (isset($_POST['update_status']) && isset($_POST['compra_id'], $_POST['novo_status'])) {
    $compra_id = intval($_POST['compra_id']);
    $novo_status = $_POST['novo_status'];
    $stmt = $conn->prepare("UPDATE bilhetes SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $compra_id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Status atualizado com sucesso.</div>';
}

// Processa o envio da resposta
if (isset($_POST['send_reply'], $_POST['message_id'], $_POST['email'], $_POST['reply'])) {
    $message_id = intval($_POST['message_id']);
    $email = trim($_POST['email']);
    $reply = trim($_POST['reply']);

    if ($message_id && $email && $reply) {
        // Envia o email de resposta
        $to = $email;
        $subject = "Resposta à sua mensagem";
        $headers = "From: no-reply@pedromorgado.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($to, $subject, $reply, $headers)) {
            $msg = '<div class="alert alert-success">Resposta enviada com sucesso.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Erro ao enviar a resposta.</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">Preencha todos os campos para enviar a resposta.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDRO MORGADO - Painel de Administração</title>
    <meta name="description" content="Painel de administração do Pedro Morgado para gerir eventos, utilizadores e encomendas.">
    <meta name="keywords" content="Pedro Morgado, admin, administração, gestão">
    <meta name="author" content="Pedro Morgado">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        .collapsible {
            background-color: #dc3545;
            color: #fff;
            cursor: pointer;
            padding: 15px 20px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(220,53,69,0.15);
        }

        .active, .collapsible:hover {
            background-color: #b71c1c;
            box-shadow: 0 4px 16px rgba(220,53,69,0.25);
        }

        .content {
            padding: 20px;
            display: none;
            overflow: hidden;
            background-color: #f9f9f9;
            border-radius: 0 0 8px 8px;
            border: 2px solid #dc3545;
            border-top: none;
        }

        .admin-card {
            background: #fff;
            border: 2px solid #dc3545;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(220,53,69,0.18);
            padding: 24px;
            margin-bottom: 24px;
        }

        .admin-card h3 {
            color: #dc3545;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-card h3.collapsible-header {
            cursor: pointer;
            transition: color 0.3s ease;
            user-select: none;
        }
        
        .admin-card h3.collapsible-header:hover {
            color: #b71c1c;
        }
        
        .admin-card h3.collapsible-header i.fa-chevron-down,
        .admin-card h3.collapsible-header i.fa-chevron-right {
            transition: transform 0.3s ease;
        }

        .admin-card h3 i {
            font-size: 1.2em;
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
                <div>
                    <span class="d-inline-block" style="font-size:1.1rem;" id="user-session-header">
                        <a href="../shop-pt.html" class="lang-link">PT</a> / <a href="../shop.html" class="lang-link">EN</a>
                        <a href="../shop.html" class="ms-3" title="Sair da Administração"><i class="fas fa-sign-out-alt text-danger" style="font-size:1.3rem;"></i></a>
                    </span>
                </div>
            </div>
            <div class="collapse navbar-collapse position-absolute" id="mainNavbar" style="top:60px;left:0;z-index:30;background:#222;border-radius:0 0 10px 0;min-width:220px;">
                <ul class="navbar-nav gap-2 p-3">
                    <li class="nav-item"><a class="nav-link active" href="admin.php">ADMINISTRAÇÃO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.html">INÍCIO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../discography.html">DISCOGRAFIA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../gallery.html">GALERIA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../video.html">VÍDEO</a></li>
                    <li class="nav-item"><a class="nav-link" href="../shop.html">LOJA</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contacts.html">CONTACTOS</a></li>
                </ul>
            </div>
        </div>
    </header>
<    <main class="flex-fill">
        <div class="container">
            <h2 class="text-center my-4">Painel de Administração</h2>
            <?= $msg ?>
            
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="admin-card">
                        <h3 class="d-flex justify-content-between align-items-center collapsible-header" data-bs-toggle="collapse" data-bs-target="#estatisticasCollapse" aria-expanded="false" aria-controls="estatisticasCollapse">
                            <span><i class="fas fa-chart-line"></i>Estatísticas de Acessos Diários</span>
                            <i class="fas fa-chevron-right" id="estatisticasChevron"></i>
                        </h3>
                        
                        <div class="collapse" id="estatisticasCollapse">
                            <p class="text-muted mb-3">Total de acessos hoje: <strong><?= $daily_access_count ?></strong></p>
                            <button type="button" class="collapsible">
                                <i class="fas fa-eye me-2"></i>Ver Detalhes dos Acessos
                            </button>
                            <div class="content">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead class="table-danger">
                                            <tr>
                                                <th><i class="fas fa-envelope me-1"></i>Email</th>
                                                <th><i class="fas fa-clock me-1"></i>Data e Hora</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php while($access = $daily_access_details->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($access['email']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($access['access_time'])) ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="admin-card">
                        <h3 class="d-flex justify-content-between align-items-center collapsible-header" data-bs-toggle="collapse" data-bs-target="#utilizadoresCollapse" aria-expanded="false" aria-controls="utilizadoresCollapse">
                            <span><i class="fas fa-users"></i>Gestão de Utilizadores</span>
                            <i class="fas fa-chevron-right" id="utilizadoresChevron"></i>
                        </h3>
                        
                        <div class="collapse" id="utilizadoresCollapse">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-danger">
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Nome</th>
                                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                                            <th><i class="fas fa-cogs me-1"></i>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while($u = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['nome']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="editar_utilizador.php?id=<?= $u['idutilizadores'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Editar
                                                </a>
                                                <a href="?del_user=<?= $u['idutilizadores'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar utilizador?')">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center" id="eventos">
                <div class="col-12 col-lg-10">
                    <div class="admin-card">
                        <h3 class="d-flex justify-content-between align-items-center collapsible-header" data-bs-toggle="collapse" data-bs-target="#eventosCollapse" aria-expanded="true" aria-controls="eventosCollapse">
                            <span><i class="fas fa-calendar-alt"></i>Gestão de Eventos</span>
                            <i class="fas fa-chevron-down" id="eventosChevron"></i>
                        </h3>
                        
                        <div class="collapse show" id="eventosCollapse">
                            <!-- Formulário de Pesquisa -->
                            <div class="row g-3 mb-4 p-3" style="background:#f8f9fa; border-radius:12px; border:1px solid #dc3545;">
                                <form method="get" class="row g-2">
                                    <input type="hidden" name="section" value="eventos">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Pesquisar por título/local</label>
                                        <input type="text" name="q_eventos" class="form-control" placeholder="Pesquisar eventos..." value="<?= htmlspecialchars($q_eventos) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Data específica</label>
                                        <input type="date" name="data_especifica" class="form-control" value="<?= htmlspecialchars($data_especifica) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Filtro temporal</label>
                                        <select name="filtro_eventos" class="form-select">
                                            <option value="todos" <?= $filtro_eventos==='todos'?'selected':'' ?>>Todos</option>
                                            <option value="futuros" <?= $filtro_eventos==='futuros'?'selected':'' ?>>Futuros</option>
                                            <option value="passados" <?= $filtro_eventos==='passados'?'selected':'' ?>>Passados</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end gap-1">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-search me-1"></i>Pesquisar
                                        </button>
                                        <a href="admin.php#eventos" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Limpar
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <div class="mb-3 d-flex gap-2 align-items-center justify-content-between flex-wrap">
                                <div>
                                    <a href="admin.php?criar=1#form-novo-evento" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Criar Evento
                                    </a>
                                    <span class="text-muted ms-3"><i class="fas fa-info-circle me-1"></i>Total: <?= $eventos_completos->num_rows ?> evento(s)</span>
                                </div>
                            </div>
                            
                            <!-- Tabela de Eventos -->
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-danger">
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                            <th><i class="fas fa-tag me-1"></i>Título</th>
                                            <th><i class="fas fa-calendar me-1"></i>Data</th>
                                            <th><i class="fas fa-clock me-1"></i>Hora</th>
                                            <th><i class="fas fa-map-marker-alt me-1"></i>Local</th>
                                            <th><i class="fas fa-cogs me-1"></i>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if ($eventos_completos->num_rows === 0): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times text-muted me-2"></i>Nenhum evento encontrado.
                                        </td></tr>
                                    <?php else: ?>
                                        <?php while($e = $eventos_completos->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= $e['id'] ?></span></td>
                                            <td class="fw-bold"><?= htmlspecialchars($e['titulo']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($e['data'])) ?></td>
                                            <td><?= htmlspecialchars($e['hora']) ?></td>
                                            <td><?= htmlspecialchars($e['local']) ?></td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" style="gap:2px;">
                                                <a href="detalhes_evento.php?id=<?= $e['id'] ?>" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye me-1"></i>Ver
                                                </a>
                                                <a href="admin.php?edit_evento=<?= $e['id'] ?>#form-evento" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Editar
                                                </a>
                                                <a href="admin.php?del_evento=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar evento?')">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Formulário de Criação/Edição -->
                        <?php if(isset($_GET['criar']) || isset($_GET['edit_evento'])): ?>
                        <div id="form-evento" class="mt-4">
                            <div class="admin-card" style="background:#f8f9fa; border-color:#28a745;">
                                <h4 class="text-success mb-3">
                                    <i class="fas fa-<?= isset($_GET['edit_evento']) ? 'edit' : 'plus' ?> me-2"></i>
                                    <?php echo isset($_GET['edit_evento']) ? 'Editar Evento' : 'Novo Evento'; ?>
                                </h4>
                                <form method="post" action="admin.php?section=eventos#form-evento">
                                    <?php if(isset($_GET['edit_evento'])): ?>
                                    <input type="hidden" name="evento_id" value="<?= intval($_GET['edit_evento']) ?>">
                                    <?php endif; ?>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-tag me-1"></i>Título *
                                            </label>
                                            <input type="text" name="titulo" class="form-control" value="<?= $evento_edit ? htmlspecialchars($evento_edit['titulo']) : '' ?>" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-calendar me-1"></i>Data *
                                            </label>
                                            <input type="date" name="data" class="form-control" value="<?= $evento_edit ? $evento_edit['data'] : '' ?>" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-clock me-1"></i>Hora *
                                            </label>
                                            <input type="time" name="hora" class="form-control" value="<?= $evento_edit ? $evento_edit['hora'] : '' ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-map-marker-alt me-1"></i>Local *
                                        </label>
                                        <input type="text" name="local" class="form-control" value="<?= $evento_edit ? htmlspecialchars($evento_edit['local']) : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-align-left me-1"></i>Descrição (opcional)
                                        </label>
                                        <textarea name="descricao" class="form-control" rows="3" placeholder="Descrição do evento..."><?= $evento_edit ? htmlspecialchars($evento_edit['descricao'] ?? '') : '' ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="<?= isset($_GET['edit_evento']) ? 'update_evento' : 'create_evento' ?>" class="btn btn-success">
                                            <i class="fas fa-<?= isset($_GET['edit_evento']) ? 'save' : 'plus' ?> me-2"></i>
                                            <?= isset($_GET['edit_evento']) ? 'Actualizar' : 'Criar' ?> Evento
                                        </button>
                                        <a href="admin.php#eventos" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                        </div> <!-- Fim do collapse eventosCollapse -->
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="admin-card">
                        <h3 class="d-flex justify-content-between align-items-center collapsible-header" data-bs-toggle="collapse" data-bs-target="#encomendasCollapse" aria-expanded="false" aria-controls="encomendasCollapse">
                            <span><i class="fas fa-shopping-cart"></i>Gestão de Encomendas</span>
                            <i class="fas fa-chevron-right" id="encomendasChevron"></i>
                        </h3>
                        
                        <div class="collapse" id="encomendasCollapse">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-danger">
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Utilizador</th>
                                            <th><i class="fas fa-calendar-alt me-1"></i>Evento</th>
                                            <th><i class="fas fa-sort-numeric-up me-1"></i>Qtd</th>
                                            <th><i class="fas fa-clock me-1"></i>Data</th>
                                            <th><i class="fas fa-info-circle me-1"></i>Estado</th>
                                            <th><i class="fas fa-cogs me-1"></i>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while($c = $compras->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['email']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($c['titulo']) ?></td>
                                        <td><span class="badge bg-info"><?= $c['quantidade'] ?></span></td>
                                        <td><?= date('d/m/Y H:i', strtotime($c['comprado_em'])) ?></td>
                                        <td>
                                            <form method="post" class="d-flex align-items-center gap-2 mb-0">
                                                <input type="hidden" name="compra_id" value="<?= $c['id'] ?>">
                                                <select name="novo_status" class="form-select form-select-sm">
                                                    <option value="Pendente" <?= ($c['status'] == 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                                                    <option value="Pago" <?= ($c['status'] == 'Pago') ? 'selected' : '' ?>>Pago</option>
                                                    <option value="Cancelado" <?= ($c['status'] == 'Cancelado') ? 'selected' : '' ?>>Cancelado</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save me-1"></i>Actualizar
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?del_compra=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar encomenda?')">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="admin-card">
                        <h3 class="d-flex justify-content-between align-items-center collapsible-header" data-bs-toggle="collapse" data-bs-target="#mensagensCollapse" aria-expanded="false" aria-controls="mensagensCollapse">
                            <span><i class="fas fa-envelope"></i>Gestão de Mensagens</span>
                            <i class="fas fa-chevron-right" id="mensagensChevron"></i>
                        </h3>
                        
                        <div class="collapse" id="mensagensCollapse">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-danger">
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Nome</th>
                                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                                            <th><i class="fas fa-comment me-1"></i>Mensagem</th>
                                            <th><i class="fas fa-clock me-1"></i>Data</th>
                                            <th><i class="fas fa-cogs me-1"></i>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while($m = $mensagens->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($m['nome'] . ' ' . $m['apelido']) ?></td>
                                        <td><?= htmlspecialchars($m['email']) ?></td>
                                        <td>
                                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['mensagem']) ?>">
                                                <?= htmlspecialchars($m['mensagem']) ?>
                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($m['data_envio'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="showReplyForm(<?= $m['id'] ?>, '<?= htmlspecialchars($m['email'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-reply me-1"></i>Responder
                                                </button>
                                                <a href="?del_mensagem=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar mensagem?')">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<    <footer class="bg-dark text-light text-center py-3">
        <p>© 2025 Pedro Morgado. Todos os direitos reservados. Desenvolvido por Rita Nunes</p>
        <div>
            <a href="https://www.facebook.com/pedro.morgado.9"><i class="fab fa-facebook-f text-danger border border-dark rounded-circle"></i></a>
            <a href="https://www.instagram.com/_pedro_morgado_/"><i class="fab fa-instagram text-danger border border-dark rounded-circle"></i></a>
            <a href="https://www.youtube.com/playlist?list=OLAK5uy_llGenPOe9vHI7BsTAKVT3AvjtIyCQd_IE"><i class="fab fa-youtube text-danger border border-dark rounded-circle"></i></a>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var coll = document.getElementsByClassName("collapsible");
            for (var i = 0; i < coll.length; i++) {
                coll[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var content = this.nextElementSibling;
                    if (content.style.display === "block") {
                        content.style.display = "none";
                    } else {
                        content.style.display = "block";
                    }
                });
            }
            
            // Controlar ícone chevron da secção de eventos
            const eventosCollapse = document.getElementById('eventosCollapse');
            const eventosChevron = document.getElementById('eventosChevron');
            
            if (eventosCollapse && eventosChevron) {
                eventosCollapse.addEventListener('show.bs.collapse', function () {
                    eventosChevron.classList.remove('fa-chevron-right');
                    eventosChevron.classList.add('fa-chevron-down');
                });
                
                eventosCollapse.addEventListener('hide.bs.collapse', function () {
                    eventosChevron.classList.remove('fa-chevron-down');
                    eventosChevron.classList.add('fa-chevron-right');
                });
            }
            
            // Controlar ícone chevron da secção de estatísticas
            const estatisticasCollapse = document.getElementById('estatisticasCollapse');
            const estatisticasChevron = document.getElementById('estatisticasChevron');
            
            if (estatisticasCollapse && estatisticasChevron) {
                estatisticasCollapse.addEventListener('show.bs.collapse', function () {
                    estatisticasChevron.classList.remove('fa-chevron-right');
                    estatisticasChevron.classList.add('fa-chevron-down');
                });
                
                estatisticasCollapse.addEventListener('hide.bs.collapse', function () {
                    estatisticasChevron.classList.remove('fa-chevron-down');
                    estatisticasChevron.classList.add('fa-chevron-right');
                });
            }
            
            // Controlar ícone chevron da secção de utilizadores
            const utilizadoresCollapse = document.getElementById('utilizadoresCollapse');
            const utilizadoresChevron = document.getElementById('utilizadoresChevron');
            
            if (utilizadoresCollapse && utilizadoresChevron) {
                utilizadoresCollapse.addEventListener('show.bs.collapse', function () {
                    utilizadoresChevron.classList.remove('fa-chevron-right');
                    utilizadoresChevron.classList.add('fa-chevron-down');
                });
                
                utilizadoresCollapse.addEventListener('hide.bs.collapse', function () {
                    utilizadoresChevron.classList.remove('fa-chevron-down');
                    utilizadoresChevron.classList.add('fa-chevron-right');
                });
            }
            
            // Controlar ícone chevron da secção de encomendas
            const encomendasCollapse = document.getElementById('encomendasCollapse');
            const encomendasChevron = document.getElementById('encomendasChevron');
            
            if (encomendasCollapse && encomendasChevron) {
                encomendasCollapse.addEventListener('show.bs.collapse', function () {
                    encomendasChevron.classList.remove('fa-chevron-right');
                    encomendasChevron.classList.add('fa-chevron-down');
                });
                
                encomendasCollapse.addEventListener('hide.bs.collapse', function () {
                    encomendasChevron.classList.remove('fa-chevron-down');
                    encomendasChevron.classList.add('fa-chevron-right');
                });
            }
            
            // Controlar ícone chevron da secção de mensagens
            const mensagensCollapse = document.getElementById('mensagensCollapse');
            const mensagensChevron = document.getElementById('mensagensChevron');
            
            if (mensagensCollapse && mensagensChevron) {
                mensagensCollapse.addEventListener('show.bs.collapse', function () {
                    mensagensChevron.classList.remove('fa-chevron-right');
                    mensagensChevron.classList.add('fa-chevron-down');
                });
                
                mensagensCollapse.addEventListener('hide.bs.collapse', function () {
                    mensagensChevron.classList.remove('fa-chevron-down');
                    mensagensChevron.classList.add('fa-chevron-right');
                });
            }
        });

        function showReplyForm(messageId, email) {
            const button = event.target;
            const replyForm = document.createElement('div');
            replyForm.classList.add('mt-3', 'p-3', 'border', 'border-danger', 'rounded');
            replyForm.style.background = '#f8f9fa';
            replyForm.innerHTML = `
                <form method="post" action="admin.php">
                    <input type="hidden" name="message_id" value="${messageId}">
                    <input type="hidden" name="email" value="${email}">
                    <div class="mb-3">
                        <label for="reply" class="form-label fw-bold">
                            <i class="fas fa-reply me-1"></i>Responder para: ${email}
                        </label>
                        <textarea name="reply" id="reply" class="form-control" rows="4" placeholder="Escreva a sua resposta aqui..." required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="send_reply" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>Enviar Resposta
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                    </div>
                </form>
            `;
            button.closest('td').appendChild(replyForm);
            button.disabled = true;
        }

        // Session status check (same as main site)
        $(function() {
            $.get('session_status.php', function(data) {
                if (data.temSessao) {
                    let html = `
                        <span class="align-middle me-2" title="${data.email}" style="color:#28a745;">
                            <i class="fas fa-circle" style="font-size:1.1rem;"></i>
                        </span>
                        <span class="fw-bold me-2" style="color:#fff;">${data.nome}</span>
                        <a href="logout.php" title="Sair" class="ms-2"><i class="fas fa-sign-out-alt text-danger" style="font-size:1.3rem;"></i></a>
                    `;
                    $('#user-session-header').prepend(html);
                } else {
                    let html = `<a href="login.php" title="Entrar" class="ms-2"><i class="fas fa-sign-in-alt text-danger" style="font-size:1.3rem;"></i></a>`;
                    $('#user-session-header').prepend(html);
                }
            }, 'json').fail(function() {
                // Fallback if session status fails
                console.log('Session status check failed');
            });
        });
    </script>
</body>
</html>
<?php
// Fecha a conexão no final do script
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
