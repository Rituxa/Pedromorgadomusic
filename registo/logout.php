<?php
session_start();
// Encerra a sessão do utilizador
session_unset();
session_destroy();

// Mensagem de logout e redirecionamento
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'pt';
$msg = $lang === 'en' ? 'Logout successful! Redirecting...' : 'Sessão terminada com sucesso! A redirecionar...';
echo '<!DOCTYPE html><html lang="' . $lang . '"><head><meta charset="UTF-8"><meta http-equiv="refresh" content="2;url=../index' . ($lang === 'en' ? '' : '-pt') . '.html"><title>Logout</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="bg-light d-flex flex-column min-vh-100"><div class="container d-flex flex-column justify-content-center align-items-center" style="min-height:60vh;"><div class="alert alert-success text-center" style="font-size:1.3rem;max-width:400px;">' . $msg . '</div></div></body></html>';
exit;
