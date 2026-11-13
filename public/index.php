
<?php

require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";

// INDICADORES DO DASHBOARD

$totalUsuarios = $pdo->query(
    "SELECT COUNT(*) FROM usuarios"
)->fetchColumn();

$totalArtistas = $pdo->query(
    "SELECT COUNT(*) FROM artistas"
)->fetchColumn();

$totalMusicas = $pdo->query(
    "SELECT COUNT(*) FROM musicas"
)->fetchColumn();

$totalReproducoes = $pdo->query(
    "SELECT COALESCE(SUM(reproducoes), 0) FROM musicas"
)->fetchColumn();

// ÚLTIMAS MÚSICAS CADASTRADAS

$sqlMusicas = "
    SELECT
        m.id,
        m.titulo,
        m.reproducoes,
        m.status,
        a.nome_artistico AS artista
    FROM musicas m
    INNER JOIN artistas a ON m.artista_id = a.id
    ORDER BY m.criada_em DESC
    LIMIT 5
";

$ultimasMusicas = $pdo->query($sqlMusicas)->fetchAll();

// REPRODUÇÕES DOS ÚLTIMOS 7 DIAS
// Observação: usa o total acumulado cadastrado por música.
// Não representa reproduções diárias reais.

$sqlGrafico = "
    SELECT
        titulo,
        reproducoes
    FROM musicas
    ORDER BY reproducoes DESC
    LIMIT 7
";

$dadosGrafico = $pdo->query($sqlGrafico)->fetchAll();

$nomesGrafico = array_column($dadosGrafico, "titulo");
$valoresGrafico = array_map(
    "intval",
    array_column($dadosGrafico, "reproducoes")
);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            WAVE
            <span>ADMIN</span>
        </div>

        <nav class="sidebar-menu">

            <a href="index.php" class="menu-item active">
                <span>▦</span> Dashboard
            </a>

            <a href="usuarios.php" class="menu-item">
                <span>♙</span> Usuários
            </a>

            <a href="artistas.php" class="menu-item">
                <span>♫</span> Artistas
            </a>

            <a href="musicas.php" class="menu-item">
                <span>♪</span> Músicas
            </a>

            <a href="albuns.php" class="menu-item">
                <span>▣</span> Álbuns
            </a>

            <a href="generos.php" class="menu-item">
                <span>♬</span> Gêneros
            </a>

        </nav>

        <div class="sidebar-footer">

            <a href="logout.php" class="logout-button">
                ⇥ Sair do sistema
            </a>

        </div>

    </aside>


    <!-- CONTEÚDO PRINCIPAL -->

    <main class="main-content">

        <!-- CABEÇALHO -->

        <header class="topbar">

            <div>
                <h2>Dashboard</h2>
                <p>Painel de controle da plataforma Wave</p>
            </div>

            <div class="admin-info">
                <div class="admin-avatar">
                    <?= strtoupper(substr(
                        $_SESSION["admin_nome"], 0, 1
                    )) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION["admin_nome"]
                        ) ?>
                    </strong>

                    <small>Administrador</small>
                </div>
            </div>

        </header>


        <!-- BOAS-VINDAS -->

        <section class="welcome-section">

            <h1>Bem-vindo ao Wave Admin!</h1>

            <p>
                Gerencie sua plataforma musical em um só lugar.
            </p>

        </section>


        <!-- CARDS DE INDICADORES -->

        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">♙</div>
                <p>Total de usuários</p>
                <h3>
                    <?= number_format(
                        (int)$totalUsuarios,
                        0,
                        ',',
                        '.'
                    ) ?>
                </h3>
            </div>

            <div class="stat-card">
                <div class="stat-icon">♫</div>
                <p>Total de artistas</p>
                <h3>
                    <?= number_format(
                        (int)$totalArtistas,
                        0,
                        ',',
                        '.'
                    ) ?>
                </h3>
            </div>

            <div class="stat-card">
                <div class="stat-icon">♪</div>
                <p>Total de músicas</p>
                <h3>
                    <?= number_format(
                        (int)$totalMusicas,
                        0,
                        ',',
                        '.'
                    ) ?>
                </h3>
            </div>

            <div class="stat-card">
                <div class="stat-icon">▷</div>
                <p>Total de reproduções</p>
                <h3>
                    <?= number_format(
                        (int)$totalReproducoes,
                        0,
                        ',',
                        '.'
                    ) ?>
                </h3>
            </div>

        </section>


        <!-- GRÁFICO -->

        <section class="content-card">

            <div class="section-heading">
                <h3>Músicas mais reproduzidas</h3>
                <span>Dados cadastrados</span>
            </div>

            <div class="chart-container">
                <canvas id="musicChart"></canvas>
            </div>

        </section>


        <!-- TABELA DE MÚSICAS -->

        <section class="content-card">

            <div class="section-heading">
                <h3>Últimas músicas cadastradas</h3>
                <a href="musicas.php">Ver todas</a>
            </div>

            <div class="table-responsive">

                <table class="table wave-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Música</th>
                            <th>Artista</th>
                            <th>Reproduções</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($ultimasMusicas) > 0): ?>

                        <?php foreach ($ultimasMusicas as $musica): ?>

                            <tr>

                                <td>
                                    <?= (int)$musica["id"] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $musica["titulo"]
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $musica["artista"]
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (int)$musica["reproducoes"],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status-badge">
                                        <?= htmlspecialchars(
                                            ucfirst($musica["status"])
                                        ) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-message">
                                Nenhuma música cadastrada.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer class="dashboard-footer">
            Wave Admin &copy; <?= date("Y") ?> -
            Todos os direitos reservados.
        </footer>

    </main>

</div>


<!-- CHART.JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const musicLabels = <?= json_encode(
        $nomesGrafico,
        JSON_HEX_TAG | JSON_HEX_APOS |
        JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    const musicValues = <?= json_encode(
        $valoresGrafico
    ) ?>;
</script>

<script src="assets/js/dashboard.js"></script>

</body>
</html>
