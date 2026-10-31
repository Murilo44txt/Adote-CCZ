<?php
// INICIA A SESSÃO PARA LER OS DADOS DE LOGIN
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saúde Animal - CCZ</title>
    
    <link rel="stylesheet" href="../styles/AdocaoStyle.css">
    <link rel="stylesheet" href="../styles/mobile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Monomakh&family=Neuton:ital,wght@0,200;0,300;0,400;0,700;0,800;1,400&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <header>
        <nav id="nav-esquerda">
            <ul id="icone-menu">
                <li id="menu"><ion-icon name="menu-outline"></ion-icon></li>
                <li id="menuX"><ion-icon name="close-outline"></ion-icon></li>
            </ul>

            <ul class="menu-principal">
                <li><a href="../index.php">Home</a></li>
                <li><a href="FaleConosco.php">Fale Conosco</a></li>
                <li><a href="adocao.php">Adoção</a></li>
                <li><a href="encontrePet.php">Encontre seu Pet</a></li>
                <li><a href="saude_animal.php">Saúde Animal</a></li>
                
                <div class="user-info">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        
                    <?php if ($_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master'): ?>
                        <a href="../admin_usuarios.php" class="btn btn-warning me-2">Gerenciar Usuários</a>
                        <a href="../admin_animais.php" class="btn btn-info me-2">Gerenciar Animais</a>
                    <?php endif; ?>

                    <span class="navbar-text me-3" style="color: orange;">
                        Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-dark" style="color: orange;">Sair</a>
                
                <?php else: ?>
                    <a href="../LoginMain.php" class="btn btn-warning">Login</a>
                <?php endif; ?>
                </div>
            </ul>
        </nav> 
    </header>

    <main class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: orange;">Saúde Animal: Campanhas e Comunicados</h2>
            
            <?php 
            // RESTRIÇÃO: Apenas admin e master podem criar postagens nesta página
            if (isset($_SESSION['loggedin']) && ($_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master')): 
            ?>
                <a href="../novo_post.php" class="btn fw-bold" style="background-color: orange; color: white;">Nova Campanha</a>
            <?php endif; ?>
        </div>

        <div class="row d-flex justify-content-center">
            <?php
            include '../conexao.php';
            
            $filtro_feed = 'saude'; 

            $sql = "SELECT p.*, u.nome AS autor FROM PostsFeed p JOIN Usuarios u ON p.usuario_id = u.id WHERE p.tipo_feed = ? ORDER BY p.data_postagem DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $filtro_feed);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    
                    // Lógica de permissão para Editar/Apagar
                    $pode_modificar = false;
                    if (isset($_SESSION['loggedin']) && ($_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master')) {
                        $pode_modificar = true;
                    }
                    ?>
                    
                    <div class="col-md-8 col-lg-7 mb-5">
    <div class="card shadow border-0">
        <div class="card-header py-3" style="background-color: orange; color: black;">
            <h5 class="mb-0 fw-bold text-center"; style="color: white">COMUNICADO OFICIAL - CCZ</h5>
        </div>
        
        <div class="card-body p-4">
            <?php if (!empty($post['titulo'])): ?>
                <h2 class="text-center mb-4 fw-bold" style="color: orange; border-bottom: 2px solid #eee; padding-bottom: 15px;">
                    <?php echo htmlspecialchars($post['titulo']); ?>
                </h2>
            <?php endif; ?>

            <img src="../<?php echo htmlspecialchars($post['foto']); ?>" class="img-fluid rounded mb-4 shadow-sm" alt="Campanha">

            <div class="card-text" style="text-align: justify; font-size: 1.1rem; line-height: 1.8; color: #333;">
                <?php echo nl2br(htmlspecialchars($post['descricao'])); ?>
            </div>

            <?php if (!empty($post['telefone']) || !empty($post['email'])): ?>
                <div class="mt-4 p-3 bg-light rounded">
                    <p class="mb-1"><strong>Informações adicionais:</strong></p>
                    <?php if(!empty($post['telefone'])): ?> <p class="mb-0">Tel: <?php echo htmlspecialchars($post['telefone']); ?></p> <?php endif; ?>
                    <?php if(!empty($post['email'])): ?> <p class="mb-0">E-mail: <?php echo htmlspecialchars($post['email']); ?></p> <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($pode_modificar): ?>
                <div class="mt-4 d-flex gap-2">
                    <a href="../editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm px-4">Editar</a>
                    <a href="../excluir_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-danger btn-sm px-4" onclick="return confirm('Apagar este comunicado?');">Apagar</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white text-muted text-end">
            Publicado em: <?php echo date("d/m/Y", strtotime($post['data_postagem'])); ?>
        </div>
    </div>
</div>
                    <?php
                }
            } else {
                echo "<p class='text-center mt-5'>Nenhuma campanha ou aviso registrado no momento.</p>";
            }
            $stmt->close();
            ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/ionicons@5.0.0/dist/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>