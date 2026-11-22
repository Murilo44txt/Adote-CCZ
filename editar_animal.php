<?php
session_start();
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php';
$id = (int)$_GET['id'];
$animal = null;

$sql = "SELECT * FROM Animais WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $animal = $result->fetch_assoc();
} else {
    die("Animal não encontrado.");
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Animal</title>
    <link rel="stylesheet" href="styles/registro.css">
</head>
<body>
    <div class="container">
        <h1>Editar Animal #<?php echo $animal['id']; ?></h1>
        <form action="processa_edicao_animal.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">

            <div class="form-group">
                <label>Foto Atual / Alterar Foto (Opcional):</label>
                <?php if (!empty($animal['foto']) && file_exists($animal['foto'])): ?>
                    <div style="margin: 8px 0;">
                        <img src="<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto atual" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
                        <br>
                        <label style="font-weight: normal; font-size: 0.9rem;">
                            <input type="checkbox" name="remover_foto" value="1"> Remover foto atual
                        </label>
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/png, image/jpeg, image/webp">
            </div>

            <div class="form-group">
                <label>Data e Hora da Entrada:</label>
                <input type="datetime-local" name="data_entrada" value="<?php echo date('Y-m-d\TH:i', strtotime($animal['data_entrada'])); ?>" required>
            </div>
            <div class="form-group">
                <label>Endereço de Origem:</label>
                <input type="text" name="endereco_origem" value="<?php echo htmlspecialchars($animal['endereco_origem'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Motivo do Recolhimento:</label>
                <textarea name="motivo_recolhimento" rows="3"><?php echo htmlspecialchars($animal['motivo_recolhimento'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Espécie:</label>
                <input type="text" name="especie" value="<?php echo htmlspecialchars($animal['especie']); ?>" required>
            </div>
            <div class="form-group">
                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="Macho" <?php echo ($animal['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                    <option value="Fêmea" <?php echo ($animal['sexo'] == 'Fêmea') ? 'selected' : ''; ?>>Fêmea</option>
                </select>
            </div>
            <div class="form-group">
                <label>Castrado:</label>
                <select name="castrado" required>
                    <option value="1" <?php echo ($animal['castrado'] == 1) ? 'selected' : ''; ?>>Sim</option>
                    <option value="0" <?php echo ($animal['castrado'] == 0) ? 'selected' : ''; ?>>Não</option>
                </select>
            </div>
            <div class="form-group">
                <label>Raça:</label>
                <input type="text" name="raca" value="<?php echo htmlspecialchars($animal['raca'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Chip:</label>
                <input type="text" name="chip" maxlength="15" value="<?php echo htmlspecialchars($animal['chip'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Pelagem:</label>
                <input type="text" name="pelagem" value="<?php echo htmlspecialchars($animal['pelagem'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Peso (kg):</label>
                <input type="number" step="0.01" name="peso" value="<?php echo $animal['peso']; ?>">
            </div>
            <div class="form-group">
                <label>Data de Nascimento:</label>
                <input type="date" name="data_nascimento" value="<?php echo $animal['data_nascimento']; ?>">
            </div>
            <div class="form-group">
                <label>Status de Adoção:</label>
                <select name="status_adocao" required>
                    <option value="Disponível" <?php echo ($animal['status_adocao'] == 'Disponível') ? 'selected' : ''; ?>>Disponível</option>
                    <option value="Em processo de adoção" <?php echo ($animal['status_adocao'] == 'Em processo de adoção') ? 'selected' : ''; ?>>Em processo de adoção</option>
                    <option value="Adotado" <?php echo ($animal['status_adocao'] == 'Adotado') ? 'selected' : ''; ?>>Adotado</option>
                </select>
            </div>

            <button type="submit">Salvar Alterações</button>
            <div class="back-button-container">
                <a href="admin_animais.php" class="back-button">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>