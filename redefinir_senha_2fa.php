<?php
session_start();
include 'conexao.php';
require_once __DIR__ . '/lib/TwoFactorAuth/TwoFactorAuth.php';
use RobThree\Auth\TwoFactorAuth;

$erro = "";
$passo = 1; // Passo 1: Pedir E-mail e Código 2FA | Passo 2: Digitar nova senha

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // PASSO 1: Validar E-mail e Código TOTP do App
    if (isset($_POST['acao']) && $_POST['acao'] === 'validar_2fa') {
        $email = $conexao->real_escape_string($_POST['email']);
        $codigo_2fa = trim($_POST['codigo_2fa']);

        $sql = "SELECT id, segredo_2fa, status_2fa FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $usuario = $res->fetch_assoc();

            if ($usuario['status_2fa'] === 'ativo') {
                $tfa = new TwoFactorAuth('Adote CCZ');

                if ($tfa->verifyCode($usuario['segredo_2fa'], $codigo_2fa)) {
                    // Validação OK: Guarda autorização na sessão para permitir a troca de senha
                    $_SESSION['redefinir_usuario_id'] = $usuario['id'];
                    $passo = 2;
                } else {
                    $erro = "Código do Autenticador inválido.";
                }
            } else {
                $erro = "Esta conta ainda não possui 2FA configurado.";
            }
        } else {
            $erro = "E-mail não encontrado no sistema.";
        }
    }

    // PASSO 2: Alterar a Senha no Banco
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
        if (!isset($_SESSION['redefinir_usuario_id'])) {
            header("Location: LoginMain.php");
            exit;
        }

        $nova_senha = $_POST['nova_senha'];
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $usuario_id = $_SESSION['redefinir_usuario_id'];

        $sql_up = "UPDATE usuarios SET senha_hash = ? WHERE id = ?";
        $stmt_up = $conexao->prepare($sql_up);
        $stmt_up->bind_param("si", $senha_hash, $usuario_id);

        if ($stmt_up->execute()) {
            unset($_SESSION['redefinir_usuario_id']);
            echo "<script>alert('Palavra-passe alterada com sucesso!'); window.location.href='LoginMain.php';</script>";
            exit;
        } else {
            $erro = "Erro ao atualizar a palavra-passe no banco de dados.";
            $passo = 2;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha via 2FA - Adote CCZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="width: 400px;">
        <h4 class="text-center mb-3">Recuperar Conta</h4>

        <?php if ($erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($passo === 1): ?>
            <!-- Formulário do Passo 1 -->
            <form method="POST">
                <input type="hidden" name="acao" value="validar_2fa">

                <div class="mb-3">
                    <label class="form-label">E-mail cadastrado</label>
                    <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
                </div>

                <div class="mb-3">
                    <label class="form-label">Código de 6 dígitos do App (Authenticator)</label>
                    <input type="text" name="codigo_2fa" class="form-control text-center fs-4 tracking-widest" maxlength="6" required placeholder="000 000" autocomplete="off">
                </div>

                <button type="submit" class="btn btn-primary w-100">Validar Autenticador</button>
            </form>

        <?php else: ?>
            <!-- Formulário do Passo 2 -->
            <form method="POST">
                <input type="hidden" name="acao" value="alterar_senha">

                <div class="mb-3">
                    <label class="form-label">Nova Palavra-passe</label>
                    <input type="password" name="nova_senha" class="form-control" required placeholder="********" autofocus>
                </div>

                <button type="submit" class="btn btn-success w-100">Atualizar Palavra-passe</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="LoginMain.php" class="text-decoration-none small">Voltar ao Login</a>
        </div>
    </div>
</body>
</html>