<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: LoginMain.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_id = (int)$_POST['id'];
    $usuario_logado = $_SESSION['id'];
    $nivel_acesso = $_SESSION['nivel_acesso'];

    $tipo_feed = $_POST['tipo_feed'];
    
    // Trava de Segurança
    if ($tipo_feed == 'saude' && ($nivel_acesso != 'admin' && $nivel_acesso != 'master')) {
        die("Erro de permissão: Apenas administradores podem gerenciar o feed de Saúde Animal.");
    }

    $titulo = !empty($_POST['titulo']) ? $conexao->real_escape_string($_POST['titulo']) : NULL;
    $telefone = !empty($_POST['telefone']) ? $conexao->real_escape_string($_POST['telefone']) : NULL;
    $email = !empty($_POST['email']) ? $conexao->real_escape_string($_POST['email']) : NULL;
    $recompensa = !empty($_POST['recompensa']) ? $conexao->real_escape_string($_POST['recompensa']) : NULL;
    $descricao = $conexao->real_escape_string($_POST['descricao']);
    
    $caminho_foto = $_POST['foto_atual'];

    // Verifica permissão no banco
    $sql_check = "SELECT usuario_id FROM PostsFeed WHERE id = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $post_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $post_check = $result_check->fetch_assoc();

    if ($post_check && ($post_check['usuario_id'] == $usuario_logado || $nivel_acesso == 'admin' || $nivel_acesso == 'master')) {
        
        // Processa nova foto se enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $diretorio = "uploads/";
            $nome_arquivo = time() . "_" . basename($_FILES["foto"]["name"]);
            $novo_caminho = $diretorio . $nome_arquivo;
            
            if (getimagesize($_FILES["foto"]["tmp_name"])) {
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $novo_caminho)) {
                    if (file_exists($caminho_foto)) {
                        unlink($caminho_foto);
                    }
                    $caminho_foto = $novo_caminho;
                }
            }
        }

        // UPDATE incluindo a nova coluna titulo
        $sql = "UPDATE PostsFeed SET tipo_feed = ?, titulo = ?, foto = ?, telefone = ?, email = ?, recompensa = ?, descricao = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssssssi", $tipo_feed, $titulo, $caminho_foto, $telefone, $email, $recompensa, $descricao, $post_id);

        if ($stmt->execute()) {
            if ($tipo_feed == 'adocao') {
                header("Location: navbars/adocao.php");
            } elseif ($tipo_feed == 'encontre') {
                header("Location: navbars/encontrePet.php");
            } else {
                header("Location: navbars/saude_animal.php");
            }
            exit;
        } else {
            echo "Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Acesso negado.";
    }
}
$conexao->close();
?>