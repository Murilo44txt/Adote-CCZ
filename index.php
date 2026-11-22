<?php
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

    <!-- Fontes originais do projeto -->
    <link href="https://fonts.googleapis.com/css2?family=Monomakh&family=Neuton:ital,wght@0,200;0,300;0,400;0,700;0,800;1,400&family=Noto+Znamenny+Musical+Notation&family=Roboto:ital,wght@0,100..900;1,100..900&family=Yanone+Kaffeesatz:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Open+Sans:wght@300;400;600&family=Pacifico&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body.home-fullscreen {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            
            /* Imagem estática de fundo responsiva */
            background-image: url('./images/headerCapaG.jpg') !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            background-color: transparent !important;
            position: relative;
        }

        /* Remove a altura fixa e o fundo duplicado do header antigo do style.css */
        body.home-fullscreen header {
            height: auto !important;
            min-height: unset !important;
            background: none !important;
            background-image: none !important;
            z-index: 10;
        }

        /* Bloco Central com Logotipo e Nome do Projeto */
        .hero-center {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            z-index: 5;
        }

        .hero-card {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 35px 50px;
            border-radius: 24px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.65);
            max-width: 90%;
        }

        /* Estilo da imagem LogoCCZ.png */
        .hero-logo-img {
            max-width: 160px;
            max-height: 130px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .hero-title {
            font-family: 'Fredericka the Great', cursive;
            font-size: clamp(2.5rem, 5vw, 4.2rem);
            color: #2c3e50;
            margin: 0;
            letter-spacing: 2px;
        }

        .hero-subtitle {
            font-family: 'Open Sans', sans-serif;
            font-size: clamp(0.95rem, 1.5vw, 1.2rem);
            color: #444;
            font-weight: 600;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .btn-perfil-nav {
            background-color: rgba(255, 255, 255, 0.92);
            color: #d35400;
            border: 2px solid #f39c12;
        }
        .btn-perfil-nav:hover {
            background-color: #f39c12;
            color: #fff;
        }
        .btn-sair-nav {
            background-color: rgba(255, 255, 255, 0.92);
            color: #212529;
            border: 1px solid #333;
        }
        .btn-sair-nav:hover {
            background-color: #212529;
            color: #fff;
        }

        footer.home-footer {
            background: rgba(0, 0, 0, 0.55);
            color: #f1f1f1;
            text-align: center;
            padding: 10px 15px;
            font-size: 0.8rem;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .hero-card {
                padding: 25px 25px;
            }
            .hero-logo-img {
                max-width: 120px;
                max-height: 95px;
            }
        }
    </style>
</head>
<body class="home-fullscreen">
    
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
            
            <div class="user-info d-flex flex-wrap align-items-center gap-2">
            <?php if (isset($_SESSION['loggedin']) &&$_SESSION['loggedin'] === true): ?>

                <?php if (in_array($_SESSION['nivel_acesso'], ['admin', 'master'])): ?>
                    <a href="admin_usuarios.php" class="btn btn-warning btn-sm fw-semibold">Gerenciar Usuários</a>
                    <a href="admin_animais.php" class="btn btn-info btn-sm fw-semibold">Gerenciar Animais</a>
                <?php endif; ?>

                <a href="perfil.php" class="btn btn-sm btn-perfil-nav d-inline-flex align-items-center gap-1 fw-semibold" title="Acessar e editar meu perfil">
                    <ion-icon name="person-circle-outline" style="font-size: 1.3rem;"></ion-icon>
                    <span>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                </a>

                <a href="logout.php" class="btn btn-sm btn-sair-nav fw-semibold">Sair</a>
                
            <?php else: ?>
                <a href="LoginMain.php" class="btn btn-warning fw-semibold">Login</a>
            <?php endif; ?>
            </div>
        </ul>
    </nav> 
</header>

<main class="hero-center animate__animated animate__fadeIn">
    <div class="hero-card">
        <img src="./images/LogoCCZ.png" alt="Logotipo Adote CCZ" class="hero-logo-img">

        <h1 class="hero-title">Adote CCZ</h1>
        <p class="hero-subtitle">Centro de Controle de Zoonoses • Adoção Responsável &amp; Bem-Estar Animal</p>
    </div>
</main>

<footer class="home-footer">
    <span>
        Projetado e Desenvolvido por Alunos do Centro Universitário Salesiano de São Paulo - Campus Maria Auxiliadora - Unisal Americana.
    </span>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./bootstrap-5.3.0-alpha1-