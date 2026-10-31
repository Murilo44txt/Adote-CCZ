<?php
session_start();
// Se o usuário não estiver logado ou não for admin/master, redireciona
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php';
$id = $_GET['id'];
// Busca os dados do usuário a ser editado
$sql = "SELECT nome, email, cpf, telefone, nivel_acesso FROM Usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
if (!$usuario) {
    die("Usuário não encontrado.");
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="styles/registro.css">
</head>
<body>
    <div class="container">
        <h1>Editar Usuário</h1>
        <form action="processa_edicao_usuario.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
            
            <?php // Apenas o master pode ver e alterar o nível de acesso
            if ($_SESSION['nivel_acesso'] == 'master'): ?>
            <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso:</label>
                <select name="nivel_acesso" id="nivel_acesso">
                    <option value="comum" <?php echo ($usuario['nivel_acesso'] == 'comum') ? 'selected' : ''; ?>>Comum</option>
                    <option value="admin" <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="master" <?php echo ($usuario['nivel_acesso'] == 'master') ? 'selected' : ''; ?>>Master</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" name="senha">
            </div>
            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>