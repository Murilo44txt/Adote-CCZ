<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] != 'master') {
    header("Location: admin_animais.php");
    exit;
}

include 'conexao.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Remove o arquivo da foto antes de apagar o registro
    $stmt_f = $conexao->prepare("SELECT foto FROM Animais WHERE id = ?");
    $stmt_f->bind_param("i", $id);
    $stmt_f->execute();
    $res = $stmt_f->get_result()->fetch_assoc();
    if (!empty($res['foto']) && file_exists($res['foto'])) {
        unlink($res['foto']);
    }
    $stmt_f->close();

    $sql = "DELETE FROM Animais WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_animais.php");
    } else {
        echo "Erro ao excluir animal: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
    exit();
} else {
    header("Location: admin_animais.php");
    exit();
}
?>