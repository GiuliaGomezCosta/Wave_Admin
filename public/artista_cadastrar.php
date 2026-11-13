
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nome = trim($_POST['nome_artistico'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');

    if ($nome === '') {
        $erro = 'Informe o nome artístico.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO artistas
                (nome_artistico, email, biografia, status)
                VALUES (:nome, :email, :biografia, 'pendente')"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email ?: null,
                ':biografia' => $biografia ?: null
            ]);

            registrarLog(
                $pdo,
                'Cadastro',
                "Artista cadastrado: {$nome}"
            );

            header("Location: artistas.php");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ($e->getCode() === '23000') {
                $erro = 'Este artista já está cadastrado.';
            } else {
                $erro = 'Não foi possível cadastrar o artista.';
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

    <title>Adicionar artista | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="form-wave-container">
    <div class="form-wave">

        <h1>Novo artista</h1>
        <p>Preencha os dados para cadastrar um artista.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label>Nome artístico</label>
            <input
                type="text"
                name="nome_artistico"
                required
                maxlength="150"
                value="<?= e($_POST['nome_artistico'] ?? '') ?>"
            >

            <label>E-mail</label>
            <input
                type="email"
                name="email"
                maxlength="150"
                value="<?= e($_POST['email'] ?? '') ?>"
            >

            <label>Biografia</label>
            <textarea
                name="biografia"
                rows="4"
            ><?= e($_POST['biografia'] ?? '') ?></textarea>

            <button type="submit" class="btn-wave w-100">
                Cadastrar artista
            </button>

            <a href="artistas.php" class="link-voltar">
                Cancelar e voltar
            </a>

        </form>
    </div>
</div>

</body>
</html>
