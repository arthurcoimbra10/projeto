<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'contratante') {
    redirecionar('inicio.php');
}

$idServico = trim((string) ($_GET['id'] ?? ''));
$emailUsuario = strtolower(trim((string) ($usuario['email'] ?? '')));
$_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
$projetos = $_SESSION['projetos'];
$servicoAtual = null;
foreach ($projetos as $projeto) {
    $idProjeto = trim((string) ($projeto['id'] ?? ''));
    $emailProprietario = strtolower(trim((string) ($projeto['owner_email'] ?? '')));
    $nomeCliente = strtolower(trim((string) ($projeto['client'] ?? '')));
    $ehProprietario = $emailProprietario === $emailUsuario || (!$emailProprietario && $nomeCliente === strtolower(trim((string) ($usuario['nome'] ?? ''))));
    if ($idProjeto === $idServico && $ehProprietario) {
        $servicoAtual = $projeto;
        break;
    }
}

if (!$servicoAtual) {
    $_SESSION['servico_aviso'] = 'Serviço não encontrado ou não pertence ao seu perfil.';
    redirecionar('inicio.php');
}

$erro = '';
$dados = [
    'titulo' => $servicoAtual['title'] ?? '',
    'tipo_artista' => $servicoAtual['category'] ?? '',
    'quantidade_artistas' => trim((string) ((int) preg_replace('/\D+/', '', (string) ($servicoAtual['budget'] ?? '1')) ?: 1)),
    'localizacao' => $servicoAtual['location'] ?? '',
    'latitude' => (string) ($servicoAtual['coordinates'][0] ?? '-23.558'),
    'longitude' => (string) ($servicoAtual['coordinates'][1] ?? '-46.668'),
    'horario_evento' => $servicoAtual['deadline'] ?? '',
    'descricao_evento' => $servicoAtual['description'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && campo('acao') === 'editar_servico') {
    $dados['titulo'] = trim(campo('titulo'));
    $dados['tipo_artista'] = trim(campo('tipo_artista'));
    $dados['quantidade_artistas'] = trim(campo('quantidade_artistas'));
    $dados['localizacao'] = trim(campo('localizacao'));
    $dados['latitude'] = trim(campo('latitude'));
    $dados['longitude'] = trim(campo('longitude'));
    $dados['horario_evento'] = trim(campo('horario_evento'));
    $dados['descricao_evento'] = trim(campo('descricao_evento'));

    $titulo = $dados['titulo'];
    $tipoArtista = $dados['tipo_artista'];
    $quantidadeArtistas = $dados['quantidade_artistas'];
    $localizacao = $dados['localizacao'];
    $latitude = (float) $dados['latitude'];
    $longitude = (float) $dados['longitude'];
    $horarioEvento = $dados['horario_evento'];
    $descricaoEvento = $dados['descricao_evento'];

    if (!formulario_valido()) {
        $erro = 'Sua sessão expirou. Tente novamente.';
    } elseif ($titulo === '' || $tipoArtista === '' || $localizacao === '' || $descricaoEvento === '') {
        $erro = 'Preencha o título, o tipo de artista, o local e os detalhes do evento.';
    } elseif (!is_numeric($quantidadeArtistas) || (int) $quantidadeArtistas < 1) {
        $erro = 'Informe uma quantidade válida de artistas.';
    } elseif ($horarioEvento === '') {
        $erro = 'Informe o horário do evento.';
    } elseif (!is_finite($latitude) || !is_finite($longitude)) {
        $erro = 'Informe coordenadas válidas para o mapa.';
    } else {
        foreach ($projetos as &$projeto) {
            $idProjeto = trim((string) ($projeto['id'] ?? ''));
            $emailProprietario = strtolower(trim((string) ($projeto['owner_email'] ?? '')));
            $nomeCliente = strtolower(trim((string) ($projeto['client'] ?? '')));
            $ehProprietario = $emailProprietario === $emailUsuario || (!$emailProprietario && $nomeCliente === strtolower(trim((string) ($usuario['nome'] ?? ''))));
            if ($idProjeto === $idServico && $ehProprietario) {
                $projeto['title'] = $titulo;
                $projeto['category'] = $tipoArtista;
                $projeto['location'] = $localizacao;
                $projeto['coordinates'] = [$latitude, $longitude];
                $projeto['budget'] = (int) $quantidadeArtistas . ' artista' . (((int) $quantidadeArtistas) > 1 ? 's' : '');
                $projeto['deadline'] = $horarioEvento;
                $projeto['description'] = $descricaoEvento;
                break;
            }
        }
        unset($projeto);
        $_SESSION['projetos'] = $projetos;
        $_SESSION['servico_aviso'] = 'Serviço atualizado com sucesso.';
        redirecionar('inicio.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar serviço | Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="settings-page create-service-page">
    <header class="home-header">
        <a class="home-brand" href="inicio.php" aria-label="Arthere — início">Arthere</a>
        <div></div>
    </header>
    <main class="settings-layout">
        <aside class="home-sidebar">
            <nav class="home-nav" aria-label="Menu principal">
                <a href="inicio.php">Início</a>
                <a href="perfil.php">Perfil</a>
                <a href="chat.php">Chat</a>
                <?php if (($usuario['tipo_conta'] ?? '') === 'contratante'): ?><a href="candidatos.php">Candidatos</a><?php endif; ?>
                <a href="configs.php">Configuração</a>
            </nav>
            <p class="home-account"><?= escapar($usuario['nome']) ?><br>Contratante</p>
            <form class="logout-form" action="sair.php" method="post">
                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                <button class="submit-button" type="submit">Sair</button>
            </form>
        </aside>

        <section class="settings-content">
            <div class="settings-stack">
                <header class="settings-heading">
                    <p class="profile-eyebrow">Atualizar oportunidade</p>
                    <h1>Editar serviço</h1>
                </header>

                <section class="settings-section">
                    <?php if ($erro): ?><p class="auth-message auth-error" role="alert"><?= escapar($erro) ?></p><?php endif; ?>

                    <form class="portfolio-form" action="editar-servico.php?id=<?= urlencode($idServico) ?>" method="post">
                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                        <input type="hidden" name="acao" value="editar_servico">

                        <label for="titulo-servico">Título do serviço</label>
                        <input id="titulo-servico" name="titulo" type="text" value="<?= escapar($dados['titulo']) ?>" required>

                        <label for="tipo-artista">Tipo de artista necessário</label>
                        <select id="tipo-artista" name="tipo_artista" required>
                            <option value="">Selecione</option>
                            <option value="Arte urbana" <?= $dados['tipo_artista'] === 'Arte urbana' ? 'selected' : '' ?>>Arte urbana</option>
                            <option value="Fotografia" <?= $dados['tipo_artista'] === 'Fotografia' ? 'selected' : '' ?>>Fotografia</option>
                            <option value="Audiovisual" <?= $dados['tipo_artista'] === 'Audiovisual' ? 'selected' : '' ?>>Audiovisual</option>
                            <option value="Música" <?= $dados['tipo_artista'] === 'Música' ? 'selected' : '' ?>>Música</option>
                            <option value="Ilustração" <?= $dados['tipo_artista'] === 'Ilustração' ? 'selected' : '' ?>>Ilustração</option>
                            <option value="Design" <?= $dados['tipo_artista'] === 'Design' ? 'selected' : '' ?>>Design</option>
                        </select>

                        <label for="quantidade-artistas">Quantidade de artistas necessários</label>
                        <input id="quantidade-artistas" name="quantidade_artistas" type="number" min="1" value="<?= escapar($dados['quantidade_artistas']) ?>" required>

                        <label for="localizacao-servico">Local do evento</label>
                        <input id="localizacao-servico" name="localizacao" type="text" value="<?= escapar($dados['localizacao']) ?>" required>

                        <div class="field-row">
                            <div>
                                <label for="latitude-servico">Latitude</label>
                                <input id="latitude-servico" name="latitude" type="number" step="0.000001" value="<?= escapar($dados['latitude']) ?>" required>
                            </div>
                            <div>
                                <label for="longitude-servico">Longitude</label>
                                <input id="longitude-servico" name="longitude" type="number" step="0.000001" value="<?= escapar($dados['longitude']) ?>" required>
                            </div>
                        </div>

                        <label for="horario-evento">Horário do evento</label>
                        <input id="horario-evento" name="horario_evento" type="datetime-local" value="<?= escapar($dados['horario_evento']) ?>" required>

                        <label for="descricao-evento">Detalhes do evento</label>
                        <textarea id="descricao-evento" name="descricao_evento" rows="5" required><?= escapar($dados['descricao_evento']) ?></textarea>

                        <button class="submit-button" type="submit">Salvar alterações</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
