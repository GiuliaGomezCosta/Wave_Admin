
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../app/config/database.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $sql = "SELECT *
            FROM administradores
            WHERE email = :email AND ativo = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($senha, $admin["senha"])) {

        session_regenerate_id(true);

        // Se o MFA estiver habilitado, exige o código
        if ((int) $admin["mfa_habilitado"] === 1) {

            if (empty($admin["segredo_totp"])) {
                $erro = "MFA habilitado sem segredo configurado. Procure o administrador do sistema.";
            } else {
                $_SESSION["mfa_pendente_id"] = $admin["id"];
                $_SESSION["mfa_pendente_em"] = time();

                unset($_SESSION["admin_id"], $_SESSION["admin_nome"]);

                header("Location: verificar_mfa.php");
                exit;
            }

        } else {

            // Login normal, quando o MFA está desabilitado
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nome"] = $admin["nome"];

            unset($_SESSION["mfa_pendente_id"], $_SESSION["mfa_pendente_em"]);

            header("Location: index.php");
            exit;
        }

    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-body">

<div class="login-container">

    <h1 class="wave-logo">WAVE</h1>
    <p class="login-subtitle">Painel administrativo</p>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">E-mail</label>

            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="Digite seu e-mail"
                   autocomplete="username"
                   required>
        </div>

        <div class="mb-4">
            <label class="form-label">Senha</label>

            <input type="password"
                   name="senha"
                   class="form-control"
                   placeholder="Digite sua senha"
                   autocomplete="current-password"
                   required>
        </div>

        <button type="submit"
                class="btn btn-wave w-100">
            Entrar no painel
        </button>

    </form>

</div>

</body>
</html>

