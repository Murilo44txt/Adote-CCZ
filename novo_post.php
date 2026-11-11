<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: LoginMain.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Postagem - CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/AdocaoStyle.css"> 
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5" style="max-width: 650px;">
        <h2 class="text-center mb-4" style="color: orange;">Criar Publicação</h2>
        <div class="card shadow p-4 border-0 rounded-3">
            <form action="processa_post.php" method="POST" enctype="multipart/form-data">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Categoria do Feed</label>
                    <select name="tipo_feed" id="tipo_feed" class="form-select form-select-lg" required>
                        <option value="adocao">Feed de Adoção</option>
                        <option value="encontre">Feed Encontre seu Pet</option>
                        <?php if ($_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master'): ?>
                            <option value="saude">Saúde Animal (Comunicados Oficiais)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-4" id="div_titulo" style="display: none;">
                    <label class="form-label fw-bold" style="color: #188C41; font-size: 1.2rem;">Título do Comunicado</label>
                    <input type="text" name="titulo" id="titulo" class="form-control form-control-lg border-success" placeholder="Ex: Campanha de Vacinação 2024">
                    <small class="text-muted">Este título aparecerá em destaque no feed de Saúde.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Imagem / Foto</label>
                    <input type="file" name="foto" class="form-control" accept="image/*" required>
                </div>

                <div id="campos_contato">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Telefone para Contato</label>
                        <input type="text" name="telefone" id="telefone" class="form-control" placeholder="(19) 99999-9999">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">E-mail para Contato</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3" id="div_recompensa">
                    <label class="form-label fw-bold">Recompensa (Opcional)</label>
                    <input type="text" name="recompensa" class="form-control" placeholder="Ex: R$ 200,00">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Descrição / Conteúdo</label>
                    <textarea name="descricao" class="form-control" rows="6" required placeholder="Escreva os detalhes aqui..."></textarea>
                </div>

                <button type="submit" class="btn w-100 fw-bold mt-2" style="background-color: orange; color: black; padding: 12px;">Publicar no Feed</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('tipo_feed').addEventListener('change', function() {
            const isSaude = this.value === 'saude';
            const divTitulo = document.getElementById('div_titulo');
            const inputTitulo = document.getElementById('titulo');
            const inputTel = document.getElementById('telefone');
            const divRec = document.getElementById('div_recompensa');

            if (isSaude) {
                divTitulo.style.display = 'block';
                inputTitulo.required = true;
                inputTel.required = false; // Torna opcional para Saúde Animal
                divRec.style.display = 'none';
            } else {
                divTitulo.style.display = 'none';
                inputTitulo.required = false;
                inputTel.required = true; // Obrigatório para Adoção/Encontre
                divRec.style.display = 'block';
            }
        });
        // Disparar o evento ao carregar caso já venha selecionado
        document.getElementById('tipo_feed').dispatchEvent(new Event('change'));
    </script>
</body>
</html>