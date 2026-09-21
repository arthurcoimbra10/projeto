<?php
require_once __DIR__ . '/../auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !formulario_valido()) {
    http_response_code(403);
    exit('Solicitação inválida. Volte à página inicial para sair.');
}
// Preserva as contas temporárias para permitir outro login durante a demonstração.
unset($_SESSION['usuario']);
session_regenerate_id(true);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['aviso'] = 'Você saiu da conta. Pode entrar novamente nesta sessão.';
redirecionar('login.php');
