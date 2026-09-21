<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
	redirecionar('login.php');
}

function iniciais_chat($nome)
{
	$iniciais = '';
	foreach (preg_split('/\s+/', trim($nome)) as $parte) {
		if ($parte !== '') {
			$iniciais .= strtoupper(substr($parte, 0, 1));
		}
	}
	return substr($iniciais, 0, 2);
}

$usuario = $_SESSION['usuario'];
$contatos = [];
$contasDemo = isset($_SESSION['contas_demo']) && is_array($_SESSION['contas_demo']) ? $_SESSION['contas_demo'] : [];
foreach ($contasDemo as $conta) {
	if (is_array($conta) && isset($conta['email'], $conta['nome']) && $conta['email'] !== $usuario['email']) {
		$contatos[] = $conta;
	}
}
$contatoEmail = isset($_GET['contato']) && is_string($_GET['contato']) ? $_GET['contato'] : '';
$contatoSelecionado = null;
foreach ($contatos as $contato) {
	if (isset($contato['email']) && $contato['email'] === $contatoEmail) {
		$contatoSelecionado = $contato;
		break;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && formulario_valido() && $contatoSelecionado && campo('mensagem') !== '') {
	$chaveConversa = $usuario['email'] . '|' . $contatoSelecionado['email'];
	$_SESSION['mensagens'][$chaveConversa][] = [
		'autor' => $usuario['email'],
		'texto' => trim(campo('mensagem')),
	];
	header('Location: chat.php?contato=' . urlencode($contatoSelecionado['email']));
	exit;
}

$mensagens = [];
if ($contatoSelecionado) {
	$chaves = [$usuario['email'] . '|' . $contatoSelecionado['email'], $contatoSelecionado['email'] . '|' . $usuario['email']];
	foreach ($chaves as $chave) {
		$mensagensSalvas = isset($_SESSION['mensagens'][$chave]) && is_array($_SESSION['mensagens'][$chave]) ? $_SESSION['mensagens'][$chave] : [];
		$mensagens = array_merge($mensagens, $mensagensSalvas);
	}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Chat | Arthere</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="chat-page">
	<header class="home-header">
		<a class="home-brand" href="inicio.php" aria-label="Arthere - início">Arthere</a>
		<div></div>
	</header>
	<main class="chat-layout<?= !$contatos ? ' no-conversations' : '' ?>">
		<aside class="home-sidebar">
			<nav class="home-nav" aria-label="Menu principal">
				<a href="inicio.php">Início</a>
				<a href="perfil.php">Perfil</a>
				<a href="chat.php" aria-current="page">Chat</a>
				<a href="configs.php">Configuração</a>
			</nav>
			<p class="home-account"><?= escapar($usuario['nome']) ?><br><?= $usuario['tipo_conta'] === 'contratante' ? 'Contratante' : 'Agente criativo' ?></p>
			<form class="logout-form" action="sair.php" method="post">
				<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
				<button class="submit-button" type="submit">Sair</button>
			</form>
		</aside>
		<?php if ($contatos): ?>
		<section class="chat-contacts" aria-labelledby="contacts-title">
			<h1 id="contacts-title">Conversas</h1>
			<div class="contact-list">
				<?php foreach ($contatos as $contato): ?>
					<?php $classeContato = $contato['email'] === $contatoEmail ? 'contact-item is-selected' : 'contact-item'; ?>
					<a class="<?= $classeContato ?>" href="chat.php?contato=<?= urlencode($contato['email']) ?>" aria-label="Abrir conversa com <?= escapar($contato['nome']) ?>">
						<span class="chat-avatar">
							<?php if (!empty($contato['foto'])): ?><img src="<?= escapar($contato['foto']) ?>" alt="">
							<?php else: ?><?= escapar(iniciais_chat($contato['nome'])) ?><?php endif; ?>
						</span>
						<strong><?= escapar($contato['nome']) ?></strong>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>
		<section class="conversation" aria-live="polite">
			<?php if (!$contatos): ?>
				<div class="conversation-placeholder"><p>Você não possui nenhuma conversa</p></div>
			<?php elseif ($contatoSelecionado): ?>
				<header class="conversation-header">
					<span class="chat-avatar"><?php if (!empty($contatoSelecionado['foto'])): ?><img src="<?= escapar($contatoSelecionado['foto']) ?>" alt=""> <?php else: ?><?= escapar(iniciais_chat($contatoSelecionado['nome'])) ?><?php endif; ?></span>
					<h2><?= escapar($contatoSelecionado['nome']) ?></h2>
				</header>
				<div class="message-list">
					<?php foreach ($mensagens as $mensagem): ?>
						<p class="message<?= $mensagem['autor'] === $usuario['email'] ? ' message-own' : '' ?>"><?= nl2br(escapar($mensagem['texto'])) ?></p>
					<?php endforeach; ?>
					<?php if (!$mensagens): ?><p class="chat-empty">Ainda não há mensagens nesta conversa.</p><?php endif; ?>
				</div>
				<form class="message-form" method="post" action="chat.php?contato=<?= urlencode($contatoSelecionado['email']) ?>">
					<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
					<label class="visually-hidden" for="mensagem">Mensagem</label>
					<input id="mensagem" name="mensagem" type="text" placeholder="Escreva uma mensagem" autocomplete="off" required>
					<button class="submit-button" type="submit">Enviar</button>
				</form>
			<?php else: ?>
				<div class="conversation-placeholder"><h2>Selecione uma conversa</h2><p>Escolha um usuário para ver as mensagens.</p></div>
			<?php endif; ?>
		</section>
	</main>
</body>
</html>
