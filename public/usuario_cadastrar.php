
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'ativo';

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um nome e um e-mail válido.';
    } elseif (!in_array($status, ['ativo', 'bloqueado'], true)) {
        $erro = 'Status inválido.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, status)
                 VALUES (:nome, :email, :status)"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':status' => $status
            ]);

            registrarLog($pdo, 'Cadastro', "Usuário cadastrado: {$email}");

            header("Location: usuarios.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                error_log($e->getMessage());
                $erro = 'Não foi possível cadastrar o usuário.';
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
    <title>Adicionar usuário | Wave Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="form-wave-container">
    <div class="form-wave">
        <h1>Novo usuário</h1>
        <p>Preencha os dados para cadastrar um usuário.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <label>Nome completo</label>
            <input type="text" name="nome" required maxlength="150"
                   value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label>E-mail</label>
            <input type="email" name="email" required maxlength="150"
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label>Status</label>
            <select name="status">
                <option value="ativo">Ativo</option>
                <option value="bloqueado">Bloqueado</option>
            </select>

            <button type="submit" class="btn-wave w-100">Cadastrar usuário</button>
            <a href="usuarios.php" class="link-voltar">Cancelar e voltar</a>
        </form>
    </div>
</div>

</body>
</html>
