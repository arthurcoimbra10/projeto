<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'contratante') {
    redirecionar('inicio.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !formulario_valido()) {
    redirecionar('inicio.php');
}

$idServico = trim((string) (campo('id') ?? ''));
$emailUsuario = strtolower(trim((string) ($usuario['email'] ?? '')));
$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$projetos = $_SESSION['projetos'];
$projetosFiltrados = [];
foreach ($projetos as $projeto) {
    $idProjeto = trim((string) ($projeto['id'] ?? ''));
    $emailProprietario = strtolower(trim((string) ($projeto['owner_email'] ?? '')));
    $nomeCliente = strtolower(trim((string) ($projeto['client'] ?? '')));
    $ehProprietario = $emailProprietario === $emailUsuario || (!$emailProprietario && $nomeCliente === strtolower(trim((string) ($usuario['nome'] ?? ''))));
    if ($idProjeto !== $idServico || !$ehProprietario) {
        $projetosFiltrados[] = $projeto;
    }
}
$_SESSION['projetos'] = $projetosFiltrados;
$_SESSION['servico_aviso'] = 'Serviço removido com sucesso.';
redirecionar('inicio.php');
