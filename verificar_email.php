<?php
include 'conexao.php';
$mensagem_erro = "";
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_post = $conexao->real_escape_string($_POST['email']);
    $codigo_digitado = $conexao->real_escape_string($_POST['codigo']);

    // Verifica se o código está correto e dentro da validade
    $sql = "SELECT id FROM usuarios WHERE email = ? AND codigo_validacao = ? AND expiracao_codigo > NOW()";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ss", $email_post, $codigo_digitado);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Código válido, limpa as colunas para ativar a conta
        $sql_ativa = "UPDATE usuarios SET codigo_validacao = NULL, expiracao_codigo = NULL WHERE email = ?";
        $stmt_ativa = $conexao->prepare($sql_ativa);
        $stmt_ativa->bind_param("s", $email_post);
        $stmt_ativa->execute();
        
        echo "<script>alert('Conta ativada com sucesso!'); window.location.href='LoginMain.php';</script>";
        exit;
    } else {
        $mensagem_erro = "Código inválido ou expirado. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Validar E-mail - Adote CCZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-sm" style="width: 400px;">
        <h4 class="text-center mb-3">Valide o seu E-mail</h4>
        <p class="text-muted text-center text-sm">Enviámos um código para <strong><?= htmlspecialchars($email) ?></strong>.</p>
        
        <?php if($mensagem_erro): ?>
            <div class="alert alert-danger p-2 text-center"><?= $mensagem_erro ?></div>
        <?php endif; ?>

        <form method="POST" action="verificar_email.php?email=<?= urlencode($email) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <div class="mb-3">
                <input type="text" name="codigo" class="form-control text-center fs-4 tracking-widest" maxlength="6" placeholder="000000" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ativar Conta</button>
        </form>
    </div>
</body>
</html>