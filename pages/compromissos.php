<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'agente_criativo') {
    redirecionar('inicio.php');
}

$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$_SESSION['candidaturas'] = isset($_SESSION['candidaturas']) && is_array($_SESSION['candidaturas']) ? $_SESSION['candidaturas'] : [];
$_SESSION['decisoes_candidaturas'] = isset($_SESSION['decisoes_candidaturas']) && is_array($_SESSION['decisoes_candidaturas']) ? $_SESSION['decisoes_candidaturas'] : [];
$emailUsuario = strtolower(trim((string) ($usuario['email'] ?? '')));
$compromissosPendentes = [];
$compromissosAceitos = [];

foreach ($_SESSION['projetos'] as $projeto) {
    $idProjeto = trim((string) ($projeto['id'] ?? ''));
    $candidatos = $_SESSION['candidaturas'][$idProjeto] ?? [];
    $emailCandidato = null;
    foreach ($candidatos as $email) {
        if (strtolower(trim((string) $email)) === $emailUsuario) {
            $emailCandidato = $email;
            break;
        }
    }

    if ($emailCandidato === null) {
        continue;
    }

    $decisao = $_SESSION['decisoes_candidaturas'][$idProjeto][$emailCandidato] ?? 'pendente';
    if ($decisao === 'aceita') {
        $compromissosAceitos[] = $projeto;
    } elseif ($decisao === 'pendente') {
        $compromissosPendentes[] = $projeto;
    }
}

function nome_contratante_compromisso($projeto)
{
    return $projeto['client'] ?? 'Contratante';
}

function foto_contratante_compromisso($projeto)
{
    $email = $projeto['owner_email'] ?? '';
    $contasDemo = $_SESSION['contas_demo'] ?? [];
    return $contasDemo[$email]['foto'] ?? '';
}

function renderizar_compromissos($projetos, $status)
{
    if (!$projetos) {
        echo '<p class="commitments-none">Nenhum projeto nesta categoria.</p>';
        return;
    }

    echo '<div class="commitments-list">';
    foreach ($projetos as $projeto) {
        $idProjeto = trim((string) ($projeto['id'] ?? ''));
        $ownerEmail = (string) ($projeto['owner_email'] ?? '');
        $ownerPhoto = foto_contratante_compromisso($projeto);
        echo '<article class="commitment-item">';
        echo '<div class="commitment-heading">';
        echo '<div><p class="profile-eyebrow">' . escapar($projeto['category'] ?? 'Projeto') . '</p>';
        echo '<h3>' . escapar($projeto['title'] ?? 'Projeto sem título') . '</h3></div>';
        echo '<span class="commitment-status commitment-status-' . escapar($status) . '">' . ($status === 'aceita' ? 'Aceita' : 'Pendente') . '</span>';
        echo '</div>';
        echo '<dl class="commitment-facts">';
        echo '<dt>Contratante</dt><dd class="person-inline">';
        if ($ownerEmail !== '') {
            echo '<a class="chat-avatar profile-avatar-link person-avatar-small" href="perfil-publico.php?usuario=' . urlencode($ownerEmail) . '" aria-label="Ver perfil de ' . escapar(nome_contratante_compromisso($projeto)) . '">';
            echo $ownerPhoto ? '<img src="' . escapar($ownerPhoto) . '" alt="">' : escapar(substr(strtoupper(nome_contratante_compromisso($projeto)), 0, 2));
            echo '</a>';
        }
        echo '<span>' . escapar(nome_contratante_compromisso($projeto)) . '</span></dd>';
        echo '<dt>Local</dt><dd>' . escapar($projeto['location'] ?? '') . '</dd>';
        echo '<dt>Horário</dt><dd>' . escapar($projeto['deadline'] ?? '') . '</dd>';
        echo '</dl>';
        echo '<p class="commitment-description">' . escapar($projeto['description'] ?? '') . '</p>';
        if ($ownerEmail !== '') {
            echo '<form action="chat.php" method="get">';
            echo '<input type="hidden" name="contato" value="' . escapar($ownerEmail) . '">';
            echo '<button class="settings-button" type="submit">Conversar com contratante</button>';
            echo '</form>';
        }
        echo '</article>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus compromissos | Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="settings-page commitments-page">
    <header class="home-header">
        <a class="home-brand" href="inicio.php" aria-label="Arthere - início">Arthere</a>
        <div></div>
    </header>
    <main class="settings-layout">
        <aside class="home-sidebar">
            <nav class="home-nav" aria-label="Menu principal">
                <a href="inicio.php">Início</a>
                <a href="perfil.php">Perfil</a>
                <a href="chat.php">Chat</a>
                <a href="compromissos.php" aria-current="page">Meus compromissos</a>
                <a href="configs.php">Configuração</a>
            </nav>
            <p class="home-account"><?= escapar($usuario['nome']) ?><br>Agente criativo</p>
            <form class="logout-form" action="sair.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <button class="submit-button" type="submit">Sair</button>
            </form>
        </aside>
        <section class="settings-content" aria-labelledby="commitments-title">
            <div class="settings-stack commitments-stack">
                <header class="settings-heading">
                    <p class="profile-eyebrow">Suas candidaturas</p>
                    <h1 id="commitments-title">Meus compromissos</h1>
                    <p>Acompanhe os projetos em que você se candidatou.</p>
                </header>

                <section class="settings-section commitment-section" aria-labelledby="pending-title">
                    <div class="commitment-section-heading">
                        <h2 id="pending-title">Candidaturas pendentes</h2>
                        <span><?= count($compromissosPendentes) ?></span>
                    </div>
                    <?php renderizar_compromissos($compromissosPendentes, 'pendente'); ?>
                </section>

                <section class="settings-section commitment-section" aria-labelledby="accepted-title">
                    <div class="commitment-section-heading">
                        <h2 id="accepted-title">Candidaturas aceitas</h2>
                        <span><?= count($compromissosAceitos) ?></span>
                    </div>
                    <?php renderizar_compromissos($compromissosAceitos, 'aceita'); ?>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
