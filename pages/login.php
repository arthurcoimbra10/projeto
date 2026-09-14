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
            <form class="auth-form login-form" action="#" method="post">
                <h1 class="brand-title">Arthere</h1>
                <p class="subtitle">Entre na sua conta</p>
                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                    <input type="email" name="email" placeholder="Email" autocomplete="email" required>
                </div>

                <div class="field password-field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    <input type="password" name="senha" placeholder="Senha" autocomplete="current-password" required>
                </div>

                <a class="forgot-password" href="#">Esqueceu a senha?</a>
                <button class="submit-button" type="submit">Entrar</button>

                <p class="auth-footer">Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </form>
        </section>
    </main>
</body>
</html>
