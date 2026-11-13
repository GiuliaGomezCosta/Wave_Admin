
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

// Exclusão somente por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: generos.php");
    exit;
}

csrf_validate();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: generos.php");
    exit;
}

// Busca o gênero
$stmt = $pdo->prepare("
    SELECT id, nome
    FROM generos
    WHERE id = :id
");

$stmt->execute([':id' => $id]);

$genero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$genero) {
    header("Location: generos.php");
    exit;
}

// Verifica se existem músicas vinculadas
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM musicas
    WHERE genero_id = :id
");

$stmt->execute([':id' => $id]);

$totalMusicas = (int) $stmt->fetchColumn();

if ($totalMusicas > 0) {
    header("Location: generos.php?erro=genero_vinculado");
    exit;
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM generos
        WHERE id = :id
    ");

    $stmt->execute([':id' => $id]);

    registrarLog(
        $pdo,
        'Exclusão de gênero',
        'Gênero excluído: ' . $genero['nome']
    );

    header("Location: generos.php?sucesso=genero_excluido");
    exit;

} catch (PDOException $e) {
    header("Location: generos.php?erro=exclusao_falhou");
    exit;
}
