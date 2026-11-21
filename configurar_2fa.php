<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado usando a sessão padrão do seu sistema
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
    header("Location: LoginMain.php");
    exit;
}

// Autoloader da biblioteca TwoFactorAuth
spl_autoload_register(function ($class) {
    $prefix = 'RobThree\\Auth\\';
    $base_dir = __DIR__ . '/TwoFactorAuth/lib/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

$usuario_id = $_SESSION['id'];
$qrProvider = new QRServerProvider(verifyssl: false);
$tfa = new TwoFactorAuth($qrProvider, 'Adote CCZ');

$erro = "";
$sucesso = "";

// Busca dados atuais do usuário na tabela Usuarios
$sql = "SELECT email, segredo_2fa, status_2fa FROM Usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Processa formulários (Ativar ou Desativar 2FA)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'ativar') {
        $codigo = preg_replace('/\s+/', '', $_POST['codigo_2fa']);

        if ($tfa->verifyCode($usuario['segredo_2fa'], $codigo)) {
            $sql_ativa = "UPDATE Usuarios SET status_2fa = 'ativo' WHERE id = ?";
            $stmt_at = $conexao->prepare($sql_ativa);
            $stmt_at->bind_param("i", $usuario_id);
            $stmt_at->execute();

            $sucesso = "Autenticação em Duas Etapas (2FA) ativada com sucesso!";
            $usuario['status_2fa'] = 'ativo';
        } else {
            $erro = "Código inválido. Verifique se a hora do seu celular está sincronizada automaticamente.";
        }
    } elseif (isset($_POST['acao']) && $_POST['acao'] === 'desativar') {
        $sql_desativa = "UPDATE Usuarios SET status_2fa = 'desativado', segredo_2fa = NULL WHERE id = ?";
        $stmt_des = $conexao->prepare($sql_desativa);
        $stmt_des->bind_param("i", $usuario_id);
        $stmt_des->execute();

        $sucesso = "Autenticação em Duas Etapas (2FA) desativada.";
        $usuario['status_2fa'] = 'desativado';
        $usuario['segredo_2fa'] = null;
    }
}

// Se o 2FA estiver desativado e ainda não houver um segredo gerado, cria um para exibir o QR Code
if ($usuario['status_2fa'] !== 'ativo' && empty($usuario['segredo_2fa'])) {
    $segredo = $tfa->createSecret();
    $sql_up = "UPDATE Usuarios SET segredo_2fa = ? WHERE id = ?";
    $stmt_up = $conexao->prepare($sql_up);
    $stmt_up->bind_param("si", $segredo, $usuario_id);
    $stmt_up->execute();
    $usuario['segredo_2fa'] = $segredo;
}

$qrCodeUrl = ($usuario['status_2fa'] !== 'ativo') 
    ? $tfa->getQRCodeImageAsDataUri($usuario['email'], $usuario['segredo_2fa']) 
    : '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segurança 2FA - Adote CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm border-0" style="max-width: 440px; width: 100%;">
        <h4 class="text-center mb-3 fw-bold">Autenticação em Duas Etapas (2FA)</h4>

        <?php if ($sucesso): ?>
            <div class="alert alert-success p-2 text-center small"><?= $sucesso ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($usuario['status_2fa'] === 'ativo'): ?>
            <div class="text-center my-3">
                <span class="badge bg-success fs-6 mb-2">Status: Ativado</span>
                <p class="small text-muted">Sua conta está protegida pelo aplicativo autenticador.</p>
            </div>

            <form method="POST" onsubmit="return confirm('Tem certeza que deseja desativar o 2FA?');">
                <input type="hidden" name="acao" value="desativar">
                <button type="submit" class="btn btn-outline-danger w-100 mb-2">Desativar 2FA</button>
            </form>
        <?php else: ?>
            <p class="small text-muted text-center">
                Escaneie o QR Code abaixo usando o <strong>Google Authenticator</strong> ou <strong>Microsoft Authenticator</strong> para adicionar uma camada extra de segurança ao seu login.
            </p>

            <div class="text-center my-3">
                <img src="<?= $qrCodeUrl ?>" alt="QR Code 2FA" class="img-fluid border p-2 bg-white rounded" style="width: 200px; height: 200px;">
                <p class="mt-2 mb-0 text-muted small">Chave manual:</p>
                <code class="fs-6 user-select-all"><?= chunk_split($usuario['segredo_2fa'], 4, ' ') ?></code>
            </div>

            <form method="POST">
                <input type="hidden" name="acao" value="ativar">
                <div class="mb-3">
                    <label for="codigo_2fa" class="form-label small fw-bold">Digite o código de 6 dígitos do aplicativo:</label>
                    <input type="text" id="codigo_2fa" name="codigo_2fa" class="form-control text-center fs-4" maxlength="7" placeholder="000000" required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">Confirmar e Ativar 2FA</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none small">Voltar para o site</a>
        </div>
    </div>
</body>
</html>