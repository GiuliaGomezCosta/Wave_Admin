
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Pesquisa
$busca = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';

$sql = "SELECT id, nome_artistico, email, biografia, status, criado_em
        FROM artistas
        WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND (
        nome_artistico LIKE :nome
        OR email LIKE :email
    )";

    $params[':nome'] = "%{$busca}%";
    $params[':email'] = "%{$busca}%";
}

// Filtro de status
if (in_array($status, ['ativo', 'pendente', 'bloqueado'], true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$artistas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artistas | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Gerenciamento de artistas</h1>
            <p>Gerencie os artistas cadastrados na plataforma Wave.</p>
        </div>

        <a href="artista_cadastrar.php" class="btn-wave">
            + Novo artista
        </a>

    </div>

    <div class="usuarios-painel">

        <!-- Pesquisa e filtros -->

        <form method="GET" class="busca-form">

            <input
                type="text"
                name="busca"
                placeholder="Pesquisar artista ou e-mail..."
                value="<?= e($busca) ?>"
            >

            <select name="status">
                <option value="">Todos os status</option>

                <option value="ativo"
                    <?= $status === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>

                <option value="pendente"
                    <?= $status === 'pendente' ? 'selected' : '' ?>>
                    Pendente
                </option>

                <option value="bloqueado"
                    <?= $status === 'bloqueado' ? 'selected' : '' ?>>
                    Bloqueado
                </option>
            </select>

            <button type="submit" class="btn-wave">
                Pesquisar
            </button>

            <a href="artistas.php" class="btn-voltar">
                Limpar
            </a>

        </form>

        <!-- Tabela de artistas -->

        <div class="table-responsive">

            <table class="table tabela-wave align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome artístico</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Data de cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($artistas): ?>

                    <?php foreach ($artistas as $artista): ?>

                        <tr>

                            <td><?= e($artista['id']) ?></td>

                            <td><?= e($artista['nome_artistico']) ?></td>

                            <td><?= e($artista['email'] ?: '—') ?></td>

                            <td>
                                <span class="status-wave <?= e($artista['status']) ?>">
                                    <?= e(ucfirst($artista['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= $artista['criado_em']
                                    ? e(date('d/m/Y', strtotime($artista['criado_em'])))
                                    : '—' ?>
                            </td>

                            <td>

                                <div class="acoes-wave">

                                    <a
                                        href="artista_editar.php?id=<?= e($artista['id']) ?>"
                                        class="acao-editar">
                                        Editar
                                    </a>

                                    <?php if ($artista['status'] === 'pendente'): ?>

                                        <form method="POST" action="artista_editar.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id"
                                                value="<?= e($artista['id']) ?>">
                                            <input type="hidden" name="operacao"
                                                value="alternar_status">

                                            <button type="submit" class="acao-status">
                                                Aprovar
                                            </button>
                                        </form>

                                    <?php elseif ($artista['status'] === 'ativo'): ?>

                                        <form method="POST" action="artista_editar.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id"
                                                value="<?= e($artista['id']) ?>">
                                            <input type="hidden" name="operacao"
                                                value="alternar_status">

                                            <button type="submit" class="acao-status">
                                                Bloquear
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <form method="POST" action="artista_editar.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id"
                                                value="<?= e($artista['id']) ?>">
                                            <input type="hidden" name="operacao"
                                                value="alternar_status">

                                            <button type="submit" class="acao-status">
                                                Ativar
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                    <form method="POST"
                                          action="artista_excluir.php"
                                          onsubmit="return confirm('Deseja realmente excluir este artista?');">

                                        <input type="hidden" name="csrf_token"
                                            value="<?= e(csrf_token()) ?>">

                                        <input type="hidden" name="id"
                                            value="<?= e($artista['id']) ?>">

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
                            Nenhum artista encontrado.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <a href="index.php" class="btn-voltar">
            ← Voltar ao dashboard
        </a>

    </div>

</div>

</body>
</html>
