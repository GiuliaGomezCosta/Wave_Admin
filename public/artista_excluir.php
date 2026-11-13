
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

csrf_validate();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Artista inválido.');
}

// Verifica se o artista existe
$stmt = $pdo->prepare(
    "SELECT nome_artistico, email
     FROM artistas
     WHERE id = :id"
);

$stmt->execute([':id' => $id]);
$artista = $stmt->fetch();

if (!$artista) {
    http_response_code(404);
    exit('Artista não encontrado.');
}

try {
    $stmt = $pdo->prepare(
        "DELETE FROM artistas WHERE id = :id"
    );

    $stmt->execute([':id' => $id]);

    registrarLog(
        $pdo,
        'Exclusão',
        "Artista excluído: {$artista['nome_artistico']} ({$artista['email']})"
    );

    header("Location: artistas.php");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit(
        'Não foi possível excluir o artista. ' .
        'Verifique se existem álbuns ou músicas vinculados a ele.'
    );
}
