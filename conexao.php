<?php
// Arquivo de conexão com o banco de dados
$servidor = "localhost";
$usuario = "root"; // Substitua pelo seu usuário do MySQL
$senha = ""; // Substitua pela sua senha do MySQL
$bd = "bd_ccz"; // Nome do banco de dados

// Cria a conexão
$conexao = new mysqli($servidor, $usuario, $senha, $bd);

// Checa a conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Opcional: Define o charset para evitar problemas de acentuação
$conexao->set_charset("utf8mb4");
?>