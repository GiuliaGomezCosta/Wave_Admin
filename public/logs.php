
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Filtros
$busca = trim($_GET['busca'] ?? '');
$acaoSelecionada = trim($_GET['acao'] ?? '');
$dataInicio = trim($_GET['data_inicio'] ?? '');
$dataFim = trim($_GET['data_fim'] ?? '');

// Validação das datas
function dataValida(string $data): bool {
    $partes = explode('-', $data);

    return count($partes) === 3
        && checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
}

if ($dataInicio !== '' && !dataValida($dataInicio)) {
    $dataInicio = '';
}

if ($dataFim !== '' && !dataValida($dataFim)) {
    $dataFim = '';
}

// Paginação
$porPagina = 20;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);

if (!$pagina || $pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $porPagina;

// Condições da consulta
$where = " WHERE 1=1 ";
$params = [];

if ($busca !== '') {
    $where .= " AND (
        l.acao LIKE :acao_busca
        OR l.descricao LIKE :descricao_busca
        OR l.ip LIKE :ip_busca
        OR adm.nome LIKE :nome_busca
        OR adm.email LIKE :email_busca
    ) ";

    $termo = "%{$busca}%";

    $params[':acao_busca'] = $termo;
    $params[':descricao_busca'] = $termo;
    $params[':ip_busca'] = $termo;
    $params[':nome_busca'] = $termo;
    $params[':email_busca'] = $termo;
}

if ($acaoSelecionada !== '') {
    $where .= " AND l.acao = :acao_filtro ";
    $params[':acao_filtro'] = $acaoSelecionada;
}

if ($dataInicio !== '') {
    $where .= " AND l.criado_em >= :data_inicio ";
    $params[':data_inicio'] = $dataInicio . ' 00:00:00';
}

if ($dataFim !== '') {
    $where .= " AND l.criado_em <= :data_fim ";
    $params[':data_fim'] = $dataFim . ' 23:59:59';
}

// Opções de ações existentes
$stmtAcoes = $pdo->query("
    SELECT DISTINCT acao
    FROM logs
    WHERE acao IS NOT NULL
    ORDER BY acao ASC
");

$acoes = $stmtAcoes->fetchAll(PDO::FETCH_COLUMN);

// Total de registros
$sqlTotal = "
    SELECT COUNT(*)
    FROM logs l
    LEFT JOIN administradores adm
        ON l.administrador_id = adm.id
    $where
";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);

$totalRegistros = (int) $stmtTotal->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
    $offset = ($pagina - 1) * $porPagina;
}

// Busca os registros
$sql = "
    SELECT
        l.id,
        l.acao,
        l.descricao,
        l.ip,
        l.criado_em,
        l.administrador_id,
        adm.nome AS administrador_nome,
        adm.email AS administrador_email
    FROM logs l
    LEFT JOIN administradores adm
        ON l.administrador_id = adm.id
    $where
    ORDER BY l.id DESC
    LIMIT :limite OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}

$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mantém os filtros na paginação
$filtros = [
    'busca' => $busca,
    'acao' => $acaoSelecionada,
    'data_inicio' => $dataInicio,
    'data_fim' => $dataFim
];

$queryFiltros = http_build_query($filtros);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logs administrativos | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">

        <div>
            <h1>Logs administrativos</h1>
            <p>Acompanhe as ações realizadas na plataforma Wave.</p>
        </div>

    </div>

    <div class="usuarios-painel">

        <!-- Resumo -->

        <div class="mb-4">
            <h5>
                Total de registros:
                <strong><?= e($totalRegistros) ?></strong>
            </h5>
        </div>

        <!-- Filtros -->

        <form method="GET" class="busca-form">

            <input
                type="text"
                name="busca"
                placeholder="Pesquisar ação, descrição, IP ou administrador..."
                value="<?= e($busca) ?>"
            >

            <select name="acao">

                <option value="">Todas as ações</option>

                <?php foreach ($acoes as $acao): ?>

                    <option
                        value="<?= e($acao) ?>"
                        <?= $acaoSelecionada === $acao ? 'selected' : '' ?>
                    >
                        <?= e($acao) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <input
                type="date"
                name="data_inicio"
                value="<?= e($dataInicio) ?>"
                title="Data inicial"
            >

            <input
                type="date"
                name="data_fim"
                value="<?= e($dataFim) ?>"
                title="Data final"
            >

            <button type="submit" class="btn-wave">
                Filtrar
            </button>

            <a href="logs.php" class="btn-voltar">
                Limpar
            </a>

        </form>

        <!-- Tabela -->

        <div class="table-responsive">

            <table class="table tabela-wave align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Administrador</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Data e horário</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($logs): ?>

                    <?php foreach ($logs as $log): ?>

                        <tr>

                            <td><?= e($log['id']) ?></td>

                            <td>
                                <?= !empty($log['administrador_nome'])
                                    ? e($log['administrador_nome'])
                                    : 'Administrador não identificado' ?>
                            </td>

                            <td>
                                <?= e($log['acao']) ?>
                            </td>

                            <td>
                                <?= e($log['descricao']) ?>
                            </td>

                            <td>
                                <?= e($log['ip'] ?: '—') ?>
                            </td>

                            <td>
                                <?= $log['criado_em']
                                    ? e(date(
                                        'd/m/Y H:i:s',
                                        strtotime($log['criado_em'])
                                    ))
                                    : '—' ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center">
                            Nenhum registro encontrado.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- Paginação -->

        <?php if ($totalPaginas > 1): ?>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">

                <span>
                    Página <?= e($pagina) ?> de <?= e($totalPaginas) ?>
                </span>

                <div class="d-flex gap-2">

                    <?php if ($pagina > 1): ?>

                        <a
                            href="?<?= e($queryFiltros) ?>&pagina=<?= $pagina - 1 ?>"
                            class="btn-voltar">
                            ← Anterior
                        </a>

                    <?php endif; ?>

                    <?php if ($pagina < $totalPaginas): ?>

                        <a
                            href="?<?= e($queryFiltros) ?>&pagina=<?= $pagina + 1 ?>"
                            class="btn-wave">
                            Próxima →
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

        <a href="index.php" class="btn-voltar">
            ← Voltar ao dashboard
        </a>

    </div>

</div>

</body>
</html>
