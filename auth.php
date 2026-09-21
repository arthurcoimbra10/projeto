<?php
// Contas temporárias armazenadas apenas nesta sessão, sem banco.
session_start();
header('Cache-Control: no-store');

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function campo($nome)
{
    return isset($_POST[$nome]) && is_string($_POST[$nome]) ? $_POST[$nome] : '';
}

function escapar($valor)
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function formulario_valido()
{
    return hash_equals($_SESSION['csrf'], campo('csrf'));
}

function redirecionar($pagina)
{
    header('Location: ' . $pagina, true, 303);
    exit;
}
