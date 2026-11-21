<?php
// Inicia a sessão para que possamos usar as variáveis de login
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Adote CCZ</title>
    
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="styles/mobile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Monomakh&family=Neuton:ital,wght@0,200;0,300;0,400;0,700;0,800;1,400&family=Noto+Znamenny+Musical+Notation&family=Roboto:ital,wght@0,100..900;1,100..900&family=Yanone+Kaffeesatz:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Open+Sans:wght@300;400;600&family=Pacifico&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    
<header id="topo">
    <nav id="nav-esquerda">
        <ul id="icone-menu">
            <li id="menu"><ion-icon name="menu-outline"></ion-icon></li>
            <li id="menuX"><ion-icon name="close-outline"></ion-icon></li>
        </ul>

        <ul class="menu-principal">
            <li><a href="index.php">Home</a></li>
            <li><a href="navbars/FaleConosco.php">Fale Conosco</a></li>
            <li><a href="navbars/adocao.php">Adoção</a></li>
            <li><a href="navbars/encontrePet.php">Encontre seu Pet</a></li>
            <li><a href="navbars/saude_animal.php">Saúde Animal</a></li>
            
            <div class="user-info d-flex align-items-center">
            <?php if (isset($_SESSION['loggedin']) &&$_SESSION['loggedin'] === true): ?>

                <?php if ($_SESSION['nivel_acesso'] == 'admin' || $_SESSION['nivel_acesso'] == 'master'): ?>
                    <a href="admin_usuarios.php" class="btn btn-warning me-2">Gerenciar Usuários</a>
                    <a href="admin_animais.php" class="btn btn-info me-2">Gerenciar Animais</a>
                <?php endif; ?>

                <a href="perfil.php" class="btn btn-outline-warning me-2 d-inline-flex align-items-center gap-1 fw-semibold" title="Acessar e editar meu perfil">
                    <ion-icon name="person-circle-outline" style="font-size: 1.4rem;"></ion-icon>
                    <span>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                </a>

                <a href="logout.php" class="btn btn-outline-dark" style="color: orange;">Sair</a>
                
            <?php else: ?>
                <a href="LoginMain.php" class="btn btn-warning">Login</a>
            <?php endif; ?>
            </div>
        </ul>

    </nav> 
</header>

    <main>
        <section class="sobre">
            <div class="sobre-mestre">
                <div class="row">
                    <div class="col-lg-3">
                        <img src="./images/sobre001.jpg" class="bd-placeholder-img rounded-circle" 
                        width="140" height="140" role="img" aria-label="Placeholder: 140x140" 
                        preserveAspectRatio="xMidYMid slice" focusable="false">
                        <h2> Adoção</h2> 
                        <p><a class="btn btn-secondary" href="navbars/adocao.php"> Clique aqui</a></p>
                    </div>
                        
                    <div class="col-lg-3">
                        <img src="./images/sobre002.jpg" class="bd-placeholder-img rounded-circle" 
                        width="140" height="140" role="img" aria-label="Placeholder: 140x