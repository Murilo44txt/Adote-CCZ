<?php
session_start();
include 'conexao.php'; 

if (!isset($_SESSION['loggedin'])) {
    header("Location: LoginMain.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['id'];
    $tipo_feed = $_POST['tipo_feed'];
    
    // Trava de segurança
    if ($tipo_feed == 'saude' && ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
        die("Erro de permissão: Apenas administradores podem postar em Saúde Animal.");
    }

    // NOVA VARIÁVEL: Título
    $titulo = !empty($_POST['titulo']) ? $conexao->real_escape_string($_POST['titulo']) : NULL;
    
    $telefone = !empty($_POST['telefone']) ? $conexao->real_escape_string($_POST['telefone']) : NULL;
    $email = !empty($_POST['email']) ? $conexao->real_escape_string($_POST['email']) : NULL;
    $recompensa = !empty($_POST['recompensa']) ? $conexao->real_escape_string($_POST['recompensa']) : NULL;
    $descricao = $conexao->real_escape_string($_POST['descricao']);

    $diretorio = "uploads/";
    $nome_arquivo = time() . "_" . basename($_FILES["foto"]["name"]);
    $caminho_completo = $diretorio . $nome_arquivo;

    $check = getimagesize($_FILES["foto"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $caminho_completo)) {
            $titulo = isset($_POST['titulo']) ? $conexao->real_escape_string($_POST['titulo']) : NULL;

$sql = "INSERT INTO PostsFeed (usuario_id, tipo_feed, titulo, foto, telefone, email, recompensa, descricao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("isssssss", $usuario_id, $tipo_feed, $titulo, $caminho_completo, $telefone, $email, $recompensa, $descricao);

            if ($stmt->execute()) {
                if ($tipo_feed == 'adocao') {
                    header("Location: navbars/adocao.php");
                } elseif ($tipo_feed == 'encontre') {
                    header("Location: navbars/encontrePet.php");
                } elseif ($tipo_feed == 'saude') {
                    header("Location: navbars/saude_animal.php");
                }
                exit; 
            } else {
                echo "Erro ao salvar no banco: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    } else {
        echo "O arquivo não é uma imagem válida.";
    }
}
$conexao->close();
?>