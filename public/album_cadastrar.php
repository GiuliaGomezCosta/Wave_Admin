<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';

$titulo = '';
$artista_id = '';
$data_lancamento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_validate();

    $titulo = trim($_POST['titulo'] ?? '');
    $artista_id = $_POST['artista_id'] ?? '';
    $data_lancamento = trim($_POST['data_lancamento'] ?? '');

    // Validar título
    if ($titulo === '') {
        $erro = 'Informe o título do álbum.';

    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título deve ter no máximo 150 caracteres.';

    // Validar artista
    } elseif (
        !ctype_digit((string)$artista_id) ||
        (int)$artista_id < 1
    ) {
        $erro = 'Selecione um artista válido.';

    // Validar data
    } elseif ($data_lancamento !== '') {

        $data = DateTime::createFromFormat('!Y-m-d', $data_lancamento);

        if (!$data || $data->format('Y-m-d') !== $data_lancamento) {
            $erro = 'Informe uma data de lançamento válida.';
        }
    }

    // Verificar se o artista existe
    if ($erro === '') {

        $stmt = $pdo->prepare(
            "SELECT id FROM artistas WHERE id = :id"
        );

        $stmt->execute([':id' => $artista_id]);

        if (!$stmt->fetch()) {
            $erro = 'O artista selecionado não existe.';
        }
    }

    // Preparar imagem da capa
    $caminhoCapa = null;
    $arquivoSalvo = null;

    if ($erro === '' && isset($_FILES['capa']) &&
        $_FILES['capa']['error'] !== UPLOAD_ERR_NO_FILE) {

        $arquivo = $_FILES['capa'];

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $erro = 'Ocorreu um erro ao enviar a imagem.';

        } elseif ($arquivo['size'] > 5 * 1024 * 1024) {
            $erro = 'A imagem deve ter no máximo 5 MB.';

        } else {

            $tiposPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo = $finfo->file($arquivo['tmp_name']);

            if (!isset($tiposPermitidos[$tipo])) {
                $erro = 'Formato inválido. Envie uma imagem JPG, PNG ou WEBP.';

            } elseif (getimagesize($arquivo['tmp_name']) === false) {
                $erro = 'O arquivo enviado não é uma imagem válida.';

            } else {

                $pastaUpload = __DIR__ . '/uploads/albuns/';

                if (!is_dir($pastaUpload)) {
                    $erro = 'A pasta de armazenamento das capas não foi encontrada.';

                } elseif (!is_writable($pastaUpload)) {
                    $erro = 'A pasta de capas não possui permissão de escrita.';

                } else {

                    $nomeArquivo = bin2hex(random_bytes(16))
                        . '.' . $tiposPermitidos[$tipo];

                    $arquivoSalvo = $pastaUpload . $nomeArquivo;
                    $caminhoCapa = 'uploads/albuns/' . $nomeArquivo;

                    if (!move_uploaded_file(
                        $arquivo['tmp_name'],
                        $arquivoSalvo
                    )) {
                        $erro = 'Não foi possível salvar a imagem enviada.';
                        $arquivoSalvo = null;
                        $caminhoCapa = null;
                    }
                }
            }
        }
    }

    // Cadastrar álbum
    if ($erro === '') {

        try {

            $stmt = $pdo->prepare(
                "INSERT INTO albuns
                (artista_id, titulo, data_lancamento, capa)
                VALUES
                (:artista_id, :titulo, :data_lancamento, :capa)"
            );

            $stmt->execute([
                ':artista_id' => (int)$artista_id,
                ':titulo' => $titulo,
                ':data_lancamento' => $data_lancamento !== ''
                    ? $data_lancamento
                    : null,
                ':capa' => $caminhoCapa
            ]);

            registrarLog(
                $pdo,
                'Cadastro de álbum',
                'Álbum cadastrado: ' . $titulo
            );

            header('Location: albuns.php');
            exit;

        } catch (PDOException $ex) {

            // Remover imagem caso o cadastro não seja concluído
            if ($arquivoSalvo && is_file($arquivoSalvo)) {
                unlink($arquivoSalvo);
            }

            $erro = 'Não foi possível cadastrar o álbum. Verifique os dados e tente novamente.';
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

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar álbum | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Cadastrar álbum</h1>
            <p>Adicione um novo álbum à plataforma Wave.</p>
        </div>

    </div>

    <div class="usuarios-painel">

        <div class="form-wave-container">

            <?php if ($erro !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-wave" enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <label for="titulo">Título do álbum *</label>

                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    maxlength="150"
                    placeholder="Digite o título do álbum"
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

                <label for="data_lancamento">Data de lançamento</label>

                <input
                    type="date"
                    id="data_lancamento"
                    name="data_lancamento"
                    value="<?= e($data_lancamento) ?>"
                >

                <label for="capa">Capa do álbum</label>

                <input
                    type="file"
                    id="capa"
                    name="capa"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

                <small>
                    Formatos permitidos: JPG, PNG e WEBP. Tamanho máximo: 5 MB.
                </small>

                <div class="acoes-wave mt-4">

                    <button type="submit" class="btn-wave">
                        Cadastrar álbum
                    </button>

                    <a href="albuns.php" class="btn-voltar">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>