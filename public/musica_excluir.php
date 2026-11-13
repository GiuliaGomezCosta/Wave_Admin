<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

// Aceitar somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: musicas.php');
    exit;
}

// Validar segurança
csrf_validate();

// Validar ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: musicas.php');
    exit;
}

// Buscar música
$stmt = $pdo->prepare(
    "SELECT id, titulo FROM musicas WHERE id = :id"
);

$stmt->execute([':id' => $id]);

$musica = $stmt->fetch();

if (!$musica) {
    header('Location: musicas.php');
    exit;
}

try {

    // Excluir música
    $stmt = $pdo->prepare(
        "DELETE FROM musicas WHERE id = :id"
    );

    $stmt->execute([':id' => $id]);

    // Registrar log
    registrarLog(
        $pdo,
        'Exclusão de música',
        'Música excluída: ' . $musica['titulo'] . ' (ID: ' . $id . ')'
    );

    header('Location: musicas.php');
    exit;

} catch (PDOException $ex) {

    http_response_code(500);

    echo "Não foi possível excluir esta música. Verifique se existem relacionamentos no banco de dados.";
    exit;
}