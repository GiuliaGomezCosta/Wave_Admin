<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    die('Música não encontrada.');
}

// Buscar música
$stmt = $pdo->prepare(
    "SELECT * FROM musicas WHERE id = :id"
);
$stmt->execute([':id' => $id]);

$musica = $stmt->fetch();

if (!$musica) {
    die('Música não encontrada.');
}

// Valores iniciais
$titulo = $musica['titulo'];
$artista_id = $musica['artista_id'];
$album_id = $musica['album_id'] ?? '';
$genero_id = $musica['genero_id'] ?? '';
$duracao = $musica['duracao'] ?? '';
$status = $musica['status'];

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_validate();

    $titulo = trim($_POST['titulo'] ?? '');
    $artista_id = $_POST['artista_id'] ?? '';
    $album_id = $_POST['album_id'] ?? '';
    $genero_id = $_POST['genero_id'] ?? '';
    $duracao = trim($_POST['duracao'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($titulo === '') {
        $erro = 'Informe o título da música.';

    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título deve ter no máximo 150 caracteres.';

    } elseif (!ctype_digit((string)$artista_id) || (int)$artista_id < 1) {
        $erro = 'Selecione um artista válido.';

    } elseif (
        $album_id !== '' &&
        (!ctype_digit((string)$album_id) || (int)$album_id < 1)
    ) {
        $erro = 'Selecione um álbum válido.';

    } elseif (
        $genero_id !== '' &&
        (!ctype_digit((string)$genero_id) || (int)$genero_id < 1)
    ) {
        $erro = 'Selecione um gênero válido.';

    } elseif (
        $duracao !== '' &&
        !preg_match('/^\d{1,3}:[0-5]\d:[0-5]\d$/', $duracao)
    ) {
        $erro = 'A duração deve estar no formato HH:MM:SS.';

    } elseif (
        !in_array($status, ['publicada', 'pendente', 'removida'], true)
    ) {
        $erro = 'Selecione um status válido.';
    }

    // Verificar artista
    if ($erro === '') {

        $stmt = $pdo->prepare(
            "SELECT id FROM artistas WHERE id = :id"
        );

        $stmt->execute([':id' => $artista_id]);

        if (!$stmt->fetch()) {
            $erro = 'O artista selecionado não existe.';
        }
    }

    // Verificar álbum e artista
    if ($erro === '' && $album_id !== '') {

        $stmt = $pdo->prepare(
            "SELECT id FROM albuns
             WHERE id = :album_id
             AND artista_id = :artista_id"
        );

        $stmt->execute([
            ':album_id' => $album_id,
            ':artista_id' => $artista_id
        ]);

        if (!$stmt->fetch()) {
            $erro = 'O álbum selecionado não pertence ao artista escolhido.';
        }
    }

    // Verificar gênero
    if ($erro === '' && $genero_id !== '') {

        $stmt = $pdo->prepare(
            "SELECT id FROM generos WHERE id = :id"
        );

        $stmt->execute([':id' => $genero_id]);

        if (!$stmt->fetch()) {
            $erro = 'O gênero selecionado não existe.';
        }
    }

    // Atualizar música
    if ($erro === '') {

        try {

            $stmt = $pdo->prepare(
                "UPDATE musicas SET
                    titulo = :titulo,
                    artista_id = :artista_id,
                    album_id = :album_id,
                    genero_id = :genero_id,
                    duracao = :duracao,
                    status = :status
                 WHERE id = :id"
            );

            $stmt->execute([
                ':titulo' => $titulo,
                ':artista_id' => (int)$artista_id,
                ':album_id' => $album_id !== '' ? (int)$album_id : null,
                ':genero_id' => $genero_id !== '' ? (int)$genero_id : null,
                ':duracao' => $duracao !== '' ? $duracao : null,
                ':status' => $status,
                ':id' => $id
            ]);

            registrarLog(
                $pdo,
                'Edição de música',
                'Música ID ' . $id . ' editada: ' . $titulo
            );

            header('Location: musicas.php');
            exit;

        } catch (PDOException $ex) {
            $erro = 'Não foi possível atualizar a música. Verifique os dados e tente novamente.';
        }
    }
}

// Buscar artistas
$stmt = $pdo->query(
    "SELECT id, nome_artistico
     FROM artistas
     ORDER BY nome_artistico ASC"
);
$artistas = $stmt->fetchAll();

// Buscar álbuns
$stmt = $pdo->query(
    "SELECT al.id, al.titulo, al.artista_id, a.nome_artistico
     FROM albuns al
     INNER JOIN artistas a ON al.artista_id = a.id
     ORDER BY al.titulo ASC"
);
$albuns = $stmt->fetchAll();

// Buscar gêneros
$stmt = $pdo->query(
    "SELECT id, nome
     FROM generos
     ORDER BY nome ASC"
);
$generos = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar música | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Editar música</h1>
            <p>Atualize as informações da música cadastrada.</p>
        </div>

    </div>

    <div class="usuarios-painel">

        <div class="form-wave-container">

            <?php if ($erro !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-wave">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= e($id) ?>"
                >

                <label for="titulo">Título da música *</label>

                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    maxlength="150"
                    value="<?= e($titulo) ?>"
                    required
                >

                <label for="artista_id">Artista *</label>

                <select id="artista_id" name="artista_id" required>

                    <option value="">Selecione um artista</option>

                    <?php foreach ($artistas as $artista): ?>

                        <option
                            value="<?= e($artista['id']) ?>"
                            <?= (string)$artista_id === (string)$artista['id'] ? 'selected' : '' ?>
                        >
                            <?= e($artista['nome_artistico']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <label for="album_id">Álbum (opcional)</label>

                <select id="album_id" name="album_id">

                    <option value="">Nenhum álbum</option>

                    <?php foreach ($albuns as $album): ?>

                        <option
                            value="<?= e($album['id']) ?>"
                            data-artista="<?= e($album['artista_id']) ?>"
                            <?= (string)$album_id === (string)$album['id'] ? 'selected' : '' ?>
                        >
                            <?= e($album['titulo']) ?> — <?= e($album['nome_artistico']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <label for="genero_id">Gênero musical (opcional)</label>

                <select id="genero_id" name="genero_id">

                    <option value="">Selecione um gênero</option>

                    <?php foreach ($generos as $genero): ?>

                        <option
                            value="<?= e($genero['id']) ?>"
                            <?= (string)$genero_id === (string)$genero['id'] ? 'selected' : '' ?>
                        >
                            <?= e($genero['nome']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <label for="duracao">Duração (opcional)</label>

                <input
                    type="text"
                    id="duracao"
                    name="duracao"
                    placeholder="Exemplo: 00:03:45"
                    pattern="[0-9]{1,3}:[0-5][0-9]:[0-5][0-9]"
                    value="<?= e($duracao) ?>"
                >

                <small>
                    Informe a duração no formato horas:minutos:segundos.
                </small>

                <label for="status">Status da música *</label>

                <select id="status" name="status" required>

                    <option value="pendente"
                        <?= $status === 'pendente' ? 'selected' : '' ?>>
                        Pendente
                    </option>

                    <option value="publicada"
                        <?= $status === 'publicada' ? 'selected' : '' ?>>
                        Publicada
                    </option>

                    <option value="removida"
                        <?= $status === 'removida' ? 'selected' : '' ?>>
                        Removida
                    </option>

                </select>

                <div class="acoes-wave mt-4">

                    <button type="submit" class="btn-wave">
                        Salvar alterações
                    </button>

                    <a href="musicas.php" class="btn-voltar">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
const artistaSelect = document.getElementById('artista_id');
const albumSelect = document.getElementById('album_id');

function filtrarAlbuns() {

    const artistaSelecionado = artistaSelect.value;

    Array.from(albumSelect.options).forEach(option => {

        if (option.value === '') {
            option.hidden = false;
            return;
        }

        const artistaDoAlbum = option.dataset.artista;

        option.hidden = (
            artistaSelecionado !== '' &&
            artistaDoAlbum !== artistaSelecionado
        );
    });

    const opcaoSelecionada = albumSelect.selectedOptions[0];

    if (
        opcaoSelecionada &&
        opcaoSelecionada.value !== '' &&
        opcaoSelecionada.hidden
    ) {
        albumSelect.value = '';
    }
}

artistaSelect.addEventListener('change', filtrarAlbuns);

filtrarAlbuns();
</script>

</body>
</html>