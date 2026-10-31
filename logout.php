<?php
// Inicia a sessão
session_start();

// Destrói todas as variáveis da sessão
$_SESSION = array();

// Finaliza a sessão
session_destroy();

// Redireciona para a página de login ou para a home
header("Location: index.php");
exit;
?>