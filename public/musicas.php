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

$sql = "SELECT
            m.id,
            m.titulo,
            m.duracao,
            m.reproducoes,
            m.status,
            m.criada_em,
            a.nome_artistico,
            al.titulo AS album_titulo,
            g.nome AS genero_nome
        FROM musicas m
        INNER JOIN artistas a ON m.artista_id = a.id
        LEFT JOIN albuns al ON m.album_id = al.id
        LEFT JOIN generos g ON m.genero_id = g.id
        WHERE 1=1";

$params = [];

// Pesquisa por título ou artista
if ($busca !== '') {
    $sql .= " AND (
        m.titulo LIKE :titulo
        OR a.nome_artistico LIKE :artista
    )";

    $params[':titulo'] = "%{$busca}%";
    $params[':artista'] = "%{$busca}%";
}

// Filtro de status
if (in_array($status, ['publicada', 'pendente', 'removida'], true)) {
    $sql .= " AND m.status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$musicas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Músicas | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Gerenciamento de músicas</h1>
            <p>Gerencie as músicas cadastradas na plataforma Wave.</p>
        </div>

        <a href="musica_cadastrar.php" class="btn-wave">
            + Nova música
        </a>

    </div>

    <div class="usuarios-painel">

        <!-- Pesquisa e filtros -->

        <form method="GET" class="busca-form">

            <input
                type="text"
                name="busca"
                placeholder="Pesquisar música ou artista..."
                value="<?= e($busca) ?>"
            >

            <select name="status">

                <option value="">Todos os status</option>

                <option value="publicada"
                    <?= $status === 'publicada' ? 'selected' : '' ?>>
                    Publicada
                </option>

                <option value="pendente"
                    <?= $status === 'pendente' ? 'selected' : '' ?>>
                    Pendente
                </option>

                <option value="removida"
                    <?= $status === 'removida' ? 'selected' : '' ?>>
                    Removida
                </option>

            </select>

            <button type="submit" class="btn-wave">
                Pesquisar
            </button>

            <a href="musicas.php" class="btn-voltar">
                Limpar
            </a>

        </form>

        <!-- Tabela de músicas -->

        <div class="table-responsive">

            <table class="table tabela-wave align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Música</th>
                        <th>Artista</th>
                        <th>Álbum</th>
                        <th>Gênero</th>
                        <th>Duração</th>
                        <th>Reproduções</th>
                        <th>Status</th>
                        <th>Data de cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($musicas): ?>

                    <?php foreach ($musicas as $musica): ?>

                        <tr>

                            <td><?= e($musica['id']) ?></td>

                            <td><?= e($musica['titulo']) ?></td>

                            <td><?= e($musica['nome_artistico']) ?></td>

                            <td>
                                <?= e($musica['album_titulo'] ?: '—') ?>
                            </td>

                            <td>
                                <?= e($musica['genero_nome'] ?: '—') ?>
                            </td>

                            <td>
                                <?= e($musica['duracao'] ?: '—') ?>
                            </td>

                            <td>
                                <?= e(number_format((int)$musica['reproducoes'], 0, ',', '.')) ?>
                            </td>

                            <td>
                                <span class="status-wave <?= e($musica['status']) ?>">
                                    <?= e(ucfirst($musica['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= $musica['criada_em']
                                    ? e(date('d/m/Y', strtotime($musica['criada_em'])))
                                    : '—' ?>
                            </td>

                            <td>

                                <div class="acoes-wave">

                                    <a
                                        href="musica_editar.php?id=<?= e($musica['id']) ?>"
                                        class="acao-editar">
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        action="musica_excluir.php"
                                        onsubmit="return confirm('Deseja realmente excluir esta música?');">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrf_token()) ?>">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= e($musica['id']) ?>">

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
                        <td colspan="10" class="text-center">
                            Nenhuma música encontrada.
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