<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 10px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <form action="reset_form.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $_GET['token'] ?? ''; ?>" required>
            <input type="password" name="senha" placeholder="Nova Senha" required>
            <button type="submit">Redefinir Senha</button>
        </form>
        <p class="message">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require 'db.php';
                $token = $_POST['token'];
                $nova_senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("SELECT * FROM users WHERE token_redefinicao = :token AND token_expira > NOW()");
                $stmt->bindParam(':token', $token);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $stmt = $pdo->prepare("UPDATE users SET senha = :senha, token_redefinicao = NULL, token_expira = NULL WHERE id = :id");
                    $stmt->bindParam(':senha', $nova_senha);
                    $stmt->bindParam(':id', $user['id']);
                    $stmt->execute();
                    echo "Senha redefinida com sucesso!";
                } else {
                    echo "Token inválido ou expirado!";
                }
            }
            ?>
        </p>
    </div>
</body>
</html>
