<?php
session_start();
include 'conexao.php'; //[cite: 1]

if (!isset($_SESSION['loggedin']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];
$usuario_logado = $_SESSION['id'];
$nivel_acesso = $_SESSION['nivel_acesso'];

// Verifica se o post pertence ao usuário ou se ele é admin/master
$sql = "SELECT usuario_id, foto, tipo_feed FROM PostsFeed WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if ($post && ($post['usuario_id'] == $usuario_logado || $nivel_acesso == 'admin' || $nivel_acesso == 'master')) {
    // Apaga o arquivo físico
    if (file_exists($post['foto'])) {
        unlink($post['foto']);
    }
    
    // Apaga do BD
    $del_sql = "DELETE FROM PostsFeed WHERE id = ?";
    $del_stmt = $conexao->prepare($del_sql);
    $del_stmt->bind_param("i", $post_id);
    $del_stmt->execute();
    
    // Redireciona de volta
    $redirect = ($post['tipo_feed'] == 'adocao') ? 'navbars/adocao.php' : 'navbars/encontrePet.php';
    header("Location: " . $redirect);
} else {
    echo "Você não tem permissão para excluir esta postagem.";
}
?>