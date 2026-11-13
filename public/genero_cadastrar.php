
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {
        $erro = 'Informe o nome do gênero musical.';
    } else {
        // Verifica se já existe um gênero com esse nome
        $stmt = $pdo->prepare("
            SELECT id FROM generos
            WHERE nome = :nome
        ");
        $stmt->execute([':nome' => $nome]);

        if ($stmt->fetch()) {
            $erro = 'Já existe um gênero com esse nome.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO generos (nome)
                    VALUES (:nome)
                ");

                $stmt->execute([':nome' => $nome]);

                registrarLog(
                    $pdo,
                    'Cadastro de gênero',
                    'Gênero cadastrado: ' . $nome
                );

                header("Location: generos.php");
                exit;

            } catch (PDOException $e) {
                $erro = 'Não foi possível cadastrar o gênero. Verifique se ele já existe.';
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

    <title>Novo gênero | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">
        <div>
            <h1>Novo gênero musical</h1>
            <p>Cadastre um novo gênero na plataforma Wave.</p>
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

                <div class="mb-3">
                    <label for="nome" class="form-label">
                        Nome do gênero
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        class="form-control"
                        placeholder="Ex.: Pop, Rock, Sertanejo..."
                        value="<?= e($nome) ?>"
                        required
                    >
                </div>

                <div class="d-flex gap-2 flex-wrap">

                    <button type="submit" class="btn-wave">
                        Cadastrar gênero
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
