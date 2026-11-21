<?php
session_start();
include 'conexao.php';

// Garante que apenas usuários logados acessem o perfil
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
    header("Location: LoginMain.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$sucesso = "";
$erro = "";

// Busca os dados atuais do usuário
$sql = "SELECT nome, email, cpf, telefone, foto_perfil, status_2fa, senha_hash FROM Usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Processamento dos formulários
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. ATUALIZAR DADOS PESSOAIS E FOTO DE PERFIL
    if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar_dados') {
        $novo_nome     = trim($_POST['nome']);
        $novo_email    = trim($_POST['email']);
        $novo_telefone = trim($_POST['telefone']);
        $caminho_foto  = $usuario['foto_perfil'];

        // Verifica se o novo e-mail já pertence a outro usuário
        $sql_check = "SELECT id FROM Usuarios WHERE email = ? AND id != ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("si", $novo_email, $usuario_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $erro = "Este e-mail já está sendo utilizado por outra conta.";
        } else {
            // Upload da Foto de Perfil (caso um arquivo tenha sido selecionado)
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));

                if (in_array($extensao, $extensoes_permitidas)) {
                    $pasta_destino = 'uploads/perfis/';
                    if (!is_dir($pasta_destino)) {
                        mkdir($pasta_destino, 0755, true);
                    }

                    $novo_nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extensao;
                    $destino_completo  = $pasta_destino . $novo_nome_arquivo;

                    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino_completo)) {
                        // Apaga a foto anterior do servidor para não acumular lixo
                        if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])) {
                            unlink($usuario['foto_perfil']);
                        }
                        $caminho_foto = $destino_completo;
                    } else {
                        $erro = "Falha ao salvar a imagem no servidor.";
                    }
                } else {
                    $erro = "Formato de imagem inválido. Use JPG, PNG ou WEBP.";
                }
            }

            if (empty($erro)) {
                $sql_up = "UPDATE Usuarios SET nome = ?, email = ?, telefone = ?, foto_perfil = ? WHERE id = ?";
                $stmt_up = $conexao->prepare($sql_up);
                $stmt_up->bind_param("ssssi", $novo_nome, $novo_email, $novo_telefone, $caminho_foto, $usuario_id);

                if ($stmt_up->execute()) {
                    // Atualiza o nome na sessão imediatamente
                    $_SESSION['nome'] = $novo_nome;
                    $usuario['nome'] = $novo_nome;
                    $usuario['email'] = $novo_email;
                    $usuario['telefone'] = $novo_telefone;
                    $usuario['foto_perfil'] = $caminho_foto;

                    $sucesso = "Dados do perfil atualizados com sucesso!";
                } else {
                    $erro = "Erro ao atualizar dados no banco.";
                }
            }
        }
    }

    // 2. ALTERAR SENHA
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
        $senha_atual     = $_POST['senha_atual'];
        $nova_senha      = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        if (!password_verify($senha_atual, $usuario['senha_hash'])) {
            $erro = "A senha atual informada está incorreta.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $erro = "A nova senha e a confirmação não coincidem.";
        } elseif (strlen($nova_senha) < 6) {
            $erro = "A nova senha deve ter pelo menos 6 caracteres.";
        } else {
            $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_senha = "UPDATE Usuarios SET senha_hash = ? WHERE id = ?";
            $stmt_s = $conexao->prepare($sql_senha);
            $stmt_s->bind_param("si", $novo_hash, $usuario_id);

            if ($stmt_s->execute()) {
                $usuario['senha_hash'] = $novo_hash;
                $sucesso = "Senha alterada com sucesso!";
            } else {
                $erro = "Erro ao atualizar a senha.";
            }
        }
    }
}

// Define a foto padrão caso o usuário ainda não tenha enviado uma
$foto_exibicao = (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil']))
    ? $usuario['foto_perfil']
    : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Adote CCZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width: 850px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0" style="color: orange;">Meu Perfil</h3>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar ao Início</a>
    </div>

    <?php if ($sucesso): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $sucesso ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $erro ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Coluna Esquerda: Dados Pessoais e Foto -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 p-4">
                <h5 class="fw-bold mb-3">Informações Pessoais</h5>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="atualizar_dados">

                    <!-- Prévia da Foto -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="<?= htmlspecialchars($foto_exibicao) ?>" alt="Foto de Perfil" class="rounded-circle border" style="width: 85px; height: 85px; object-fit: cover;">
                        <div>
                            <label for="foto_perfil" class="form-label small fw-bold mb-1">Alterar foto de perfil</label>
                            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp">
                            <small class="text-muted">JPG, PNG ou WEBP.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">CPF (Não editável)</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($usuario['cpf']) ?>" disabled>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">Salvar Alterações</button>
                </form>
            </div>
        </div>

        <!-- Coluna Direita: Segurança (2FA + Senha) -->
        <div class="col-md-5">
            <!-- Card Autenticador 2FA -->
            <div class="card shadow-sm border-0 p-4 mb-4">
                <h5 class="fw-bold mb-2">Autenticação em 2 Etapas</h5>
                <p class="small text-muted mb-3">Proteja sua conta com Google Authenticator ou Microsoft Authenticator.</p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-bold">Status atual:</span>
                    <?php if ($usuario['status_2fa'] === 'ativo'): ?>
                        <span class="badge bg-success">Ativado</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Desativado</span>
                    <?php endif; ?>
                </div>

                <a href="configurar_2fa.php" class="btn btn-outline-dark w-100 btn-sm fw-bold">
                    <?= ($usuario['status_2fa'] === 'ativo') ? 'Gerenciar Autenticador' : 'Ativar Autenticador (2FA)' ?>
                </a>
            </div>

            <!-- Card Alterar Senha -->
            <div class="card shadow-sm border-0 p-4">
                <h5 class="fw-bold mb-3">Alterar Senha</h5>

                <form method="POST">
                    <input type="hidden" name="acao" value="alterar_senha">

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Senha Atual</label>
                        <input type="password" name="senha_atual" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Nova Senha</label>
                        <input type="password" name="nova_senha" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirmar Nova Senha</label>
                        <input type="password" name="confirmar_senha" class="form-control form-control-sm" required>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 btn-sm fw-bold">Atualizar Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>