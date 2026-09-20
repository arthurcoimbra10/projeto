<?php
require_once __DIR__ . '/../auth.php';
if (isset($_SESSION['usuario'])) {
    redirecionar('inicio.php');
}
$erro = '';
$nome = trim(campo('nome'));
$email = strtolower(trim(campo('email')));
$tipo = campo('tipo_conta');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = campo('senha');
    if (!formulario_valido()) {
        $erro = 'Sua sessão expirou. Tente novamente.';
    } elseif ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe seu nome e um email válido.';
    } elseif (strlen($senha) < 6 || strlen($senha) > 72 || strpos($senha, "\0") !== false) {
        $erro = 'Use uma senha entre 6 e 72 bytes (letras acentuadas podem ocupar mais de um byte).';
    } elseif (!in_array($tipo, ['contratante', 'agente_criativo'], true)) {
        $erro = 'Selecione o tipo de conta.';
    } elseif (isset($_SESSION['contas_demo'][$email])) {
        $erro = 'Este email já foi cadastrado nesta sessão. Faça login.';
    } else {
        $_SESSION['contas_demo'][$email] = ['nome' => $nome, 'senha' => password_hash($senha, PASSWORD_DEFAULT), 'tipo_conta' => $tipo];
        $_SESSION['aviso'] = 'Conta criada com sucesso! Entre com seu email e senha.';
        redirecionar('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="auth-page">
        <aside class="auth-side" aria-hidden="true"></aside>

        <section class="auth-content">
            <form class="auth-form register-form" action="cadastro.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <?php if ($erro): ?><p class="auth-message auth-error" role="alert"><?= escapar($erro) ?></p><?php endif; ?>
                <h1 class="form-title">Criar Conta</h1>
                <p class="subtitle">Junte-se à comunidade Arthere</p>

                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.7-3.3 3.1-5 7-5s6.3 1.7 7 5"></path></svg>
                    <input type="text" name="nome" placeholder="Nome Completo" autocomplete="name" value="<?= escapar($nome) ?>" required>
                </div>

                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                    <input type="email" name="email" placeholder="Email" autocomplete="email" value="<?= escapar($email) ?>" required>
                </div>

                <div class="field password-field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    <input type="password" name="senha" placeholder="Senha (mínimo 6 caracteres)" autocomplete="new-password" minlength="6" maxlength="72" required>
                </div>

                <div class="account-type" aria-label="Tipo de conta">
                    <input type="radio" name="tipo_conta" id="contratante" value="contratante" required <?= $tipo === 'contratante' ? 'checked' : '' ?>>
                    <label for="contratante">Contratante</label>
                    <input type="radio" name="tipo_conta" id="agente-criativo" value="agente_criativo" required <?= $tipo === 'agente_criativo' ? 'checked' : '' ?>>
                    <label for="agente-criativo">Agente Criativo</label>
                </div>

                <button class="submit-button" type="submit">Cadastrar</button>
                <p class="auth-footer">Já tem uma conta? <a href="login.php">Faça Login</a></p>
            </form>
        </section>
    </main>
</body>
</html>
