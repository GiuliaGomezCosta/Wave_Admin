
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        exit('Usuário inválido.');
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuarioAtual = $stmt->fetch();

    if (!$usuarioAtual) {
        http_response_code(404);
        exit('Usuário não encontrado.');
    }

    if (($_POST['operacao'] ?? '') === 'alternar_status') {
        $novoStatus = $usuarioAtual['status'] === 'ativo' ? 'bloqueado' : 'ativo';

        $stmt = $pdo->prepare(
            "UPDATE usuarios SET status = :status WHERE id = :id"
        );

        $stmt->execute([
            ':status' => $novoStatus,
            ':id' => $id
        ]);

        registrarLog($pdo, 'Alteração de status', "Usuário ID {$id}: {$novoStatus}");

        header("Location: usuarios.php");
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? '';

    $erro = '';

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um nome e um e-mail válido.';
    } elseif (!in_array($status, ['ativo', 'bloqueado'], true)) {
        $erro = 'Status inválido.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE usuarios
                 SET nome = :nome, email = :email, status = :status
                 WHERE id = :id"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':status' => $status,
                ':id' => $id
            ]);

            registrarLog($pdo, 'Edição', "Usuário atualizado: {$email}");

            header("Location: usuarios.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                error_log($e->getMessage());
                $erro = 'Não foi possível atualizar o usuário.';
            }
        }
    }

    $usuario = [
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'status' => $status
    ];

} else {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        exit('Usuário inválido.');
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(404);
        exit('Usuário não encontrado.');
    }

    $erro = '';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuário | Wave Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="form-wave-container">
    <div class="form-wave">
        <h1>Editar usuário</h1>
        <p>Atualize as informações do usuário.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= e($usuario['id']) ?>">

            <label>Nome completo</label>
            <input type="text" name="nome" required maxlength="150" value="<?= e($usuario['nome']) ?>">

            <label>E-mail</label>
            <input type="email" name="email" required maxlength="150" value="<?= e($usuario['email']) ?>">

            <label>Status</label>
            <select name="status">
                <option value="ativo" <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="bloqueado" <?= $usuario['status'] === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select>

            <button type="submit" class="btn-wave w-100">Salvar alterações</button>
            <a href="usuarios.php" class="link-voltar">Cancelar e voltar</a>
        </form>
    </div>
</div>

</body>
</html>
