<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
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
        <h2>Recuperar Senha</h2>
        <form action="reset_password.php" method="POST">
            <input type="email" name="email" placeholder="Digite seu email" required>
            <button type="submit">Enviar link de recuperação</button>
        </form>
        <p class="message">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require 'db.php';
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $token = bin2hex(random_bytes(50));
                    $token_expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $stmt = $pdo->prepare("UPDATE users SET token_redefinicao = :token, token_expira = :expira WHERE email = :email");
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':expira', $token_expira);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    echo "Link de recuperação enviado para o email!";
                } else {
                    echo "Email não encontrado!";
                }
            }
            ?>
        </p>
    </div>
</body>
</html>
