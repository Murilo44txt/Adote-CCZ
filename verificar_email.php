<?php
session_start();
include 'conexao.php';
require_once 'enviar_email.php';

if (!isset($_SESSION['usuario_verificar_id'])) {
    header("Location: registro.php");
    exit;
}

$usuario_id = $_SESSION['usuario_verificar_id'];
$erro = "";
$aviso = "";
$email_verificado = false;

// Busca dados completos do usuário para já poder logá-lo se escolher configurar o 2FA
$sql = "SELECT id, nome, email, nivel_acesso, codigo_validacao, expiracao_codigo FROM Usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    unset($_SESSION['usuario_verificar_id']);
    header("Location: registro.php");
    exit;
}

// Gera e envia o código SOMENTE ao chegar aqui (ou ao clicar em reenviar)
if (empty($usuario['codigo_validacao']) || isset($_GET['reenviar'])) {
    $codigo_validacao = sprintf("%06d", mt_rand(1, 999999));
    $expiracao_codigo = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $sql_up = "UPDATE Usuarios SET codigo_validacao = ?, expiracao_codigo = ? WHERE id = ?";
    $stmt_up = $conexao->prepare($sql_up);
    $stmt_up->bind_param("ssi", $codigo_validacao, $expiracao_codigo, $usuario_id);
    $stmt_up->execute();

    if (enviarEmailVerificacao($usuario['email'], $usuario['nome'], $codigo_validacao)) {
        $aviso = "Enviamos um código de 6 dígitos para <b>" . htmlspecialchars($usuario['email']) . "</b>.";
    } else {
        $erro = "Não foi possível enviar o e-mail de verificação. Verifique a configuração SMTP.";
    }
}

// Valida o código digitado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_digitado = trim($_POST['codigo']);
    $agora = date('Y-m-d H:i:s');

    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();

    if ($dados['codigo_validacao'] === $codigo_digitado && $dados['expiracao_codigo'] >= $agora) {
        // Confirma o e-mail e limpa o código usado
        $sql_ok = "UPDATE Usuarios SET codigo_validacao = NULL, expiracao_codigo = NULL WHERE id = ?";
        $stmt_ok = $conexao->prepare($sql_ok);
        $stmt_ok->bind_param("i", $usuario_id);
        $stmt_ok->execute();

        // Já autentica o usuário na sessão para que ele possa acessar configurar_2fa.php ou o site direto
        $_SESSION['loggedin']     = true;
        $_SESSION['id']           = $dados['id'];
        $_SESSION['nome']         = $dados['nome'];
        $_SESSION['nivel_acesso'] = $dados['nivel_acesso'] ?? 'usuario';

        unset($_SESSION['usuario_verificar_id']);
        $email_verificado = true;
    } else {
        $erro = "Código inválido ou expirado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar E-mail - Adote CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm border-0" style="max-width: 420px; width: 100%;">
        
        <?php if ($email_verificado): ?>
            <!-- Tela de Escolha Opcional de TOTP após confirmar o e-mail -->
            <div class="text-center">
                <div class="mb-3">
                    <span class="badge bg-success fs-6 px-3 py-2">E-mail Verificado!</span>
                </div>
                <h4 class="fw-bold mb-2">Deseja configurar um autenticador?</h4>
                <p class="small text-muted mb-4">
                    Você pode adicionar uma camada extra de segurança vinculando o <strong>Google Authenticator</strong> ou <strong>Microsoft Authenticator</strong> agora, ou fazer isso mais tarde pelo seu perfil.
                </p>

                <div class="d-grid gap-2">
                    <a href="configurar_2fa.php" class="btn btn-warning fw-bold">Sim, configurar autenticador (2FA)</a>
                    <a href="index.php" class="btn btn-outline-secondary">Agora não, ir para o site</a>
                </div>
            </div>

        <?php else: ?>
            <!-- Formulário de Verificação do E-mail -->
            <h4 class="text-center mb-3 fw-bold">Verifique seu E-mail</h4>

            <?php if ($aviso): ?>
                <div class="alert alert-info p-2 text-center small"><?= $aviso ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Código recebido por e-mail:</label>
                    <input type="text" name="codigo" class="form-control text-center fs-4" maxlength="6" placeholder="000000" autofocus required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">Confirmar E-mail</button>
            </form>

            <div class="text-center mt-3">
                <a href="verificar_email.php?reenviar=1" class="small text-decoration-none">Reenviar código</a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>