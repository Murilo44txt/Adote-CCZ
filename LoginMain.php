<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login</title>
    <link rel="stylesheet" href="styles/registro.css">
    <script src="scripts/script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <form action="processa_login.php" method="POST">
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>
    </div>
    <button type="submit">Login</button>
</form>

        <div class="login-link">
                Ainda não tem uma Conta? <a href="registro.php">Registre-se Já!</a>
            </div>

        <div class="back-button-container">
            <a href="index.php" class="back-button">Voltar para Home</a>
        </div>
        
    </div>

    
    <script src="scripts/script.js"></script>
</body>
</html>