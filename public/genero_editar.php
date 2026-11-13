
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(
    $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: generos.php");
    exit;
}

// Busca o gênero
$stmt = $pdo->prepare("
    SELECT id, nome
    FROM generos
    WHERE id = :id
");
$stmt->execute([':id' => $id]);

$genero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$genero) {
    header("Location: generos.php");
    exit;
}

$erro = '';
$nome = $genero['nome'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {
        $erro = 'Informe o nome do gênero musical.';
    } else {
        // Verifica duplicidade, ignorando o próprio gênero
        $stmt = $pdo->prepare("
            SELECT id
            FROM generos
            WHERE nome = :nome
            AND id <> :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':id' => $id
        ]);

        if ($stmt->fetch()) {
            $erro = 'Já existe outro gênero com esse nome.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE generos
                    SET nome = :nome
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':nome' => $nome,
                    ':id' => $id
                ]);

                registrarLog(
                    $pdo,
                    'Edição de gênero',
                    'Gênero atualizado: ' . $nome
                );

                header("Location: generos.php");
                exit;

            } catch (PDOException $e) {
                $erro = 'Não foi possível atualizar o gênero.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar gênero | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">
        <div>
            <h1>Editar gênero musical</h1>
            <p>Atualize as informações do gênero cadastrado.</p>
        </div>
    </div>

    <div class="usuarios-painel">

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <div class="form-wave-container">

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

                <div class="mb-3">
                    <label for="nome" class="form-label">
                        Nome do gênero
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        class="form-control"
                        value="<?= e($nome) ?>"
                        required
                    >
                </div>

                <div class="d-flex gap-2 flex-wrap">

                    <button type="submit" class="btn-wave">
                        Salvar alterações
                    </button>

                    <a href="generos.php" class="btn-voltar">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
