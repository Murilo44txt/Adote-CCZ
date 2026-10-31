<?php
// Inicia a sessão e verifica se é MASTER
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] != 'master') {
    // Se não for master, manda para o painel de animais sem fazer nada
    header("Location: admin_animais.php");
    exit;
}

include 'conexao.php'; //

// Verifica se o ID foi passado pela URL
if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Prepara e executa a query de exclusão
    $sql = "DELETE FROM Animais WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Sucesso, volta para o painel
        header("Location: admin_animais.php");
    } else {
        echo "Erro ao excluir animal: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
    exit();
} else {
    // Se nenhum ID foi fornecido, apenas redireciona
    header("Location: admin_animais.php");
    exit();
}
?>