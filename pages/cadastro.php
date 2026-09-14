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
            <form class="auth-form register-form" action="#" method="post">
                <h1 class="form-title">Criar Conta</h1>
                <p class="subtitle">Junte-se à comunidade Arthere</p>

                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.7-3.3 3.1-5 7-5s6.3 1.7 7 5"></path></svg>
                    <input type="text" name="nome" placeholder="Nome Completo" autocomplete="name" required>
                </div>

                <div class="field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                    <input type="email" name="email" placeholder="Email" autocomplete="email" required>
                </div>

                <div class="field password-field">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    <input type="password" name="senha" placeholder="Senha" autocomplete="new-password" required>
                </div>

                <div class="account-type" aria-label="Tipo de conta">
                    <input type="radio" name="tipo_conta" id="contratante" value="contratante">
                    <label for="contratante">Contratante</label>
                    <input type="radio" name="tipo_conta" id="agente-criativo" value="agente_criativo">
                    <label for="agente-criativo">Agente Criativo</label>
                </div>

                <button class="submit-button" type="submit">Cadastrar</button>
                <p class="auth-footer">Já tem uma conta? <a href="login.php">Faça Login</a></p>
            </form>
        </section>
    </main>
</body>
</html>
