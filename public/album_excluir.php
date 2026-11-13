
<?php
require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/log.php";

// Permite exclusão somente por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: albuns.php");
    exit;
}

// Valida o token de segurança
csrf_validate();

// Recebe o ID do álbum
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: albuns.php");
    exit;
}

// Busca o álbum
$stmt = $pdo->prepare("
    SELECT id, titulo, capa
    FROM albuns
    WHERE id = :id
");
$stmt->execute([':id' => $id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    header("Location: albuns.php");
    exit;
}

// Verifica se existem músicas vinculadas ao álbum
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM musicas
    WHERE album_id = :id
");
$stmt->execute([':id' => $id]);

$totalMusicas = (int) $stmt->fetchColumn();

if ($totalMusicas > 0) {
    header("Location: albuns.php?erro=album_vinculado");
    exit;
}

try {
    // Exclui o álbum do banco de dados
    $stmt = $pdo->prepare("
        DELETE FROM albuns
        WHERE id = :id
    ");

    $stmt->execute([':id' => $id]);

    // Registra a ação no histórico
    registrarLog(
        $pdo,
        'Exclusão de álbum',
        'Álbum excluído: ' . $album['titulo']
    );

    // Remove a capa, se estiver dentro da pasta correta
    $capa = $album['capa'];

    if (
        !empty($capa) &&
        strpos($capa, 'uploads/albuns/') === 0
    ) {
        $pastaUploads = realpath(__DIR__ . '/uploads/albuns');
        $arquivoCapa = realpath(__DIR__ . '/' . $capa);

        if (
            $pastaUploads !== false &&
            $arquivoCapa !== false &&
            strpos(
                $arquivoCapa,
                $pastaUploads . DIRECTORY_SEPARATOR
            ) === 0 &&
            is_file($arquivoCapa)
        ) {
            unlink($arquivoCapa);
        }
    }

    header("Location: albuns.php?sucesso=album_excluido");
    exit;

} catch (PDOException $e) {
    header("Location: albuns.php?erro=exclusao_falhou");
    exit;
}
