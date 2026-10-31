<?php
// Inicia a sessão e verifica o nível de acesso
session_start();

// Se o usuário não estiver logado ou não for admin/master, redireciona para a home
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <div class="container">
        <h1>Usuários Cadastrados</h1>

        <?php
        // Apenas o master pode criar novos usuários (inclusive admins)
        if ($_SESSION['nivel_acesso'] == 'master') {
            echo '<a href="registro.php" class="btn-add-user">Adicionar Novo Usuário</a>';
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'conexao.php';
                $sql = "SELECT id, nome, email, nivel_acesso FROM Usuarios ORDER BY nome ASC";
                $result = $conexao->query($sql);

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nome"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                    echo "<td>" . htmlspecialchars(ucfirst($row["nivel_acesso"])) . "</td>";
                    echo "<td class='actions'>";

                    // Lógica de permissões para as ações
                    $podeEditar = false;
                    $podeExcluir = false;

                    // Master pode editar e excluir todos, exceto ele mesmo
                    if ($_SESSION['nivel_acesso'] == 'master' && $_SESSION['id'] != $row['id']) {
                        $podeEditar = true;
                        $podeExcluir = true;
                    }
                    // Admin normal pode editar e excluir apenas usuários 'comum'
                    else if ($_SESSION['nivel_acesso'] == 'admin' && $row['nivel_acesso'] == 'comum') {
                        $podeEditar = true;
                        $podeExcluir = true;
                    }

                    if ($podeEditar) {
                        echo "<a href='editar_usuario.php?id=" . $row["id"] . "' class='btn-edit'>Editar</a>";
                    }
                    if ($podeExcluir) {
                        echo "<a href='excluir_usuario.php?id=" . $row["id"] . "' class='btn-delete' onclick='return confirm(\"Tem certeza?\");'>Excluir</a>";
                    }

                    echo "</td>";
                    echo "</tr>";
                }
                $conexao->close();
                ?>
            </tbody>
        </table>
        <div class="back-button-container">
            <a href="index.php" class="back-button">Voltar para Home</a>
        </div>
    </div>
</body>
</html>