<?php
session_start();
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_entrada        = $_POST['data_entrada'];
    $endereco_origem     = !empty($_POST['endereco_origem']) ? trim($_POST['endereco_origem']) : NULL;
    $motivo_recolhimento = !empty($_POST['motivo_recolhimento']) ? trim($_POST['motivo_recolhimento']) : NULL;
    $especie             = trim($_POST['especie']);
    $sexo                = trim($_POST['sexo']);
    $castrado            = (int)$_POST['castrado'];
    $raca                = !empty($_POST['raca']) ? trim($_POST['raca']) : NULL;
    $chip                = !empty($_POST['chip']) ? trim($_POST['chip']) : NULL;
    $pelagem             = !empty($_POST['pelagem']) ? trim($_POST['pelagem']) : NULL;
    $peso                = !empty($_POST['peso']) ? (float)$_POST['peso'] : NULL;
    $data_nascimento     = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : NULL;
    $status_adocao       = trim($_POST['status_adocao']);
    $caminho_foto        = NULL;

    // Upload opcional da foto do animal
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $extensoes_permitidas)) {
            $pasta = 'uploads/animais/';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }
            $nome_arquivo = 'animal_' . uniqid() . '.' . $extensao;
            $destino = $pasta . $nome_arquivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $caminho_foto = $destino;
            }
        }
    }

    $sql = "INSERT INTO Animais (data_entrada, endereco_origem, motivo_recolhimento, especie, sexo, castrado, raca, chip, pelagem, peso, data_nascimento, status_adocao, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssssisssdsss", 
        $data_entrada, $endereco_origem, $motivo_recolhimento, $especie, $sexo, $castrado, 
        $raca, $chip, $pelagem, $peso, $data_nascimento, $status_adocao, $caminho_foto
    );

    if ($stmt->execute()) {
        header("Location: admin_animais.php");
        exit;
    } else {
        echo "Erro ao cadastrar animal: " . $stmt->error;
    }

    $stmt->close();
    $conexao->close();
}
?>