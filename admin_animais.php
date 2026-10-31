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
    <title>Gerenciar Animais</title>
    <link rel="stylesheet" href="styles/admin.css"> 
</head>
<body>
    <div class="container">
        <h1>Animais Cadastrados</h1>

        <a href="cadastro_animal.php" class="btn-add">Adicionar Novo Animal</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Espécie</th>
                    <th>Raça</th>
                    <th>Sexo</th>
                    <th>Castrado</th>
                    <th>Peso(KG)</th>
                    <th>Status</th>
                    <th>Data Entrada</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'conexao.php'; //
                // Busca todos os animais, ordenados pela data de entrada mais recente
                $sql = "SELECT id, especie, raca, sexo, castrado,peso, status_adocao, data_entrada FROM Animais ORDER BY data_entrada DESC";
                $result = $conexao->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["especie"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["raca"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["sexo"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["castrado"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["peso"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["status_adocao"]) . "</td>";
                        echo "<td>" . date("d/m/Y H:i", strtotime($row["data_entrada"])) . "</td>";
                        echo "<td class='actions'>";
                        
                        // Admin e Master podem editar
                        echo "<a href='editar_animal.php?id=" . $row["id"] . "' class='btn-edit'>Editar</a>";
                        
                        // SOMENTE Master pode excluir
                        if ($_SESSION['nivel_acesso'] == 'master') {
                            echo "<a href='excluir_animal.php?id=" . $row["id"] . "' class='btn-delete' onclick='return confirm(\"Tem certeza que deseja excluir este animal? A ação não pode ser desfeita.\");'>Excluir</a>";
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Nenhum animal cadastrado.</td></tr>";
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