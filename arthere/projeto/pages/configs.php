<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
	redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
$erro = '';
$aviso = '';
$conta = isset($_SESSION['contas_demo'][$usuario['email']]) ? $_SESSION['contas_demo'][$usuario['email']] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$acao = campo('acao');
	if (!formulario_valido()) {
		$erro = 'Sua sessão expirou. Tente novamente.';
	} elseif ($acao === 'editar_conta') {
		$nome = trim(campo('nome'));
		$email = strtolower(trim(campo('email')));
		$tipoConta = campo('tipo_conta');
		if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$erro = 'Informe um nome e um email válido.';
		} elseif (!in_array($tipoConta, ['contratante', 'agente_criativo'], true)) {
			$erro = 'Selecione um tipo de conta válido.';
		} elseif ($email !== $usuario['email'] && isset($_SESSION['contas_demo'][$email])) {
			$erro = 'Este email já está sendo usado por outra conta.';
		} elseif ($conta) {
			unset($_SESSION['contas_demo'][$usuario['email']]);
			$conta['nome'] = $nome;
			$conta['email'] = $email;
			$conta['tipo_conta'] = $tipoConta;
			$_SESSION['contas_demo'][$email] = $conta;
			$_SESSION['usuario'] = ['nome' => $nome, 'email' => $email, 'tipo_conta' => $tipoConta];
			$usuario = $_SESSION['usuario'];
			$aviso = 'Sua conta foi atualizada.';
		}
	} elseif ($acao === 'apagar_conta') {
				$senhaExclusao = campo('senha_exclusao');
				if (!$conta || !password_verify($senhaExclusao, $conta['senha'])) {
					$erro = 'Senha incorreta. A conta não foi apagada.';
				} else {
					unset($_SESSION['contas_demo'][$usuario['email']]);
					unset($_SESSION['portfolios'][$usuario['email']], $_SESSION['avaliacoes'][$usuario['email']]);
					unset($_SESSION['usuario']);
					$_SESSION['aviso'] = 'Sua conta foi apagada.';
					redirecionar('login.php');
				}
	}
}

$tipoConta = $usuario['tipo_conta'] === 'contratante' ? 'contratante' : 'agente_criativo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configurações | Arthere</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="settings-page">
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
				<a href="configs.php" aria-current="page">Configuração</a>
			</nav>
			<p class="home-account"><?= escapar($usuario['nome']) ?><br><?= $tipoConta === 'contratante' ? 'Contratante' : 'Agente criativo' ?></p>
			<form class="logout-form" action="sair.php" method="post">
				<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
				<button class="submit-button" type="submit">Sair</button>
			</form>
		</aside>
		<section class="settings-content" aria-labelledby="settings-title">
			<div class="settings-stack">
				<header class="settings-heading">
					<p class="profile-eyebrow">Preferências</p>
					<h1 id="settings-title">Configurações</h1>
					<p>Gerencie sua conta e suas preferências no Arthere.</p>
				</header>
				<?php if ($aviso): ?><p class="auth-message" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
				<?php if ($erro): ?><p class="auth-message auth-error" role="alert"><?= escapar($erro) ?></p><?php endif; ?>

				<section class="settings-section" aria-labelledby="edit-title">
					<h2 id="edit-title">Editar conta</h2>
					<form class="settings-form" method="post" action="configs.php">
						<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
						<input type="hidden" name="acao" value="editar_conta">
						<label for="nome">Nome</label>
						<input id="nome" name="nome" type="text" value="<?= escapar($usuario['nome']) ?>" required>
						<label for="email">Email</label>
						<input id="email" name="email" type="email" value="<?= escapar($usuario['email']) ?>" required>
						<label for="tipo-conta">Tipo de conta</label>
						<select id="tipo-conta" name="tipo_conta" required>
							<option value="contratante"<?= $tipoConta === 'contratante' ? ' selected' : '' ?>>Contratante</option>
							<option value="agente_criativo"<?= $tipoConta === 'agente_criativo' ? ' selected' : '' ?>>Agente criativo</option>
						</select>
						<button class="submit-button" type="submit">Salvar alterações</button>
					</form>
				</section>

				<section class="settings-section" aria-labelledby="terms-title">
					<h2 id="terms-title">Acordo de termos e serviço</h2>
					<details class="terms-details">
						<summary>Leia os termos de uso</summary>
						<p>Ao usar o Arthere, você concorda em fornecer informações verdadeiras e respeitar os demais usuários.</p>
						<p>Os projetos, mensagens e avaliações devem ser usados de forma responsável. Esta versão é uma demonstração e armazena os dados apenas durante a sessão.</p>
					</details>
				</section>

				<details class="settings-delete-section">
					<summary class="settings-button danger-button">Apagar conta</summary>
					<form class="delete-account-form" action="configs.php" method="post" onsubmit="return confirm('Tem certeza que deseja apagar sua conta?');">
						<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
						<input type="hidden" name="acao" value="apagar_conta">
						<label for="senha-exclusao">Digite sua senha para confirmar</label>
						<input id="senha-exclusao" name="senha_exclusao" type="password" autocomplete="current-password" required>
						<button class="settings-button danger-button" type="submit">Confirmar exclusão</button>
					</form>
				</details>
			</div>
		</section>
	</main>
</body>
</html>
