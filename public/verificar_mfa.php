
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../app/config/database.php";
require_once "../app/helpers/totp.php";

$erro = "";

// Verifica se existe uma autenticação pendente
if (
    empty($_SESSION["mfa_pendente_id"]) ||
    empty($_SESSION["mfa_pendente_em"])
) {
    header("Location: login.php");
    exit;
}

// Expira a tentativa pendente após 5 minutos
if (time() - (int) $_SESSION["mfa_pendente_em"] > 300) {
    unset($_SESSION["mfa_pendente_id"], $_SESSION["mfa_pendente_em"]);

    header("Location: login.php");
    exit;
}

// Busca novamente o administrador
$stmt = $pdo->prepare("
    SELECT id, nome, ativo, mfa_habilitado, segredo_totp
    FROM administradores
    WHERE id = :id
");

$stmt->execute([
    ":id" => $_SESSION["mfa_pendente_id"]
]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$admin ||
    (int) $admin["ativo"] !== 1 ||
    (int) $admin["mfa_habilitado"] !== 1 ||
    empty($admin["segredo_totp"])
) {
    unset($_SESSION["mfa_pendente_id"], $_SESSION["mfa_pendente_em"]);

    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo = trim($_POST["codigo"] ?? "");

    if (totp_validar($admin["segredo_totp"], $codigo)) {

        session_regenerate_id(true);

        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_nome"] = $admin["nome"];
        $_SESSION["mfa_verificado"] = true;

        unset($_SESSION["mfa_pendente_id"], $_SESSION["mfa_pendente_em"]);

        header("Location: index.php");
        exit;

    } else {
        $erro = "Código incorreto ou expirado. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verificação MFA | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-body">

<div class="login-container">

    <h1 class="wave-logo">WAVE</h1>
    <p class="login-subtitle">Verificação em duas etapas</p>

    <p class="text-center mb-4">
        Digite o código de 6 dígitos gerado pelo seu aplicativo autenticador.
    </p>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-4">
            <label class="form-label" for="codigo">
                Código de autenticação
            </label>

            <input
                type="text"
                id="codigo"
                name="codigo"
                class="form-control"
                placeholder="000000"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                autocomplete="one-time-code"
                required
                autofocus
            >
        </div>

        <button type="submit" class="btn btn-wave w-100">
            Verificar código
        </button>

    </form>

    <div class="text-center mt-3">
        <a href="login.php" class="btn-voltar">
            Voltar ao login
        </a>
    </div>

</div>

</body>
</html>
