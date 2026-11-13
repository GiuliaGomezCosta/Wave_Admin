
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Pesquisa
$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT id, nome FROM generos WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND nome LIKE :nome";
    $params[':nome'] = "%{$busca}%";
}

$sql .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$generos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gêneros musicais | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Gerenciamento de gêneros</h1>
            <p>Gerencie os gêneros musicais cadastrados na plataforma Wave.</p>
        </div>

        <a href="genero_cadastrar.php" class="btn-wave">
            + Novo gênero
        </a>

    </div>

    <div class="usuarios-painel">

        <!-- Mensagens de sucesso e erro -->

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'genero_excluido'): ?>

            <div class="alert alert-success">
                Gênero excluído com sucesso!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>

            <?php if ($_GET['erro'] === 'genero_vinculado'): ?>

                <div class="alert alert-warning">
                    Não foi possível excluir este gênero porque existem músicas vinculadas a ele.
                </div>

            <?php elseif ($_GET['erro'] === 'exclusao_falhou'): ?>

                <div class="alert alert-danger">
                    Ocorreu um erro ao tentar excluir o gênero.
                </div>

            <?php elseif ($_GET['erro'] === 'duplicado'): ?>

                <div class="alert alert-danger">
                    Já existe um gênero com esse nome.
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <!-- Pesquisa -->

        <form method="GET" class="busca-form">

            <input
                type="text"
                name="busca"
                placeholder="Pesquisar gênero musical..."
                value="<?= e($busca) ?>"
            >

            <button type="submit" class="btn-wave">
                Pesquisar
            </button>

            <a href="generos.php" class="btn-voltar">
                Limpar
            </a>

        </form>

        <!-- Tabela de gêneros -->

        <div class="table-responsive">

            <table class="table tabela-wave align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do gênero</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($generos): ?>

                    <?php foreach ($generos as $genero): ?>

                        <tr>

                            <td><?= e($genero['id']) ?></td>

                            <td><?= e($genero['nome']) ?></td>

                            <td>

                                <div class="acoes-wave">

                                    <a
                                        href="genero_editar.php?id=<?= e($genero['id']) ?>"
                                        class="acao-editar">
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        action="genero_excluir.php"
                                        onsubmit="return confirm('Deseja realmente excluir este gênero?');">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrf_token()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= e($genero['id']) ?>"
                                        >

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
                        <td colspan="3" class="text-center">
                            Nenhum gênero encontrado.
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
