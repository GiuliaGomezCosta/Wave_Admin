
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Pesquisa
$busca = trim($_GET['busca'] ?? '');

// Busca os álbuns
$sql = "SELECT
            al.id,
            al.titulo,
            al.data_lancamento,
            al.capa,
            al.artista_id,
            a.nome_artistico
        FROM albuns al
        INNER JOIN artistas a ON al.artista_id = a.id
        WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND (
        al.titulo LIKE :titulo
        OR a.nome_artistico LIKE :artista
    )";

    $params[':titulo'] = "%{$busca}%";
    $params[':artista'] = "%{$busca}%";
}

$sql .= " ORDER BY al.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$albuns = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Álbuns | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Gerenciamento de álbuns</h1>
            <p>Gerencie os álbuns cadastrados na plataforma Wave.</p>
        </div>

        <a href="album_cadastrar.php" class="btn-wave">
            + Novo álbum
        </a>

    </div>

    <div class="usuarios-painel">

        <!-- Mensagens de sucesso e erro -->

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'album_excluido'): ?>

            <div class="alert alert-success">
                Álbum excluído com sucesso!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>

            <?php if ($_GET['erro'] === 'album_vinculado'): ?>

                <div class="alert alert-warning">
                    Não foi possível excluir este álbum porque existem músicas vinculadas a ele.
                    Remova o vínculo das músicas antes de tentar novamente.
                </div>

            <?php elseif ($_GET['erro'] === 'exclusao_falhou'): ?>

                <div class="alert alert-danger">
                    Ocorreu um erro ao tentar excluir o álbum. Tente novamente.
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <!-- Pesquisa -->

        <form method="GET" class="busca-form">

            <input
                type="text"
                name="busca"
                placeholder="Pesquisar álbum ou artista..."
                value="<?= e($busca) ?>"
            >

            <button type="submit" class="btn-wave">
                Pesquisar
            </button>

            <a href="albuns.php" class="btn-voltar">
                Limpar
            </a>

        </form>

        <!-- Tabela de álbuns -->

        <div class="table-responsive">

            <table class="table tabela-wave align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Álbum</th>
                        <th>Artista</th>
                        <th>Data de lançamento</th>
                        <th>Capa</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($albuns): ?>

                    <?php foreach ($albuns as $album): ?>

                        <tr>

                            <td><?= e($album['id']) ?></td>

                            <td><?= e($album['titulo']) ?></td>

                            <td><?= e($album['nome_artistico']) ?></td>

                            <td>
                                <?= $album['data_lancamento']
                                    ? e(date('d/m/Y', strtotime($album['data_lancamento'])))
                                    : '—' ?>
                            </td>

                            <td>
                                <?= !empty($album['capa']) ? 'Cadastrada' : '—' ?>
                            </td>

                            <td>

                                <div class="acoes-wave">

                                    <a
                                        href="album_editar.php?id=<?= e($album['id']) ?>"
                                        class="acao-editar">
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        action="album_excluir.php"
                                        onsubmit="return confirm('Deseja realmente excluir este álbum?');">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrf_token()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= e($album['id']) ?>"
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
                        <td colspan="6" class="text-center">
                            Nenhum álbum encontrado.
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
