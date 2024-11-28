<?php
session_start();

// Verificar se o usuário está logado e se o perfil é 'professor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'professor') {
    header("Location: login.php");
    exit;
}

require 'db.php'; // Conexão com o banco de dados

// Obter informações do professor
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

// Consultar dados do usuário (tabela 'users' no banco)
try {
    // Consultar informações do usuário na tabela 'users'
    $stmt_user = $pdo->prepare("SELECT nome, email FROM users WHERE id = :usuario_id");
    $stmt_user->bindParam(':usuario_id', $usuario_id);
    $stmt_user->execute();
    $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_info) {
        $usuario_nome = $user_info['nome'];
        $usuario_email = $user_info['email'];
    } else {
        throw new Exception("Usuário não encontrado.");
    }
    
    // Atualizar dados do usuário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novo_nome = trim($_POST['nome']);
        $novo_email = trim($_POST['email']);
        $nova_senha = trim($_POST['senha']);
        $confirmar_senha = trim($_POST['confirmar_senha']);
        
        // Validar senhas
        if (!empty($nova_senha) && $nova_senha === $confirmar_senha) {
            // Atualizar senha, se fornecida
            $stmt_update = $pdo->prepare("UPDATE users SET nome = :nome, email = :email, senha = :senha WHERE id = :usuario_id");
            $stmt_update->bindParam(':nome', $novo_nome);
            $stmt_update->bindParam(':email', $novo_email);
            $stmt_update->bindParam(':senha', password_hash($nova_senha, PASSWORD_DEFAULT)); // Criptografando a senha
            $stmt_update->bindParam(':usuario_id', $usuario_id);
        } else {
            // Atualizar sem senha
            $stmt_update = $pdo->prepare("UPDATE users SET nome = :nome, email = :email WHERE id = :usuario_id");
            $stmt_update->bindParam(':nome', $novo_nome);
            $stmt_update->bindParam(':email', $novo_email);
            $stmt_update->bindParam(':usuario_id', $usuario_id);
        }
        
        if ($stmt_update->execute()) {
            // Atualizar as variáveis de sessão com os novos dados
            $_SESSION['user_nome'] = $novo_nome;
            $_SESSION['user_email'] = $novo_email;
            header("Location: perfil.php");
            exit;
        } else {
            $erro = "Erro ao atualizar informações.";
        }
    }
} catch (PDOException $e) {
    die("Erro ao buscar ou atualizar dados: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Professor</title>
    <link rel="stylesheet" href="dashboard_professor.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.perfil-container {
    width: 80%;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-size: 14px;
    font-weight: bold;
}

input {
    padding: 8px;
    font-size: 14px;
    width: 100%;
    border-radius: 4px;
    border: 1px solid #ccc;
}

button {
    padding: 10px;
    background-color: #c53c3c;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #e02626;
}


</style>
</head>
<body>

<div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.jpg" id="logo" alt="Logo">
                <h2>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>
                <p>Email: <?php echo htmlspecialchars($usuario_email); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="professor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Início</a></li>
                    <li><a href="perfil.php"><i class="fas fa-user"></i> Perfil</a></li>
                    <li><a href="justificativa.php"><i class="fas fa-clipboard-check"></i> Justificativas</a></li>
                    <li><a href="reposicao.php"><i class="fas fa-sync-alt"></i> Reposições</a></li>
                    <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                    <li><a href="documentos.php"><i class="fas fa-file-alt"></i> Documentos</a></li>
                    <li class="sobre"><a href="sobre.php"><i class="fas fa-info-circle"></i> Sobre</a></li>
                    <li class="ajuda"><a href="ajuda.php"><i class="fas fa-question-circle"></i> Ajuda</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
        <div class="perfil-container">
        <h2>Perfil - <?php echo htmlspecialchars($usuario_nome); ?></h2>
        
        <!-- Formulário para editar informações do perfil -->
        <form action="perfil.php" method="POST">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_nome); ?>" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_email); ?>" required>
            
            <label for="senha">Nova Senha</label>
            <input type="password" id="senha" name="senha" placeholder="Digite a nova senha (se quiser alterar)">
            
            <label for="confirmar_senha">Confirmar Nova Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha">
            
            <?php if (isset($erro)): ?>
                <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>