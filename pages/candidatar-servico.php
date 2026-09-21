<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'agente_criativo') {
    redirecionar('inicio.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !formulario_valido()) {
    redirecionar('inicio.php');
}

$idServico = trim(campo('id'));
$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$servico = null;
foreach ($_SESSION['projetos'] as $projeto) {
    if (trim((string) ($projeto['id'] ?? '')) === $idServico) {
        $servico = $projeto;
        break;
    }
}

if (!$servico || empty($servico['owner_email']) || $servico['owner_email'] === $usuario['email']) {
    $_SESSION['servico_aviso'] = 'Não foi possível realizar essa candidatura.';
    redirecionar('inicio.php');
}

$_SESSION['candidaturas'] = isset($_SESSION['candidaturas']) && is_array($_SESSION['candidaturas']) ? $_SESSION['candidaturas'] : [];
$_SESSION['candidaturas'][$idServico] = isset($_SESSION['candidaturas'][$idServico]) && is_array($_SESSION['candidaturas'][$idServico])
    ? $_SESSION['candidaturas'][$idServico]
    : [];

if (!in_array($usuario['email'], $_SESSION['candidaturas'][$idServico], true)) {
    $_SESSION['candidaturas'][$idServico][] = $usuario['email'];
    $_SESSION['servico_aviso'] = 'Sua candidatura foi enviada ao contratante.';
} else {
    $_SESSION['servico_aviso'] = 'Você já se candidatou a esta vaga.';
}

redirecionar('inicio.php');
