
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";

$pesquisa = trim($_GET['busca'] ?? '');

$sql = "SELECT id, nome, email, status, criado_em FROM usuarios";

if ($pesquisa !== '') {
    $sql .= " WHERE nome LIKE :busca OR email LIKE :busca";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

if ($pesquisa !== '') {
    $stmt->bindValue(':busca', "%{$pesquisa}%");
}

$stmt->execute();
$usuarios = $stmt->fetchAll();

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">
        <div>
            <h1>Gerenciamento de usuários</h1>
            <p>Cadastre, consulte e gerencie os usuários da plataforma.</p>
        </div>

        <a href="usuario_cadastrar.php" class="btn-wave">
            + Novo usuário
        </a>
    </div>

    <div class="usuarios-painel">

        <form method="GET" class="busca-form">
            <input
                type="text"
                name="busca"
                placeholder="Pesquisar por nome ou e-mail..."
                value="<?= e($pesquisa) ?>"
            >

            <button type="submit" class="btn-wave">Pesquisar</button>
            <a href="usuarios.php" class="btn-voltar">Limpar</a>
        </form>

        <div class="table-responsive">
            <table class="table tabela-wave align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($usuarios) > 0): ?>

                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= e($usuario['id']) ?></td>
                        <td><?= e($usuario['nome']) ?></td>
                        <td><?= e($usuario['email']) ?></td>

                        <td>
                            <span class="status-wave <?= $usuario['status'] === 'ativo' ? 'ativo' : 'bloqueado' ?>">
                                <?= e(ucfirst($usuario['status'])) ?>
                            </span>
                        </td>

                        <td>
                            <?= e(date('d/m/Y', strtotime($usuario['criado_em']))) ?>
                        </td>

                        <td>
                            <div class="acoes-wave">

                                <a
                                    href="usuario_editar.php?id=<?= e($usuario['id']) ?>"
                                    class="acao-editar"
                                >
                                    Editar
                                </a>

                                <form method="POST" action="usuario_editar.php">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($usuario['id']) ?>">
                                    <input type="hidden" name="operacao" value="alternar_status">

                                    <button type="submit" class="acao-status">
                                        <?= $usuario['status'] === 'ativo' ? 'Bloquear' : 'Ativar' ?>
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="usuario_excluir.php"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($usuario['id']) ?>">

                                    <button type="submit" class="acao-excluir">
                                        Excluir
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn-voltar">← Voltar ao dashboard</a>

    </div>
</div>

</body>
</html>
