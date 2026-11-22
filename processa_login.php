<?php
session_start();
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, email, senha_hash, nivel_acesso, status_2fa, segredo_2fa FROM Usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario['senha_hash'])) {
            // Guarda os dados em sessão temporária até concluir a verificação 2FA
            $_SESSION['2fa_pendente'] = [
                'id'           => $usuario['id'],
                'nome'         => $usuario['nome'],
                'email'        => $usuario['email'],
                'nivel_acesso' => $usuario['nivel_acesso'],
                'status_2fa'   => $usuario['status_2fa'],
                'segredo_2fa'  => $usuario['segredo_2fa']
            ];

            $stmt->close();
            $conexao->close();

            header("Location: escolher_2fa.php");
            exit;
        } else {
            echo "<script>alert('E-mail ou senha incorretos.'); window.location.href='LoginMain.php';</script>";
        }
    } else {
        echo "<script>alert('E-mail ou senha incorretos.'); window.location.href='LoginMain.php';</script>";
    }

    $stmt->close();
    $conexao->close();
}
?>