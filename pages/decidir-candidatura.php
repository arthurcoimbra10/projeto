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
    redirecionar('candidatos.php');
}

$idServico = trim(campo('id'));
$emailCandidato = strtolower(trim(campo('candidato')));
$decisao = campo('decisao');
if (!in_array($decisao, ['aceita', 'rejeitada'], true)) {
    redirecionar('candidatos.php');
}

$emailUsuario = strtolower(trim((string) ($usuario['email'] ?? '')));
$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$projetoEncontrado = false;
foreach ($_SESSION['projetos'] as $projeto) {
    $idProjeto = trim((string) ($projeto['id'] ?? ''));
    $emailProprietario = strtolower(trim((string) ($projeto['owner_email'] ?? '')));
    if ($idProjeto === $idServico && $emailProprietario === $emailUsuario) {
        $projetoEncontrado = true;
        break;
    }
}

$_SESSION['candidaturas'] = isset($_SESSION['candidaturas']) && is_array($_SESSION['candidaturas']) ? $_SESSION['candidaturas'] : [];
$candidatosDoProjeto = $_SESSION['candidaturas'][$idServico] ?? [];
$emailCandidatoReal = null;
foreach ($candidatosDoProjeto as $email) {
    if (strtolower(trim((string) $email)) === $emailCandidato) {
        $emailCandidatoReal = $email;
        break;
    }
}

if (!$projetoEncontrado || $emailCandidatoReal === null) {
    $_SESSION['candidatos_aviso'] = 'Não foi possível atualizar essa candidatura.';
    redirecionar('candidatos.php');
}

$_SESSION['decisoes_candidaturas'] = isset($_SESSION['decisoes_candidaturas']) && is_array($_SESSION['decisoes_candidaturas'])
    ? $_SESSION['decisoes_candidaturas']
    : [];
$_SESSION['decisoes_candidaturas'][$idServico] = isset($_SESSION['decisoes_candidaturas'][$idServico]) && is_array($_SESSION['decisoes_candidaturas'][$idServico])
    ? $_SESSION['decisoes_candidaturas'][$idServico]
    : [];
$_SESSION['decisoes_candidaturas'][$idServico][$emailCandidatoReal] = $decisao;
$_SESSION['candidatos_aviso'] = $decisao === 'aceita'
    ? 'Candidato aceito com sucesso.'
    : 'Candidato rejeitado.';

redirecionar('candidatos.php');
