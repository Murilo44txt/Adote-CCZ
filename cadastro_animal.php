<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Animal</title>
    <link rel="stylesheet" href="styles/registro.css"> 
    <script src="scripts/script.js"></script>
</head>
<body>

    <div class="container">
    <h2>Cadastro de Novo Animal</h2>
    <form action="processa_animal.php" method="POST">

        <div class="form-group">
            <label for="data_entrada">Data de Entrada:</label>
            <input type="datetime-local" id="data_entrada" name="data_entrada" required>
        </div>

        <div class="form-group">
            <label for="endereco_origem">Endereço de Origem:</label>
            <input type="text" id="endereco_origem" name="endereco_origem">
        </div>

        <div class="form-group">
            <label for="motivo_recolhimento">Motivo do Recolhimento:</label>
            <textarea id="motivo_recolhimento" name="motivo_recolhimento" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="especie">Espécie:</label>
            <input type="text" id="especie" name="especie" value="Cachorro" required>
        </div>

        <div class="form-group">
            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
                <option value="Macho">Macho</option>
                <option value="Fêmea">Fêmea</option>
            </select>
        </div>

        <div class="form-group">
                <label>Castrado:</label>
                <select name="castrado" required>
                    <option value="1" id="castrado_sim" name="castrado"  required> Sim</option>
                    <option value="0" id="castrado_nao" name="castrado"  required> Não</option>
                </select>
            </div>

        <div class="form-group">
            <label for="raca">Raça:</label>
            <input type="text" id="raca" name="raca" placeholder="Ex: SRD">
        </div>

        <div class="form-group">
            <label for="chip">Chip (15 dígitos):</label>
            <input type="text" id="chip" name="chip" pattern="\d{15}" title="Digite 15 dígitos numéricos.">
        </div>
        <div class="form-group">
            <label for="pelagem">Pelagem:</label>
            <input type="text" id="pelagem" name="pelagem">
        </div>

        <div class="form-group">
            <label for="peso">Peso (kg):</label>
            <input type="number" id="peso" name="peso" step="0.01">
        </div>
        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento (aproximada):</label>
            <input type="date" id="data_nascimento" name="data_nascimento">
        </div>

        <div class="form-group">
            <label for="status_adocao">Status de Adoção:</label>
            <select id="status_adocao" name="status_adocao" required>
                <option value="Disponível">Disponível</option>
                <option value="Em processo de adoção">Em processo de adoção</option>
                <option value="Adotado">Adotado</option>
            </select>
        </div>

        <button type="submit">Cadastrar Animal</button>
    </form>
    <div class="back-button-container">
            <a href="index.php" class="back-button">Voltar para Home</a>
        </div>
    </div>

    </div>
</body>
</html>