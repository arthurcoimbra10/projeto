<?php
require_once __DIR__ . '/../auth.php';
if (!isset($_SESSION['usuario'])) {
    redirecionar('login.php');
}

$usuario = $_SESSION['usuario'];
if ($usuario['tipo_conta'] !== 'contratante') {
    redirecionar('inicio.php');
}

$erro = '';
$dados = [
    'titulo' => '',
    'tipo_artista' => '',
    'quantidade_artistas' => '1',
    'localizacao' => '',
    'latitude' => '-23.558',
    'longitude' => '-46.668',
    'horario_evento' => '',
    'descricao_evento' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && campo('acao') === 'criar_servico') {
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
        $_SESSION['projetos'] = isset($_SESSION['projetos']) && is_array($_SESSION['projetos']) ? $_SESSION['projetos'] : [];
        $projetos = $_SESSION['projetos'];
        $projetos[] = [
            'id' => time() . '-' . ((int) count($projetos) + 1),
            'title' => $titulo,
            'category' => $tipoArtista,
            'client' => $usuario['nome'],
            'owner_email' => $usuario['email'],
            'location' => $localizacao,
            'coordinates' => [$latitude, $longitude],
            'budget' => (int) $quantidadeArtistas . ' artista' . (((int) $quantidadeArtistas) > 1 ? 's' : ''),
            'deadline' => $horarioEvento,
            'description' => $descricaoEvento,
        ];
        $_SESSION['projetos'] = $projetos;
        $_SESSION['servico_aviso'] = 'Serviço publicado no mapa com sucesso.';
        redirecionar('inicio.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar serviço | Arthere</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addressInput = document.getElementById('localizacao-servico');
            const latitudeInput = document.getElementById('latitude-servico');
            const longitudeInput = document.getElementById('longitude-servico');
            const statusMessage = document.getElementById('address-geocode-status');
            const form = document.querySelector('.portfolio-form');
            let timer = null;

            async function buscarCoordenadasPorEndereco() {
                const endereco = addressInput.value.trim();
                if (!addressInput || !latitudeInput || !longitudeInput) {
                    return false;
                }
                if (!endereco || endereco.length < 5) {
                    return false;
                }

                if (statusMessage) {
                    statusMessage.textContent = 'Buscando coordenadas do endereço…';
                }

                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(endereco)}`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept-Language': 'pt-BR'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Falha ao consultar o endereço');
                    }

                    const dados = await response.json();
                    if (!dados || !dados.length) {
                        throw new Error('Endereço não encontrado');
                    }

                    const resultado = dados[0];
                    latitudeInput.value = resultado.lat;
                    longitudeInput.value = resultado.lon;

                    if (statusMessage) {
                        statusMessage.textContent = 'Coordenadas atualizadas automaticamente para o endereço informado.';
                    }
                    return true;
                } catch (error) {
                    if (statusMessage) {
                        statusMessage.textContent = 'Não foi possível localizar o endereço automaticamente. Ajuste as coordenadas manualmente.';
                    }
                    return false;
                }
            }

            if (addressInput && latitudeInput && longitudeInput) {
                addressInput.addEventListener('input', () => {
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(() => {
                        buscarCoordenadasPorEndereco();
                    }, 700);
                });
                addressInput.addEventListener('blur', () => {
                    buscarCoordenadasPorEndereco();
                });
            }

            if (form) {
                form.addEventListener('submit', async (event) => {
                    const endereco = addressInput.value.trim();
                    const latitude = Number(latitudeInput.value);
                    const longitude = Number(longitudeInput.value);

                    if (!endereco || !Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                        event.preventDefault();
                        const ok = await buscarCoordenadasPorEndereco();
                        if (!ok) {
                            if (statusMessage) {
                                statusMessage.textContent = 'Informe um endereço válido ou ajuste as coordenadas manualmente.';
                            }
                            return;
                        }
                    }
                });
            }
        });
    </script>
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
                    <p class="profile-eyebrow">Publicar oportunidade</p>
                    <h1>Adicionar serviço no mapa</h1>
                </header>

                <section class="settings-section">
                    <?php if ($erro): ?><p class="auth-message auth-error" role="alert"><?= escapar($erro) ?></p><?php endif; ?>

                    <form class="portfolio-form" action="novo-servico.php" method="post">
                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
                        <input type="hidden" name="acao" value="criar_servico">

                        <label for="titulo-servico">Título do serviço</label>
                        <input id="titulo-servico" name="titulo" type="text" value="<?= escapar($dados['titulo']) ?>" placeholder="Ex.: Evento de música ao vivo" required>

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
                        <input id="localizacao-servico" name="localizacao" type="text" value="<?= escapar($dados['localizacao']) ?>" placeholder="Ex.: Vila Madalena, São Paulo" required>
                        <p id="address-geocode-status" class="geocode-status">As coordenadas serão preenchidas automaticamente conforme o endereço.</p>

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
                        <textarea id="descricao-evento" name="descricao_evento" rows="5" placeholder="Descreva o tipo de evento, a proposta, a atmosfera e o que será necessário do artista." required><?= escapar($dados['descricao_evento']) ?></textarea>

                        <button class="submit-button" type="submit">Publicar no mapa</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
