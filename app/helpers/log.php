
<?php

function registrarLog(PDO $pdo, string $acao, string $descricao): void
{
    $sql = "INSERT INTO logs
            (administrador_id, acao, descricao, ip)
            VALUES (:administrador_id, :acao, :descricao, :ip)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':administrador_id' => $_SESSION['admin_id'] ?? null,
        ':acao' => $acao,
        ':descricao' => $descricao,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}
