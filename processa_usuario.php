<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $cpf      = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $senha    = $_POST['senha'];

    // Verifica duplicidade
    $sql_verifica = "SELECT id FROM Usuarios WHERE email = ? OR cpf = ?";
    $stmt_verifica = $conexao->prepare($sql_verifica);
    $stmt_verifica->bind_param("ss", $email, $cpf);
    $stmt_verifica->execute();
    $stmt_verifica->store_result();

    if ($stmt_verifica->num_rows > 0) {
        echo "<script>alert('E-mail ou CPF já cadastrado.'); window.history.back();</script>";
        exit;
    }
    $stmt_verifica->close();

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Salva sem gerar código ainda (status_2fa opcional inicia desativado)
    $sql = "INSERT INTO Usuarios (nome, email, cpf, telefone, senha_hash, status_2fa) VALUES (?, ?, ?, ?, ?, 'desativado')";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssss", $nome, $email, $cpf, $telefone, $senha_hash);

    if ($stmt->execute()) {
        $_SESSION['usuario_verificar_id'] = $stmt->insert_id;
        header("Location: verificar_email.php");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
}
?>