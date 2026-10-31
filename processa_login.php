<?php
// Inicia a sessão
session_start();

// Inclui a conexão
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conexao->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    // Agora buscamos também o nível de acesso
    $sql = "SELECT id, nome, senha_hash, nivel_acesso FROM Usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha_hash'])) {
            // Senha correta, armazena todos os dados na sessão
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; // Nova linha!

            // Redireciona para a página principal
            header("Location: index.php");
            exit();
        } else {
            echo "Email ou senha inválidos.";
        }
    } else {
        echo "Email ou senha inválidos.";
    }

    $stmt->close();
    $conexao->close();
}
?>