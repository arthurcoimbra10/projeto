<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'contratante') {
    redirecionar('inicio.php');
}

$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$_SESSION['candidaturas'] = isset($_SESSION['candidaturas']) && is_array($_SESSION['candidaturas']) ? $_SESSION['candidaturas'] : [];
$_SESSION['decisoes_candidaturas'] = isset($_SESSION['decisoes_candidaturas']) && is_array($_SESSION['decisoes_candidaturas']) ? $_SESSION['decisoes_candidaturas'] : [];
$projetosDoUsuario = [];
$emailUsuario = strtolower(trim((string) ($usuario['email'] ?? '')));

foreach ($_SESSION['projetos'] as $projeto) {
    $emailProprietario = strtolower(trim((string) ($projeto['owner_email'] ?? '')));
    if ($emailProprietario === $emailUsuario) {
        $projetosDoUsuario[] = $projeto;
    }
}

$contasDemo = isset($_SESSION['contas_demo']) && is_array($_SESSION['contas_demo']) ? $_SESSION['contas_demo'] : [];
$candidatosAviso = $_SESSION['candidatos_aviso'] ?? '';
unset($_SESSION['candidatos_aviso']);
function nome_candidato($email, $contasDemo)
{
    return isset($contasDemo[$email]['nome']) ? $contasDemo[$email]['nome'] : $email;
}

function foto_candidato($email, $contasDemo)
{
    return $contasDemo[$email]['foto'] ?? '';
}

function iniciais_candidato($nome)
{
    $iniciais = '';
    foreach (preg_split('/\s+/', trim($nome)) as $parte) {
        if ($parte !== '') {
            $iniciais .= strtoupper(substr($parte, 0, 1));
        }
    }
    return substr($iniciais, 0, 2);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos | Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="settings-page candidates-page">
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
                <a href="candidatos.php" aria-current="page">Candidatos</a>
                <a href="configs.php">Configuração</a>
            </nav>
            <p class="home-account"><?= escapar($usuario['nome']) ?><br>Contratante</p>
            <form class="logout-form" action="sair.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <button class="submit-button" type="submit">Sair</button>
            </form>
        </aside>
        <section class="settings-content" aria-labelledby="candidates-title">
            <div class="settings-stack candidates-stack">
                <header class="settings-heading">
                    <p class="profile-eyebrow">Suas oportunidades</p>
                    <h1 id="candidates-title">Candidatos</h1>
                    <p>Veja os agentes interessados em cada projeto e entre em contato.</p>
                </header>
                <?php if ($candidatosAviso): ?><p class="auth-message" role="status"><?= escapar($candidatosAviso) ?></p><?php endif; ?>

                <?php if (!$projetosDoUsuario): ?>
                    <section class="settings-section candidates-empty">
                        <h2>Você ainda não publicou projetos</h2>
                        <p>Adicione um serviço no mapa para começar a receber candidaturas.</p>
                        <form action="novo-servico.php" method="get">
                            <button class="submit-button" type="submit">Adicionar serviço</button>
                        </form>
                    </section>
                <?php else: ?>
                    <?php foreach ($projetosDoUsuario as $projeto): ?>
                        <?php
                        $idProjeto = trim((string) ($projeto['id'] ?? ''));
                        $emailsCandidatosTodos = isset($_SESSION['candidaturas'][$idProjeto]) && is_array($_SESSION['candidaturas'][$idProjeto])
                            ? $_SESSION['candidaturas'][$idProjeto]
                            : [];
                        $emailsCandidatos = array_values(array_filter($emailsCandidatosTodos, static function ($email) use ($idProjeto) {
                            return ($_SESSION['decisoes_candidaturas'][$idProjeto][$email] ?? 'pendente') !== 'rejeitada';
                        }));
                        ?>
                        <section class="settings-section candidate-project" aria-labelledby="project-<?= escapar($idProjeto) ?>">
                            <div class="candidate-project-heading">
                                <div>
                                    <p class="profile-eyebrow"><?= escapar($projeto['category'] ?? 'Projeto') ?></p>
                                    <h2 id="project-<?= escapar($idProjeto) ?>"><?= escapar($projeto['title'] ?? 'Projeto sem título') ?></h2>
                                    <p><?= escapar($projeto['location'] ?? '') ?></p>
                                </div>
                                <span class="candidate-count"><?= count($emailsCandidatos) ?> <?= count($emailsCandidatos) === 1 ? 'candidato' : 'candidatos' ?></span>
                            </div>

                            <?php if (!$emailsCandidatos): ?>
                                <p class="candidates-none">Ainda não há candidaturas para este projeto.</p>
                            <?php else: ?>
                                <div class="candidate-list">
                                    <?php foreach ($emailsCandidatos as $emailCandidato): ?>
                                        <?php $nomeCandidato = nome_candidato($emailCandidato, $contasDemo); ?>
                                        <?php $fotoCandidato = foto_candidato($emailCandidato, $contasDemo); ?>
                                        <?php $decisao = $_SESSION['decisoes_candidaturas'][$idProjeto][$emailCandidato] ?? 'pendente'; ?>
                                        <article class="candidate-item">
                                            <a class="chat-avatar profile-avatar-link" href="perfil-publico.php?usuario=<?= urlencode($emailCandidato) ?>" aria-label="Ver perfil de <?= escapar($nomeCandidato) ?>"><?php if ($fotoCandidato): ?><img src="<?= escapar($fotoCandidato) ?>" alt="Foto de <?= escapar($nomeCandidato) ?>"><?php else: ?><?= escapar(iniciais_candidato($nomeCandidato)) ?><?php endif; ?></a>
                                            <div class="candidate-info">
                                                <h3><?= escapar($nomeCandidato) ?></h3>
                                                <p><?= escapar($emailCandidato) ?></p>
                                                <span class="candidate-status candidate-status-<?= escapar($decisao) ?>">
                                                    <?= $decisao === 'aceita' ? 'Candidatura aceita' : ($decisao === 'rejeitada' ? 'Candidatura rejeitada' : 'Aguardando decisão') ?>
                                                </span>
                                            </div>
                                            <div class="candidate-actions">
                                                <?php if ($decisao === 'pendente'): ?>
                                                    <form action="decidir-candidatura.php" method="post">
                                                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                                                        <input type="hidden" name="id" value="<?= escapar($idProjeto) ?>">
                                                        <input type="hidden" name="candidato" value="<?= escapar($emailCandidato) ?>">
                                                        <input type="hidden" name="decisao" value="aceita">
                                                        <button class="settings-button candidate-accept-button" type="submit">Aceitar</button>
                                                    </form>
                                                    <form action="decidir-candidatura.php" method="post">
                                                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                                                        <input type="hidden" name="id" value="<?= escapar($idProjeto) ?>">
                                                        <input type="hidden" name="candidato" value="<?= escapar($emailCandidato) ?>">
                                                        <input type="hidden" name="decisao" value="rejeitada">
                                                        <button class="settings-button danger-button" type="submit">Rejeitar</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form action="chat.php" method="get">
                                                    <input type="hidden" name="contato" value="<?= escapar($emailCandidato) ?>">
                                                    <button class="settings-button" type="submit">Conversar</button>
                                                </form>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
