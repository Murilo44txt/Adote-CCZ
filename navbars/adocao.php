<?php
// INICIA A SESSÃO PARA LER OS DADOS DE LOGIN
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Adote CCZ</title>
    
    <link rel="stylesheet" href="../styles/AdocaoStyle.css">
    <link rel="stylesheet" href="../styles/mobile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Monomakh&family=Neuton:ital,wght@0,200;0,300;0,400;0,700;0,800;1,400&family=Noto+Znamenny+Musical+Notation&family=Roboto:ital,wght@0,100..900;1,100..900&family=Yanone+Kaffeesatz:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"   rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Open+Sans:wght@300;400;600&family=Pacifico&display=swap" rel="stylesheet"/>
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

    <!-- Lógica a ser inserida dentro de adocao.php e encontrePet.php -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: orange;">Feed de Adoção</h2>
        <?php if (isset($_SESSION['loggedin'])): ?>
            <a href="../novo_post.php" class="btn btn-warning fw-bold">Criar Postagem</a>
        <?php endif; ?>
    </div>

    <div class="row d-flex justify-content-center">
        <?php
        include '../conexao.php';
        
        $filtro_feed = 'adocao'; // Mude para 'encontre' na página encontrePet.php

        $sql = "SELECT p.*, u.nome AS autor FROM PostsFeed p JOIN Usuarios u ON p.usuario_id = u.id WHERE p.tipo_feed = ? ORDER BY p.data_postagem DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $filtro_feed);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($post = $result->fetch_assoc()) {
                
                // Lógica de permissão: Dono do post, Admin ou Master podem editar/apagar
                $pode_modificar = false;
                if (isset($_SESSION['loggedin'])) {
                    if ($_SESSION['id'] == $post['usuario_id'] || $_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master') {
                        $pode_modificar = true;
                    }
                }
                ?>
                
                <!-- Estilo Card Instagram -->
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">@<?php echo htmlspecialchars($post['autor']); ?></span>
                            <small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($post['data_postagem'])); ?></small>
                        </div>
                        
                        <!-- Imagem do Feed -->
                        <img src="../<?php echo htmlspecialchars($post['foto']); ?>" class="card-img-top" alt="Foto do Pet" style="height: 400px; object-fit: cover;">
                        
                        <div class="card-body">
                            <p class="card-text mb-1">
                                <span class="fw-bold"><?php echo htmlspecialchars($post['autor']); ?></span> 
                                <?php echo nl2br(htmlspecialchars($post['descricao'])); ?>
                            </p>
                            
                            <hr class="my-2">
                            
                            <!-- Contatos -->
                            <p class="mb-0 fs-6"><strong style="color: #188C41;">Tel:</strong> <?php echo htmlspecialchars($post['telefone']); ?></p>
                            <p class="mb-0 fs-6"><strong style="color: #188C41;">E-mail:</strong> <?php echo htmlspecialchars($post['email']); ?></p>
                            
                            <?php if (!empty($post['recompensa'])): ?>
                                <p class="mb-0 fs-6 mt-1 text-danger fw-bold">Recompensa: <?php echo htmlspecialchars($post['recompensa']); ?></p>
                            <?php endif; ?>

                            <!-- Botões de Ação -->
                            <?php if ($pode_modificar): ?>
                                <div class="mt-3 d-flex gap-2">
                                    <a href="../editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary w-50">Editar</a>
                                    <a href="../excluir_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger w-50" onclick="return confirm('Deseja realmente apagar este post?');">Apagar</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php
            }
        } else {
            echo "<p class='text-center'>Nenhuma postagem encontrada neste feed.</p>";
        } // <-- A CHAVE DO ELSE QUE ESTAVA FALTANDO FOI ADICIONADA AQUI
        $stmt->close();
        ?>
    </div>

</section>

        <section class ="Comunidade"> 

        <h2>Comunidade:</h2>

        <div class="instagram-button-container">
    <a href="https://www.instagram.com/adocaoccz/" target="_blank" class="instagram-button"> <ion-icon name="logo-instagram"></ion-icon> Instagram Adoções</a>
        </div>

        <div class="facebook-button-container">
    <a href="https://www.facebook.com/adocaoccz" target="_blank" class="facebook-button"> <ion-icon name="logo-facebook"></ion-icon> Facebook Adoções</a>
        </div>

        </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js" 
    integrity="sha512-CEiA+78TpP9KAIPzqBvxUv8hy41jyI3f2uHi7DGp/Y/Ka973qgSdybNegWFciqh6GrN2UePx2KkflnQUbUhNIA==" 
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/shortcuts/sticky.min.js" 
    integrity="sha512-O5YCLUxCY2OBc4rfpnKUIgE4LGuCiW8CrJ7ty4hvkBAAKUOnlbomEFWd3a6ruRnFvO3LG2wiaGiJ1heOvdppvA==" 
    crossorigin="anonymous"></script>

    <script src="./bootstrap-5.3.0-alpha1-dist/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/ionicons@5.0.0/dist/ionicons.js"></script>

    <script src="./"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

</body>
</html>