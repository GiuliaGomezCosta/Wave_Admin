
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Artista inválido.');
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM artistas WHERE id = :id"
    );
    $stmt->execute([':id' => $id]);
    $artistaAtual = $stmt->fetch();

    if (!$artistaAtual) {
        http_response_code(404);
        exit('Artista não encontrado.');
    }

    // Aprovar, ativar ou bloquear
    if (($_POST['operacao'] ?? '') === 'alternar_status') {

        $statusAtual = $artistaAtual['status'];

        if ($statusAtual === 'pendente') {
            $novoStatus = 'ativo';
        } elseif ($statusAtual === 'ativo') {
            $novoStatus = 'bloqueado';
        } else {
            $novoStatus = 'ativo';
        }

        $stmt = $pdo->prepare(
            "UPDATE artistas SET status = :status WHERE id = :id"
        );

        $stmt->execute([
            ':status' => $novoStatus,
            ':id' => $id
        ]);

        registrarLog(
            $pdo,
            'Alteração de status',
            "Artista ID {$id}: {$statusAtual} para {$novoStatus}"
        );

        header("Location: artistas.php");
        exit;
    }

    // Edição dos dados
    $nome = trim($_POST['nome_artistico'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($nome === '') {
        $erro = 'Informe o nome artístico.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!in_array($status, ['ativo', 'pendente', 'bloqueado'], true)) {
        $erro = 'Status inválido.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE artistas
                 SET nome_artistico = :nome,
                     email = :email,
                     biografia = :biografia,
                     status = :status
                 WHERE id = :id"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email !== '' ? $email : null,
                ':biografia' => $biografia !== '' ? $biografia : null,
                ':status' => $status,
                ':id' => $id
            ]);

            registrarLog(
                $pdo,
                'Edição',
                "Artista atualizado: {$nome}"
            );

            header("Location: artistas.php");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ($e->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                $erro = 'Não foi possível atualizar o artista.';
            }
        }
    }

    $artista = [
        'id' => $id,
        'nome_artistico' => $nome,
        'email' => $email,
        'biografia' => $biografia,
        'status' => $status
    ];

} else {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Artista inválido.');
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM artistas WHERE id = :id"
    );
    $stmt->execute([':id' => $id]);
    $artista = $stmt->fetch();

    if (!$artista) {
        http_response_code(404);
        exit('Artista não encontrado.');
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar artista | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="form-wave-container">
    <div class="form-wave">

        <h1>Editar artista</h1>
        <p>Atualize as informações do artista.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <input type="hidden" name="id"
                   value="<?= e($artista['id']) ?>">

            <label>Nome artístico</label>
            <input
                type="text"
                name="nome_artistico"
                required
                maxlength="150"
                value="<?= e($artista['nome_artistico']) ?>"
            >

            <label>E-mail</label>
            <input
                type="email"
                name="email"
                maxlength="150"
                value="<?= e($artista['email'] ?? '') ?>"
            >

            <label>Biografia</label>
            <textarea
                name="biografia"
                rows="4"
            ><?= e($artista['biografia'] ?? '') ?></textarea>

            <label>Status</label>
            <select name="status" required>
                <option value="pendente"
                    <?= $artista['status'] === 'pendente' ? 'selected' : '' ?>>
                    Pendente
                </option>

                <option value="ativo"
                    <?= $artista['status'] === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>

                <option value="bloqueado"
                    <?= $artista['status'] === 'bloqueado' ? 'selected' : '' ?>>
                    Bloqueado
                </option>
            </select>

            <button type="submit" class="btn-wave w-100">
                Salvar alterações
            </button>

            <a href="artistas.php" class="link-voltar">
                Cancelar e voltar
            </a>

        </form>
    </div>
</div>

</body>
</html>
