<?php
session_start();
include 'conexao.php';
require_once __DIR__ . '/lib/TwoFactorAuth/TwoFactorAuth.php';
use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION['usuario_temp_id'])) {
    header("Location: LoginMain.php");
    exit;
}

$erro = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $usuario_id = $_SESSION['usuario_temp_id'];

    $sql = "SELECT id, nome, segredo_2fa FROM usuarios WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    $tfa = new TwoFactorAuth('Adote CCZ');

    // Valida o token de 6 dígitos contra o segredo salvo do usuário
    if ($tfa->verifyCode($usuario['segredo_2fa'], $codigo)) {
        // Token correto! Autentica a sessão definitivamente
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        unset($_SESSION['usuario_temp_id']); // Limpa a sessão temporária

        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Código inválido ou expirado. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Validação 2FA - Adote CCZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-sm" style="width: 380px;">
        <h4 class="text-center mb-3">Autenticador</h4>
        <p class="text-muted text-center small">Abra o <strong>Google Authenticator</strong> ou <strong>Microsoft Authenticator</strong> no seu celular e insira o código atual.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="codigo" class="form-control text-center fs-3 tracking-widest" maxlength="6" autofocus required placeholder="000 000" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</body>
</html>