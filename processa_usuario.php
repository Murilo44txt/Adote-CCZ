<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'conexao.php';
require_once 'enviar_email.php'; // Inclui o arquivo que criamos acima

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conexao->real_escape_string($_POST['nome']);
    $email = $conexao->real_escape_string($_POST['email']);
    $cpf = $conexao->real_escape_string($_POST['cpf']);
    $telefone = $conexao->real_escape_string($_POST['telefone']);
    $senha = $_POST['senha'];

    // Verificação de duplicidade
    $sql_verifica = "SELECT id FROM usuarios WHERE email = ? OR cpf = ?";
    $stmt_verifica = $conexao->prepare($sql_verifica);
    $stmt_verifica->bind_param("ss", $email, $cpf);
    $stmt_verifica->execute();
    $stmt_verifica->store_result();

    if ($stmt_verifica->num_rows > 0) {
        echo "<script>alert('E-mail ou CPF já registado.'); window.history.back();</script>";
        exit;
    }
    $stmt_verifica->close();

    // Geração do código, expiração e hash
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $codigo_validacao = sprintf("%06d", mt_rand(1, 999999));
    $expiracao_codigo = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Inserção no banco
    $sql = "INSERT INTO usuarios (nome, email, cpf, telefone, senha_hash, codigo_validacao, expiracao_codigo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssssss", $nome, $email, $cpf, $telefone, $senha_hash, $codigo_validacao, $expiracao_codigo);

    if ($stmt->execute()) {
        // Envia o e-mail usando o PHPMailer
        $enviado = enviarEmailVerificacao($email, $nome, $codigo_validacao);

        if ($enviado) {
            header("Location: verificar_email.php?email=" . urlencode($email));
            exit;
        } else {
            echo "Registo efetuado, mas houve um erro ao enviar o e-mail de verificação.";
        }
    } else {
        echo "Erro ao registar: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
}
?>