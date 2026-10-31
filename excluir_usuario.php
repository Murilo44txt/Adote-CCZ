<?php
include 'conexao.php';

// Verifica se o ID foi passado pela URL
if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepara e executa a query de exclusão
    $sql = "DELETE FROM Usuarios WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Usuário excluído com sucesso!";
    } else {
        echo "Erro ao excluir usuário: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();

    // Redireciona de volta para a página de administração
    header("Location: admin_usuarios.php");
    exit();
} else {
    // Se nenhum ID foi fornecido, apenas redireciona
    header("Location: admin_usuarios.php");
    exit();
}
?>