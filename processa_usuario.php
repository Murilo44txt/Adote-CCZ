<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapa e limpa os dados para evitar injeção de SQL
    $nome = $conexao->real_escape_string($_POST['nome']);
    $email = $conexao->real_escape_string($_POST['email']);
    $cpf = $conexao->real_escape_string($_POST['cpf']);
    $telefone = $conexao->real_escape_string($_POST['telefone']);
    $senha = $_POST['senha'];

    // Gera um hash seguro da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Prepara a query SQL para inserção de dados
    $sql = "INSERT INTO Usuarios (nome, email, cpf, telefone, senha_hash) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    // Binda os parâmetros à query
    $stmt->bind_param("sssss", $nome, $email, $cpf, $telefone, $senha_hash);

    // Executa a query e verifica o resultado
    if ($stmt->execute()) {
        
        // --- LÓGICA DE REDIRECIONAMENTO ADICIONADA AQUI ---
        // Redireciona o usuário para a página de login após o sucesso
        header("Location: LoginMain.php?cadastro=sucesso");
        exit; // Essencial para parar a execução da página atual
        
    } else {
        // Se houver erro, você pode exibir ou também redirecionar com um aviso
        echo "Erro ao cadastrar usuário: " . $stmt->error;
    }

    // Fecha a declaração e a conexão
    $stmt->close();
    $conexao->close();
}
?>