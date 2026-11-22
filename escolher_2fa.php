<?php
session_start();
include 'conexao.php';
require_once 'enviar_email.php';

// Autoloader da biblioteca TwoFactorAuth (pasta TwoFactorAuth/lib/)
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

// Se não passou pela etapa de e-mail/senha, volta para o login
if (!isset($_SESSION['2fa_pendente'])) {
    header("Location: LoginMain.php");
    exit;
}

$pendente  = $_SESSION['2fa_pendente'];
$tem_totp  = ($pendente['status_2fa'] === 'ativo' and !empty($pendente['segredo_2fa']));
$metodo    = $_GET['metodo'] ?? '';
$erro      = '';
$aviso     = '';

// Se o usuário escolheu "email" (ou clicou em reenviar), gera e dispara o código agora
if ($metodo === 'email' and $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_SESSION['2fa_email_enviado']) or isset($_GET['reenviar'])) {
        $codigo_email = sprintf("%06d", mt_rand(1, 999999));
        $expiracao    = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $sql_up = "UPDATE Usuarios SET codigo_validacao = ?, expiracao_codigo = ? WHERE id = ?";
        $stmt_up = $conexao->prepare($sql_up);
        $stmt_up->bind_param("ssi", $codigo_email, $expiracao, $pendente['id']);
        $stmt_up->execute();
        $stmt_up->close();

        if (enviarEmailVerificacao($pendente['email'], $pendente['nome'], $codigo_email)) {
            $_SESSION['2fa_email_enviado'] = true;
            $aviso = "Enviamos um código de 6 dígitos para <strong>" . htmlspecialchars($pendente['email']) . "</strong>.";
        } else {
            $erro = "Falha ao enviar o e-mail de verificação. Verifique a configuração SMTP.";
        }
    } else {
        $aviso = "Digite o código de 6 dígitos enviado para <strong>" . htmlspecialchars($pendente['email']) . "</strong>.";
    }
}

// Processa a validação do código submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_post = $_POST['metodo'] ?? '';
    $codigo      = preg_replace('/\s+/', '', $_POST['codigo'] ?? '');
    $valido      = false;

    if ($metodo_post === 'totp' and $tem_totp) {
        $qrProvider = new QRServerProvider(verifyssl: false);
        $tfa = new TwoFactorAuth($qrProvider, 'Adote CCZ');

        if ($tfa->verifyCode($pendente['segredo_2fa'], $codigo)) {
            $valido = true;
        } else {
            $erro = "Código do autenticador inválido ou expirado.";
            $metodo = 'totp';
        }
    } elseif ($metodo_post === 'email') {
        $agora = date('Y-m-d H:i:s');
        $sql_v = "SELECT codigo_validacao, expiracao_codigo FROM Usuarios WHERE id = ?";
        $stmt_v = $conexao->prepare($sql_v);
        $stmt_v->bind_param("i", $pendente['id']);
        $stmt_v->execute();
        $dados = $stmt_v->get_result()->fetch_assoc();
        $stmt_v->close();

        if ($dados and $dados['codigo_validacao'] === $codigo and $dados['expiracao_codigo'] >= $agora) {
            // Limpa o código usado do banco
            $sql_limpa = "UPDATE Usuarios SET codigo_validacao = NULL, expiracao_codigo = NULL WHERE id = ?";
            $stmt_l = $conexao->prepare($sql_limpa);
            $stmt_l->bind_param("i", $pendente['id']);
            $stmt_l->execute();
            $stmt_l->close();

            $valido = true;
        } else {
            $erro = "Código de e-mail inválido ou expirado.";
            $metodo = 'email';
        }
    }

    // Se validado com sucesso, promove a sessão para logada
    if ($valido) {
        $_SESSION['loggedin']     = true;
        $_SESSION['id']           = $pendente['id'];
        $_SESSION['nome']         = $pendente['nome'];
        $_SESSION['nivel_acesso'] = $pendente['nivel_acesso'];

        unset($_SESSION['2fa_pendente']);
        unset($_SESSION['2fa_email_enviado']);

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação em Duas Etapas - Adote CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card p-4 shadow-sm border-0" style="max-width: 430px; width: 100%;">
        <h4 class="text-center fw-bold mb-2">Verificação de Segurança</h4>
        <p class="small text-muted text-center mb-4">
            Olá, <strong><?= htmlspecialchars($pendente['nome']) ?></strong>. Para concluir o acesso, confirme sua identidade.
        </p>

        <?php if ($aviso): ?>
            <div class="alert alert-info p-2 text-center small"><?= $aviso ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($metodo === ''): ?>
            <!-- ETAPA 1: ESCOLHA DO MÉTODO -->
            <p class="small fw-bold text-center mb-3">Escolha como deseja receber o código:</p>

            <div class="d-grid gap-2">
                <a href="escolher_2fa.php?metodo=email" class="btn btn-outline-dark py-2 text-start d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Código por E-mail</div>
                        <small class="text-muted"><?= htmlspecialchars($pendente['email']) ?></small>
                    </div>
                    <span class="badge bg-warning text-dark">Enviar</span>
                </a>

                <?php if ($tem_totp): ?>
                    <a href="escolher_2fa.php?metodo=totp" class="btn btn-outline-dark py-2 text-start d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Aplicativo Autenticador</div>
                            <small class="text-muted">Google / Microsoft Authenticator</small>
                        </div>
                        <span class="badge bg-success">Ativo</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-secondary py-2 text-start d-flex justify-content-between align-items-center" disabled>
                        <div>
                            <div class="fw-bold">Aplicativo Autenticador</div>
                            <small class="text-muted">Não configurado no seu perfil</small>
                        </div>
                        <span class="badge bg-secondary">Inativo</span>
                    </button>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- ETAPA 2: DIGITAR O CÓDIGO DO MÉTODO ESCOLHIDO -->
            <form method="POST">
                <input type="hidden" name="metodo" value="<?= htmlspecialchars($metodo) ?>">

                <div class="mb-3">
                    <label for="codigo" class="form-label small fw-bold">
                        <?= ($metodo === 'totp') ? 'Código de 6 dígitos do Aplicativo Autenticador:' : 'Código de 6 dígitos enviado ao seu E-mail:' ?>
                    </label>
                    <input type="text" id="codigo" name="codigo" class="form-control text-center fs-4" maxlength="7" placeholder="000000" autofocus required autocomplete="off">
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold">Verificar e Entrar</button>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-3 small">
                <a href="escolher_2fa.php" class="text-decoration-none">Trocar método</a>
                <?php if ($metodo === 'email'): ?>
                    <a href="escolher_2fa.php?metodo=email&reenviar=1" class="text-decoration-none">Reenviar código</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <hr class="my-3">
        <div class="text-center">
            <a href="LoginMain.php" class="small text-muted text-decoration-none">Cancelar e voltar ao login</a>
        </div>
    </div>

</body>
</html>