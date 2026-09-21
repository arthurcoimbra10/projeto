<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
$perfilEmail = isset($_GET['usuario']) && is_string($_GET['usuario']) ? strtolower(trim($_GET['usuario'])) : '';
$contasDemo = isset($_SESSION['contas_demo']) && is_array($_SESSION['contas_demo']) ? $_SESSION['contas_demo'] : [];
$perfil = $contasDemo[$perfilEmail] ?? null;
if (!$perfil || $perfilEmail === strtolower($usuario['email'])) {
    redirecionar('perfil.php');
}

$nome = $perfil['nome'] ?? $perfilEmail;
$tipoConta = ($perfil['tipo_conta'] ?? 'agente_criativo') === 'contratante' ? 'Contratante' : 'Agente criativo';
$iniciais = '';
foreach (preg_split('/\s+/', trim($nome)) as $parte) {
    if ($parte !== '') {
        $iniciais .= strtoupper(substr($parte, 0, 1));
    }
}
$iniciais = substr($iniciais, 0, 2);
$portfolio = $_SESSION['portfolios'][$perfilEmail] ?? [];
$avaliacoes = $_SESSION['avaliacoes'][$perfilEmail] ?? [];
$avaliacaoErro = '';
$avaliacaoAviso = '';

function dados_avaliador_publico($nome, $usuario, $contasDemo)
{
    if (($usuario['nome'] ?? '') === $nome) {
        return ['email' => $usuario['email'], 'foto' => $usuario['foto'] ?? ''];
    }
    foreach ($contasDemo as $email => $conta) {
        if (($conta['nome'] ?? '') === $nome) {
            return ['email' => $email, 'foto' => $conta['foto'] ?? ''];
        }
    }
    return ['email' => '', 'foto' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && campo('acao') === 'adicionar_avaliacao') {
    $projetoAvaliado = trim(campo('projeto_avaliado'));
    $comentario = trim(campo('comentario'));
    $nota = (int) campo('nota');
    if (!formulario_valido()) {
        $avaliacaoErro = 'Sua sessão expirou. Tente novamente.';
    } elseif ($projetoAvaliado === '' || $comentario === '') {
        $avaliacaoErro = 'Preencha todos os campos da avaliação.';
    } elseif ($nota < 1 || $nota > 5) {
        $avaliacaoErro = 'Escolha uma nota entre 1 e 5.';
    } else {
        $avaliacoes[] = [
            'avaliador' => $usuario['nome'],
            'projeto' => $projetoAvaliado,
            'comentario' => $comentario,
            'nota' => $nota,
        ];
        $_SESSION['avaliacoes'][$perfilEmail] = $avaliacoes;
        $avaliacaoAviso = 'Avaliação publicada no perfil.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= escapar($nome) ?> | Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="profile-page public-profile-page">
    <header class="home-header">
        <a class="home-brand" href="inicio.php" aria-label="Arthere - início">Arthere</a>
        <div></div>
    </header>
    <main class="profile-layout">
        <aside class="home-sidebar">
            <nav class="home-nav" aria-label="Menu principal">
                <a href="inicio.php">Início</a>
                <a href="perfil.php">Perfil</a>
                <a href="chat.php">Chat</a>
                <?php if (($usuario['tipo_conta'] ?? '') === 'contratante'): ?><a href="candidatos.php">Candidatos</a><?php endif; ?>
                <?php if (($usuario['tipo_conta'] ?? '') !== 'contratante'): ?><a href="compromissos.php">Meus compromissos</a><?php endif; ?>
                <a href="configs.php">Configuração</a>
            </nav>
            <p class="home-account"><?= escapar($usuario['nome']) ?><br><?= $usuario['tipo_conta'] === 'contratante' ? 'Contratante' : 'Agente criativo' ?></p>
            <form class="logout-form" action="sair.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <button class="submit-button" type="submit">Sair</button>
            </form>
        </aside>
        <section class="profile-content" aria-labelledby="profile-title">
            <div class="profile-stack">
                <div class="profile-card">
                    <p class="profile-eyebrow">Perfil público</p>
                    <h1 id="profile-title">Perfil</h1>
                    <div class="profile-avatar" aria-label="Foto de <?= escapar($nome) ?>">
                        <?php if (!empty($perfil['foto'])): ?><img src="<?= escapar($perfil['foto']) ?>" alt="Foto de <?= escapar($nome) ?>">
                        <?php else: ?><span><?= escapar($iniciais) ?></span><?php endif; ?>
                    </div>
                    <h2><?= escapar($nome) ?></h2>
                    <p class="profile-type"><?= escapar($tipoConta) ?></p>
                    <div class="profile-details">
                        <span>Email</span>
                        <strong><?= escapar($perfilEmail) ?></strong>
                    </div>
                    <form action="chat.php" method="get">
                        <input type="hidden" name="contato" value="<?= escapar($perfilEmail) ?>">
                        <button class="submit-button" type="submit">Conversar</button>
                    </form>
                </div>

                <section class="portfolio-section" aria-labelledby="portfolio-title">
                    <div class="portfolio-heading"><p class="profile-eyebrow">Trabalhos realizados</p><h2 id="portfolio-title">Portfólio</h2></div>
                    <div class="portfolio-list">
                        <?php if (!$portfolio): ?><p class="candidates-none">Este usuário ainda não adicionou trabalhos ao portfólio.</p><?php endif; ?>
                        <?php foreach ($portfolio as $projeto): ?><article class="portfolio-item"><h3><?= escapar($projeto['titulo']) ?></h3><p><?= nl2br(escapar($projeto['descricao'])) ?></p><?php if (!empty($projeto['link'])): ?><a href="<?= escapar($projeto['link']) ?>" target="_blank" rel="noopener noreferrer">Ver projeto</a><?php endif; ?></article><?php endforeach; ?>
                    </div>
                </section>

                <section class="reviews-section" aria-labelledby="reviews-title">
                    <p class="profile-eyebrow">Opinião da comunidade</p>
                    <h2 id="reviews-title">Avaliações</h2>
                    <?php if ($avaliacaoAviso): ?><p class="auth-message" role="status"><?= escapar($avaliacaoAviso) ?></p><?php endif; ?>
                    <?php if ($avaliacaoErro): ?><p class="auth-message auth-error" role="alert"><?= escapar($avaliacaoErro) ?></p><?php endif; ?>
                    <form class="portfolio-form" action="perfil-publico.php?usuario=<?= urlencode($perfilEmail) ?>" method="post">
                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                        <input type="hidden" name="acao" value="adicionar_avaliacao">
                        <label for="projeto-avaliado">Projeto avaliado</label>
                        <input id="projeto-avaliado" name="projeto_avaliado" type="text" placeholder="Ex.: Identidade visual" required>
                        <label for="nota">Nota</label>
                        <select id="nota" name="nota" required><option value="">Selecione uma nota</option><option value="5">5 - Excelente</option><option value="4">4 - Muito bom</option><option value="3">3 - Bom</option><option value="2">2 - Regular</option><option value="1">1 - Precisa melhorar</option></select>
                        <label for="comentario">Comentário</label>
                        <textarea id="comentario" name="comentario" rows="4" placeholder="Compartilhe sua experiência" required></textarea>
                        <button class="submit-button" type="submit">Publicar avaliação</button>
                    </form>
                    <div class="reviews-list">
                        <?php foreach ($avaliacoes as $avaliacao): ?><?php $dadosAvaliador = dados_avaliador_publico($avaliacao['avaliador'], $usuario, $contasDemo); ?><article class="review-item"><div class="review-heading"><h3 class="person-inline"><?php if ($dadosAvaliador['email']): ?><a class="chat-avatar profile-avatar-link person-avatar-small" href="perfil-publico.php?usuario=<?= urlencode($dadosAvaliador['email']) ?>" aria-label="Ver perfil de <?= escapar($avaliacao['avaliador']) ?>"><?php if ($dadosAvaliador['foto']): ?><img src="<?= escapar($dadosAvaliador['foto']) ?>" alt=""><?php else: ?><?= escapar(substr(strtoupper($avaliacao['avaliador']), 0, 2)) ?><?php endif; ?></a><?php endif; ?><span><?= escapar($avaliacao['avaliador']) ?></span></h3><span><?= escapar($avaliacao['nota']) ?>/5</span></div><p class="review-project"><?= escapar($avaliacao['projeto']) ?></p><p><?= nl2br(escapar($avaliacao['comentario'])) ?></p></article><?php endforeach; ?>
                    </div>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
