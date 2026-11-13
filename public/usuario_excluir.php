
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
    exit('Usuário inválido.');
}

$stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(404);
    exit('Usuário não encontrado.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);

    registrarLog(
        $pdo,
        'Exclusão',
        "Usuário excluído: {$usuario['email']}"
    );

    header("Location: usuarios.php");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Não foi possível excluir o usuário.');
}
