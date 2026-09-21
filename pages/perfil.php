<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
	redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
$nome = $usuario['nome'];
$iniciais = '';
foreach (preg_split('/\s+/', trim($nome)) as $parte) {
	if ($parte !== '') {
		$iniciais .= strtoupper(substr($parte, 0, 1));
	}
}
$iniciais = substr($iniciais, 0, 2);
$tipoConta = $usuario['tipo_conta'] === 'contratante' ? 'Contratante' : 'Agente criativo';
$portfolioErro = '';
$portfolioAviso = '';
$portfolio = $_SESSION['portfolios'][$usuario['email']] ?? [];
$avaliacaoErro = '';
$avaliacaoAviso = '';
$avaliacoes = $_SESSION['avaliacoes'][$usuario['email']] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && campo('acao') === 'adicionar_portfolio') {
	$tituloProjeto = trim(campo('titulo_projeto'));
	$descricaoProjeto = trim(campo('descricao_projeto'));
	$linkProjeto = trim(campo('link_projeto'));
	if (!formulario_valido()) {
		$portfolioErro = 'Sua sessão expirou. Tente novamente.';
	} elseif ($tituloProjeto === '' || $descricaoProjeto === '') {
		$portfolioErro = 'Informe o nome e a descrição do projeto.';
	} elseif ($linkProjeto !== '' && !filter_var($linkProjeto, FILTER_VALIDATE_URL)) {
		$portfolioErro = 'Informe um link válido ou deixe o campo em branco.';
	} else {
		$portfolio[] = [
			'titulo' => $tituloProjeto,
			'descricao' => $descricaoProjeto,
			'link' => $linkProjeto,
		];
		$_SESSION['portfolios'][$usuario['email']] = $portfolio;
		$portfolioAviso = 'Projeto adicionado ao seu portfólio.';
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && campo('acao') === 'adicionar_avaliacao') {
	$avaliador = trim(campo('avaliador'));
	$projetoAvaliado = trim(campo('projeto_avaliado'));
	$comentario = trim(campo('comentario'));
	$nota = (int) campo('nota');
	if (!formulario_valido()) {
		$avaliacaoErro = 'Sua sessão expirou. Tente novamente.';
	} elseif ($avaliador === '' || $projetoAvaliado === '' || $comentario === '') {
		$avaliacaoErro = 'Preencha todos os campos da avaliação.';
	} elseif ($nota < 1 || $nota > 5) {
		$avaliacaoErro = 'Escolha uma nota entre 1 e 5.';
	} else {
		$avaliacoes[] = [
			'avaliador' => $avaliador,
			'projeto' => $projetoAvaliado,
			'comentario' => $comentario,
			'nota' => $nota,
		];
		$_SESSION['avaliacoes'][$usuario['email']] = $avaliacoes;
		$avaliacaoAviso = 'Avaliação adicionada ao seu perfil.';
	}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Perfil | Arthere</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="profile-page">
	<header class="home-header">
		<a class="home-brand" href="inicio.php" aria-label="Arthere - início">Arthere</a>
		<div></div>
	</header>
	<main class="profile-layout">
		<aside class="home-sidebar">
			<nav class="home-nav" aria-label="Menu principal">
				<a href="inicio.php">Início</a>
				<a href="perfil.php" aria-current="page">Perfil</a>
				<a href="chat.php">Chat</a>
				<a href="configs.php">Configuração</a>
			</nav>
			<p class="home-account"><?= escapar($nome) ?><br><?= escapar($tipoConta) ?></p>
			<form class="logout-form" action="sair.php" method="post">
				<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
				<button class="submit-button" type="submit">Sair</button>
			</form>
		</aside>
		<section class="profile-content" aria-labelledby="profile-title">
			<div class="profile-stack">
			<div class="profile-card">
				<p class="profile-eyebrow">Minha conta</p>
				<h1 id="profile-title">Perfil</h1>
				<div class="profile-avatar" aria-label="Foto de <?= escapar($nome) ?>">
					<?php if (!empty($usuario['foto'])): ?>
						<img src="<?= escapar($usuario['foto']) ?>" alt="Foto de <?= escapar($nome) ?>">
					<?php else: ?>
						<span><?= escapar($iniciais) ?></span>
					<?php endif; ?>
				</div>
				<h2><?= escapar($nome) ?></h2>
				<p class="profile-type"><?= escapar($tipoConta) ?></p>
				<div class="profile-details">
					<span>Email</span>
					<strong><?= escapar($usuario['email']) ?></strong>
				</div>
			</div>

			<section class="portfolio-section" aria-labelledby="portfolio-title">
				<div class="portfolio-heading">
					<div>
						<p class="profile-eyebrow">Trabalhos realizados</p>
						<h2 id="portfolio-title">Meu portfólio</h2>
					</div>
				</div>
				<?php if ($portfolioAviso): ?><p class="auth-message" role="status"><?= escapar($portfolioAviso) ?></p><?php endif; ?>
				<?php if ($portfolioErro): ?><p class="auth-message auth-error" role="alert"><?= escapar($portfolioErro) ?></p><?php endif; ?>
				<form class="portfolio-form" action="perfil.php" method="post">
					<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
					<input type="hidden" name="acao" value="adicionar_portfolio">
					<label for="titulo-projeto">Nome do projeto</label>
					<input id="titulo-projeto" name="titulo_projeto" type="text" placeholder="Ex.: Identidade visual" required>
					<label for="descricao-projeto">Descrição</label>
					<textarea id="descricao-projeto" name="descricao_projeto" rows="4" placeholder="Conte brevemente sobre seu trabalho" required></textarea>
					<label for="link-projeto">Link do projeto <span>(opcional)</span></label>
					<input id="link-projeto" name="link_projeto" type="url" placeholder="https://seu-projeto.com">
					<button class="submit-button" type="submit">Adicionar ao portfólio</button>
				</form>
				<div class="portfolio-list">
					<?php foreach ($portfolio as $projeto): ?>
						<article class="portfolio-item">
							<h3><?= escapar($projeto['titulo']) ?></h3>
							<p><?= nl2br(escapar($projeto['descricao'])) ?></p>
							<?php if ($projeto['link']): ?><a href="<?= escapar($projeto['link']) ?>" target="_blank" rel="noopener noreferrer">Ver projeto</a><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="reviews-section" aria-labelledby="reviews-title">
				<p class="profile-eyebrow">Opinião da comunidade</p>
				<h2 id="reviews-title">Avaliações</h2>
				<?php if ($avaliacaoAviso): ?><p class="auth-message" role="status"><?= escapar($avaliacaoAviso) ?></p><?php endif; ?>
				<?php if ($avaliacaoErro): ?><p class="auth-message auth-error" role="alert"><?= escapar($avaliacaoErro) ?></p><?php endif; ?>
				<form class="portfolio-form" action="perfil.php" method="post">
					<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
					<input type="hidden" name="acao" value="adicionar_avaliacao">
					<label for="avaliador">Nome de quem avaliou</label>
					<input id="avaliador" name="avaliador" type="text" placeholder="Ex.: Maria Silva" required>
					<label for="projeto-avaliado">Projeto avaliado</label>
					<input id="projeto-avaliado" name="projeto_avaliado" type="text" placeholder="Ex.: Identidade visual" required>
					<label for="nota">Nota</label>
					<select id="nota" name="nota" required>
						<option value="">Selecione uma nota</option>
						<option value="5">5 - Excelente</option>
						<option value="4">4 - Muito bom</option>
						<option value="3">3 - Bom</option>
						<option value="2">2 - Regular</option>
						<option value="1">1 - Precisa melhorar</option>
					</select>
					<label for="comentario">Comentário</label>
					<textarea id="comentario" name="comentario" rows="4" placeholder="Compartilhe sua experiência" required></textarea>
					<button class="submit-button" type="submit">Publicar avaliação</button>
				</form>
				<div class="reviews-list">
					<?php foreach ($avaliacoes as $avaliacao): ?>
						<article class="review-item">
							<div class="review-heading">
								<h3><?= escapar($avaliacao['avaliador']) ?></h3>
								<span><?= escapar($avaliacao['nota']) ?>/5</span>
							</div>
							<p class="review-project"><?= escapar($avaliacao['projeto']) ?></p>
							<p><?= nl2br(escapar($avaliacao['comentario'])) ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
			</div>
		</section>
	</main>
</body>
</html>
