
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once "../app/middleware/auth.php";
require_once "../app/config/database.php";
require_once "../app/middleware/csrf.php";
require_once "../app/helpers/totp.php";
require_once "../app/helpers/log.php";

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$id = $_SESSION["admin_id"];

$stmt = $pdo->prepare("
    SELECT id, nome, email, mfa_habilitado, segredo_totp
    FROM administradores
    WHERE id = :id AND ativo = 1
");

$stmt->execute([":id" => $id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$erro = "";
$sucesso = "";

// Se o MFA ainda não estiver ativado
if ((int) $admin["mfa_habilitado"] !== 1) {

    // Gera segredo somente se ainda não existir
    if (empty($admin["segredo_totp"])) {

        $segredo = totp_gerar_segredo();

        $stmt = $pdo->prepare("
            UPDATE administradores
            SET segredo_totp = :segredo
            WHERE id = :id
        ");

        $stmt->execute([
            ":segredo" => $segredo,
            ":id" => $id
        ]);

        $admin["segredo_totp"] = $segredo;
    }

    // Processa formulário
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        csrf_validate();

        $codigo = trim($_POST["codigo"] ?? "");

        if (totp_validar($admin["segredo_totp"], $codigo)) {

            $stmt = $pdo->prepare("
                UPDATE administradores
                SET mfa_habilitado = 1
                WHERE id = :id
            ");

            $stmt->execute([":id" => $id]);

            registrarLog(
                $pdo,
                'Ativação de MFA',
                'Autenticação em duas etapas ativada.'
            );

            $sucesso = "MFA ativado com sucesso! No próximo login será solicitado o código.";

            $admin["mfa_habilitado"] = 1;

        } else {

            $erro = "Código incorreto. Confira o aplicativo autenticador e tente novamente.";

        }
    }
}

// Gera código temporário para comparação
$codigoTeste = "";

if (
    (int) $admin["mfa_habilitado"] !== 1 &&
    !empty($admin["segredo_totp"])
) {
    $codigoTeste = totp_gerar_codigo($admin["segredo_totp"]);
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Configurar MFA | Wave Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">

    <meta http-equiv="refresh" content="30">

</head>

<body class="usuarios-page">

<div class="usuarios-container">

    <div class="usuarios-topo">
        <div>
            <h1>Autenticação em duas etapas</h1>
            <p>Proteja sua conta administrativa com um código temporário.</p>
        </div>
    </div>

    <div class="usuarios-painel">

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= e($sucesso) ?>
            </div>
        <?php endif; ?>

        <?php if ((int) $admin["mfa_habilitado"] !== 1): ?>

            <h5>1. Configure seu aplicativo autenticador</h5>

            <p>
                Abra o Google Authenticator, Microsoft Authenticator
                ou outro aplicativo compatível com TOTP.
            </p>

            <p>
                Escolha a opção para adicionar uma conta manualmente
                e informe esta chave:
            </p>

            <div class="alert alert-info">
                <strong>Chave secreta:</strong>
                <br>
                <code><?= e($admin["segredo_totp"]) ?></code>
            </div>

            <div class="alert alert-warning">

                <h5>Teste temporário do servidor</h5>

                <p>
                    Código gerado pelo PHP:
                </p>

                <h2 style="letter-spacing: 5px;">
                    <?= e($codigoTeste) ?>
                </h2>

                <p class="mb-0">
                    Compare este código com o exibido no aplicativo autenticador.
                    A página será atualizada a cada 30 segundos.
                </p>

            </div>

            <p class="text-muted">
                Configure o tipo de código como baseado em tempo (TOTP),
                com 6 dígitos e intervalo de 30 segundos.
            </p>

            <hr>

            <h5>2. Confirme a configuração</h5>

            <p>
                Digite o código de 6 dígitos exibido no aplicativo
                para confirmar a ativação.
            </p>

            <form method="POST" class="form-wave">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <div class="mb-3">

                    <label for="codigo" class="form-label">
                        Código de autenticação
                    </label>

                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        class="form-control"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        placeholder="000000"
                        required
                    >

                </div>

                <button type="submit" class="btn-wave">
                    Ativar MFA
                </button>

            </form>

        <?php else: ?>

            <p>
                Sua conta está protegida pela autenticação em duas etapas.
            </p>

        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="btn-voltar">
                ← Voltar ao dashboard
            </a>
        </div>

    </div>

</div>

</body>
</html>
