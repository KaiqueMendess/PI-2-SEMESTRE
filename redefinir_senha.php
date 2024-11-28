<?php
// Código PHP para enviar o e-mail de recuperação de senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    
    // Conectar ao banco de dados
    $pdo = new PDO('mysql:host=localhost;dbname=seubanco', 'usuario', 'senha');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o e-mail existe no banco de dados
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Gerar token de recuperação de senha
        $token = bin2hex(random_bytes(50));

        // Atualizar o token no banco de dados
        $sql = "UPDATE usuarios SET token = :token WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Enviar o link de recuperação por e-mail
        $link_recuperacao = "http://localhost/Sistemfatec/redefinir_senha.php?token=$token";
        $assunto = "Recuperação de Senha";
        $mensagem = "Clique no link abaixo para redefinir sua senha:\n$link_recuperacao";
        $headers = "From: seu_email@outlook.com";

        if (mail($email, $assunto, $mensagem, $headers)) {
            echo "<script>alert('Link de recuperação enviado para o seu e-mail!');</script>";
        } else {
            echo "<script>alert('Erro ao enviar o link de recuperação.');</script>";
        }
    } else {
        echo "<script>alert('E-mail não encontrado.');</script>";
    }
}

// Código PHP para processar o token e a nova senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['senha']) && isset($_POST['token'])) {
    $nova_senha = $_POST['senha'];
    $token = $_POST['token'];

    // Conectar ao banco de dados
    $pdo = new PDO('mysql:host=localhost;dbname=seubanco', 'usuario', 'senha');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o token é válido
    $sql = "SELECT * FROM usuarios WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // O token é válido, atualizar a senha (hashificando-a antes de salvar)
        $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);

        // Atualizar a senha no banco de dados
        $sql = "UPDATE usuarios SET senha = :senha, token = NULL WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        echo "<script>alert('Senha redefinida com sucesso!');</script>";
    } else {
        echo "<script>alert('Token inválido ou expirado.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #007bff;
        }
        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
            color: #444;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #888;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recuperação de Senha</h2>

    <!-- Formulário para enviar o e-mail de recuperação -->
    <form action="recuperar_senha.php" method="POST">
        <label for="email">Digite seu e-mail</label>
        <input type="email" name="email" required placeholder="Seu e-mail">
        <button type="submit">Enviar link de recuperação</button>
    </form>

    <div class="footer">
        <p>Já tem uma conta? <a href="login.php">Login</a></p>
    </div>

    <!-- Formulário de redefinir senha (somente se o token estiver presente) -->
    <?php if (isset($_GET['token'])): ?>
        <h2>Redefinir Senha</h2>
        <form action="resetar_senha.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <label for="senha">Nova senha</label>
            <input type="password" name="senha" required placeholder="Nova senha">
            <button type="submit">Redefinir senha</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>
