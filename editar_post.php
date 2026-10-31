<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];
$usuario_logado = $_SESSION['id'];
$nivel_acesso = $_SESSION['nivel_acesso'];

// Busca os dados atuais da postagem
$sql = "SELECT * FROM PostsFeed WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Postagem não encontrada.");
}

// Verifica permissão (Dono do post ou Admin/Master)
if ($post['usuario_id'] != $usuario_logado && $nivel_acesso != 'admin' && $nivel_acesso != 'master') {
    die("Você não tem permissão para editar esta postagem.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Postagem - CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/AdocaoStyle.css"> 
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5" style="max-width: 600px;">
        <h2 class="text-center mb-4" style="color: orange;">Editar Postagem</h2>
        <div class="card shadow p-4 border-0 rounded-3">
            <form action="processa_edicao_post.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="foto_atual" value="<?php echo htmlspecialchars($post['foto']); ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Onde você quer postar?</label>
                    <select name="tipo_feed" id="tipo_feed" class="form-select" required>
                        <option value="adocao" <?php echo ($post['tipo_feed'] == 'adocao') ? 'selected' : ''; ?>>Feed de Adoção</option>
                        <option value="encontre" <?php echo ($post['tipo_feed'] == 'encontre') ? 'selected' : ''; ?>>Feed Encontre seu Pet</option>
                        
                        <?php if ($nivel_acesso == 'admin' || $nivel_acesso == 'master'): ?>
                            <option value="saude" <?php echo ($post['tipo_feed'] == 'saude') ? 'selected' : ''; ?>>Saúde Animal (Campanhas)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3" id="div_titulo" style="display: none;">
                    <label class="form-label fw-bold">Título do Comunicado</label>
                    <input type="text" name="titulo" id="titulo" class="form-control form-control-lg" value="<?php echo htmlspecialchars($post['titulo'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Foto do Pet/Banner (Deixe vazio para manter a atual)</label>
                    <div class="mb-2">
                        <img src="<?php echo htmlspecialchars($post['foto']); ?>" style="width: 120px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Telefone para Contato</label>
                    <input type="text" name="telefone" id="telefone" class="form-control" value="<?php echo htmlspecialchars($post['telefone'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">E-mail para Contato</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($post['email'] ?? ''); ?>">
                </div>

                <div class="mb-3" id="div_recompensa">
                    <label class="form-label fw-bold">Recompensa (Opcional)</label>
                    <input type="text" name="recompensa" class="form-control" value="<?php echo htmlspecialchars($post['recompensa'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="6" required><?php echo htmlspecialchars($post['descricao']); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn w-50 fw-bold" style="background-color: orange; color: black;">Salvar Alterações</button>
                    <a href="index.php" class="btn btn-secondary w-50 fw-bold">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectFeed = document.getElementById('tipo_feed');
            const divTitulo = document.getElementById('div_titulo');
            const inputTitulo = document.getElementById('titulo');
            const inputTelefone = document.getElementById('telefone');
            const divRecompensa = document.getElementById('div_recompensa');

            function ajustarFormulario() {
                if (selectFeed.value === 'saude') {
                    divTitulo.style.display = 'block';
                    inputTitulo.setAttribute('required', 'required');
                    inputTelefone.removeAttribute('required');
                    divRecompensa.style.display = 'none';
                } else {
                    divTitulo.style.display = 'none';
                    inputTitulo.removeAttribute('required');
                    inputTelefone.setAttribute('required', 'required');
                    divRecompensa.style.display = 'block';
                }
            }

            selectFeed.addEventListener('change', ajustarFormulario);
            ajustarFormulario(); // Executa ao carregar para definir estado inicial
        });
    </script>
</body>
</html>