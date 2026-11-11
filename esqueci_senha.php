<?php
include 'conexao.php';
require_once 'enviar_email.php';

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conexao->real_escape_string($_POST['email']);

    // Verifica se o e-mail existe
    $sql = "SELECT id, nome FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $nome = $usuario['nome'];

        // Gera o código de 6 dígitos e expiração de 15 minutos
        $codigo = sprintf("%06d", mt_rand(1, 999999));
        $expiracao = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Salva o código na base de dados do utilizador
        $sql_update = "UPDATE usuarios SET codigo_validacao = ?, expiracao_codigo = ? WHERE email = ?";
        $stmt_update = $conexao->prepare($sql_update);
        $stmt_update->bind_param("sss", $codigo, $expiracao, $email);
        $stmt_update->execute();

        // Envia o e-mail
        $assunto = "Adote CCZ - Redefinição de Palavra-passe";
        $mensagem_email = "Olá <b>$nome</b>,<br><br>Recebemos um pedido para redefinir a sua palavra-passe.<br>O seu código de verificação é: <h2>$codigo</h2><br>Este código expira em 15 minutos.";
        
        // Função do PHPMailer adaptada para redefinição (ou reutiliza a função anterior alterando o texto se preferir)
        $enviado = enviarEmailVerificacao($email, $nome, $codigo);

        if ($enviado) {
            header("Location: redefinir_senha.php?email=" . urlencode($email));
            exit;
        } else {
            $erro = "Erro ao enviar o e-mail. Tente novamente mais tarde.";
        }
    } else {
        $erro = "Este e-mail não está registado no sistema.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Palavra-passe - Adote CCZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-sm" style="width: 400px;">
        <h4 class="text-center mb-3">Recuperar Conta</h4>
        <p class="text-muted text-center small">Introduza o seu e-mail para receber o código de redefinição.</p>
        
        <?php if($erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar Código</button>
            <div class="text-center mt-3">
                <a href="LoginMain.php" class="text-decoration-none small">Voltar ao Login</a>
            </div>
        </form>
    </div>
</body>
</html>