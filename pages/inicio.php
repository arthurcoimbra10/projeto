<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início | Arthere</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="../css/style.css">
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script defer src="../js/script.js"></script>
</head>
<body class="home-page">
    <header class="home-header">
        <a class="home-brand" href="inicio.php" aria-label="Arthere — início">Arthere</a>
        <form class="project-search" role="search">
            <label class="visually-hidden" for="project-query">Buscar projetos por nome, local ou categoria</label>
            <input id="project-query" type="search" placeholder="Buscar" autocomplete="off">
            <button id="filter-toggle" type="button" aria-label="Filtrar projetos" aria-expanded="false" aria-controls="project-filters">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="9" cy="18" r="2"/></svg>
            </button>
            <div id="project-filters" class="project-filters" hidden>
                <label for="project-category">Categoria</label>
                <select id="project-category">
                    <option value="">Todas as categorias</option>
                    <option>Arte urbana</option>
                    <option>Fotografia</option>
                    <option>Audiovisual</option>
                </select>
            </div>
        </form>
    </header>
    <main class="home-layout">
        <aside class="home-sidebar">
            <nav class="home-nav" aria-label="Menu principal">
                <a href="inicio.php" aria-current="page">Início</a>
                <a href="perfil.php">Perfil</a>
                <a href="chat.php">Chat</a>
                <a href="configs.php">Configuração</a>
            </nav>
            <p class="home-account"><?= escapar($usuario['nome']) ?><br><?= $usuario['tipo_conta'] === 'contratante' ? 'Contratante' : 'Agente criativo' ?></p>
            <form class="logout-form" action="sair.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <button class="submit-button" type="submit">Sair</button>
            </form>
        </aside>
        <section class="map-section" aria-label="Mapa de projetos">
            <div id="project-map" aria-label="Localização dos projetos disponíveis"></div>
            <div class="map-caption"><strong>Explore oportunidades</strong><span id="project-count" role="status">Carregando projetos…</span></div>
            <p id="map-error" class="map-error" role="status" hidden>Não foi possível carregar o mapa. Verifique sua conexão. Você pode selecionar os projetos na lista ao lado.</p>
            <noscript><p class="map-error">Ative o JavaScript para explorar o mapa e os projetos.</p></noscript>
        </section>
        <aside class="project-panel" aria-label="Descrição do projeto">
            <div class="panel-heading"><h1>Descrição</h1><button id="close-project" type="button" aria-label="Fechar detalhes do projeto" hidden>×</button></div>
            <div id="project-empty" class="project-empty"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M38 20c0 11-14 23-14 23S10 31 10 20a14 14 0 1 1 28 0Z"/><circle cx="24" cy="20" r="5"/></svg><h2>Seu próximo projeto está no mapa</h2><p>Selecione um ponto para conhecer o serviço e os detalhes do projeto.</p></div>
            <article id="project-detail" hidden aria-live="polite">
                <span id="detail-category" class="project-tag"></span>
                <h2 id="detail-title"></h2>
                <p id="detail-description" class="detail-description"></p>
                <dl class="project-facts"><dt>Contratante</dt><dd id="detail-client"></dd><dt>Local do serviço</dt><dd id="detail-location"></dd><dt>Orçamento estimado</dt><dd id="detail-budget"></dd><dt>Prazo de execução</dt><dd id="detail-deadline"></dd></dl>
            </article>
            <section class="nearby-projects" aria-labelledby="nearby-title"><h2 id="nearby-title">Projetos no mapa</h2><div id="project-list"></div></section>
            <p class="demo-note">Projetos ilustrativos para demonstração.</p>
        </aside>
    </main>
</body>
</html>
