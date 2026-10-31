<?php
// Proteção de página: somente admin e master
session_start();
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php'; //

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coleta todos os dados do formulário, incluindo o ID
    $id = (int)$_POST['id'];
    $data_entrada = $_POST['data_entrada'];
    $endereco_origem = !empty($_POST['endereco_origem']) ? $conexao->real_escape_string($_POST['endereco_origem']) : NULL;
    $motivo_recolhimento = !empty($_POST['motivo_recolhimento']) ? $conexao->real_escape_string($_POST['motivo_recolhimento']) : NULL;
    $especie = $conexao->real_escape_string($_POST['especie']);
    $sexo = $conexao->real_escape_string($_POST['sexo']);
    $castrado = (int)$_POST['castrado'];
    $raca = !empty($_POST['raca']) ? $conexao->real_escape_string($_POST['raca']) : NULL;
    $chip = !empty($_POST['chip']) ? $conexao->real_escape_string($_POST['chip']) : NULL;
    $pelagem = !empty($_POST['pelagem']) ? $conexao->real_escape_string($_POST['pelagem']) : NULL;
    $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : NULL;
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : NULL;
    $status_adocao = $conexao->real_escape_string($_POST['status_adocao']);

    // Prepara a query de ATUALIZAÇÃO (UPDATE)
    $sql = "UPDATE Animais SET 
                data_entrada = ?, 
                endereco_origem = ?, 
                motivo_recolhimento = ?, 
                especie = ?, 
                sexo = ?, 
                castrado = ?, 
                raca = ?, 
                chip = ?, 
                pelagem = ?, 
                peso = ?, 
                data_nascimento = ?, 
                status_adocao = ?
            WHERE id = ?";
    
    $stmt = $conexao->prepare($sql);
    
    // Binda os parâmetros (os mesmos 12 + o ID no final)
    // s s s s s i s s s d s s i
    $stmt->bind_param("sssssisssdssi", 
        $data_entrada, $endereco_origem, $motivo_recolhimento, $especie, $sexo, $castrado, 
        $raca, $chip, $pelagem, $peso, $data_nascimento, $status_adocao, $id
    );

    if ($stmt->execute()) {
        header("Location: admin_animais.php");
    } else {
        echo "Erro ao atualizar animal: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
    exit();
}
?>