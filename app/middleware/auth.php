
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Confere se a sessão passou pela segunda etapa
if (
    isset($_SESSION["mfa_verificado"]) &&
    $_SESSION["mfa_verificado"] !== true
) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
