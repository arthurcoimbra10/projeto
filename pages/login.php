<?php
require_once __DIR__ . '/../auth.php';
if (isset($_SESSION['usuario'])) {
    redirecionar('inicio.php');
}
$erro = '';
$email = strtolower(trim(campo('email')));
$aviso = $_SESSION['aviso'] ?? '';
unset($_SESSION['aviso']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conta = $_SESSION['contas_demo'][$email] ?? null;
    if (!formulario_valido()) {
        $erro = 'Sua sessão expirou. Tente novamente.';
    } elseif (!$conta || !password_verify(campo('senha'), $conta['senha'])) {
        $erro = 'Email ou senha incorretos. Cadastre uma conta nesta sessão para entrar.';
    } else {
        session_regenerate_id(true);
        $_SESSION['usuario'] = ['nome' => $conta['nome'], 'email' => $email, 'tipo_conta' => $conta['tipo_conta']];
        redirecionar('inicio.php');
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
            <form class="auth-form login-form" action="login.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <h1 class="brand-title">Arthere</h1>
                <p class="subtitle">Entre na sua conta</p>
                <?php if ($aviso): ?><p class="auth-message" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
                <?php if ($erro): ?><p class="auth-message auth-error" role="alert"><?= escapar($erro) ?></p><?php endif; ?>
                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                    <input type="email" name="email" placeholder="Email" autocomplete="email" value="<?= escapar($email) ?>" required>
                </div>

                <div class="field password-field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    <input type="password" name="senha" placeholder="Senha" autocomplete="current-password" required>
                </div>

                <button class="submit-button" type="submit">Entrar</button>

                <p class="auth-footer">Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </form>
        </section>
    </main>
</body>
</html>
