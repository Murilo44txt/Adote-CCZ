<?php
include 'conexao.php';
$mensagem_erro = "";
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_post = $conexao->real_escape_string($_POST['email']);
    $codigo_digitado = $conexao->real_escape_string($_POST['codigo']);
    $nova_senha = $_POST['nova_senha'];

    // Valida se o código está correto e ainda dentro do prazo
    $sql = "SELECT id FROM usuarios WHERE email = ? AND codigo_validacao = ? AND expiracao_codigo > NOW()";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ss", $email_post, $codigo_digitado);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Código válido! Gera o hash da nova palavra-passe e limpa o código temporário
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE usuarios SET senha_hash = ?, codigo_validacao = NULL, expiracao_codigo = NULL WHERE email = ?";
        $stmt_update = $conexao->prepare($sql_update);
        $stmt_update->bind_param("ss", $senha_hash, $email_post);
        $stmt_update->execute();

        echo "<script>alert('Palavra-passe alterada com sucesso!'); window.location.href='LoginMain.php';</script>";
        exit;
    } else {
        $mensagem_erro = "Código inválido ou expirado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Nova Palavra-passe - Adote CCZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-sm" style="width: 400px;">
        <h4 class="text-center mb-3">Redefinir Palavra-passe</h4>
        <p class="text-muted text-center small">Insira o código enviado para <strong><?= htmlspecialchars($email) ?></strong> e a sua nova palavra-passe.</p>
        
        <?php if($mensagem_erro): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $mensagem_erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="mb-3">
                <label class="form-label">Código de Verificação (6 dígitos)</label>
                <input type="text" name="codigo" class="form-control text-center fs-5 tracking-widest" maxlength="6" required placeholder="000000">
            </div>

            <div class="mb-3">
                <label class="form-label">Nova Palavra-passe</label>
                <input type="password" name="nova_senha" class="form-control" required placeholder="********">
            </div>

            <button type="submit" class="btn btn-primary w-100">Atualizar Palavra-passe</button>
        </form>
    </div>
</body>
</html>