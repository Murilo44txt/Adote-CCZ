<?php
session_start();
// Proteção: Apenas admin ou master podem processar a edição
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = $conexao->real_escape_string($_POST['nome']);
    $email = $conexao->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    // Inicia a query de atualização
    $sql = "UPDATE Usuarios SET nome = ?, email = ?";
    $params = [$nome, $email];
    $types = "ss";

    // Adiciona a atualização de senha se ela foi fornecida
    if (!empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql .= ", senha_hash = ?";
        $params[] = $senha_hash;
        $types .= "s";
    }
    
    // Apenas o master pode alterar o nível de acesso
    if ($_SESSION['nivel_acesso'] == 'master' && isset($_POST['nivel_acesso'])) {
        $nivel_acesso = $_POST['nivel_acesso'];
        $sql .= ", nivel_acesso = ?";
        $params[] = $nivel_acesso;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header("Location: admin_usuarios.php");
    } else {
        echo "Erro ao atualizar usuário: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
    exit();
}
?>