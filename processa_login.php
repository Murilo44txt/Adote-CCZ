<?php
// Inicia a sessão
session_start();

// Inclui a conexão
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conexao->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    // Selecionamos também as colunas de status e segurança 2FA
    $sql = "SELECT id, nome, senha_hash, nivel_acesso, status_2fa, segredo_2fa FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha_hash'])) {
            
            // VERIFICAÇÃO 2FA: Se o usuário ativou a autenticação em duas etapas
            if ($usuario['status_2fa'] === 'ativo') {
                // Armazena dados temporários para validar na tela seguinte
                $_SESSION['usuario_temp_id'] = $usuario['id'];
                $_SESSION['usuario_temp_nome'] = $usuario['nome'];
                $_SESSION['usuario_temp_nivel'] = $usuario['nivel_acesso'];

                header("Location: validar_login_2fa.php");
                exit();
            }

            // Caso NÃO tenha 2FA ativo, realiza o login diretamente
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];

            // Redireciona para a página principal
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('E-mail ou senha inválidos.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('E-mail ou senha inválidos.'); window.history.back();</script>";
    }

    $stmt->close();
    $conexao->close();
}
?>