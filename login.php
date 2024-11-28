<?php
session_start();
require 'db.php';  // Conexão com o banco

// Processar cadastro
if (isset($_POST['register'])) {
    $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $perfil = $_POST['perfil'];  // Receber o perfil escolhido

    $stmt = $pdo->prepare("INSERT INTO users (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':perfil', $perfil);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao cadastrar.";
    }
}
// Processar login
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // Verificar se os campos não estão vazios
    if (empty($email) || empty($senha)) {
        echo "Por favor, preencha todos os campos.";
    } else {
        // Preparar a consulta para verificar se o email existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o email existe e a senha bate
        if ($user && password_verify($senha, $user['senha'])) {
            // Definir as variáveis de sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_perfil'] = $user['perfil'];  // Salvar o perfil na sessão

            // Verificar o perfil do usuário e redirecionar para a página correta
            if ($user['perfil'] == 'professor') {
                header("Location: professor_dashboard.php");  // Página para professores
            } elseif ($user['perfil'] == 'coordenador') {
                header("Location: dashboard.php");  // Página para coordenadores
            } else {
                echo "Perfil de usuário não reconhecido.";
            }
            exit;
        } else {
            echo "Email ou senha incorretos!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="shortcut icon" type="imagex/png" href="assets/img/logo.jpg">
</head>
<body>
    <div class="container">
        <!-- Formulário de Cadastro -->
        <div class="content first-content">
            <div class="first-column">
                <img src="img/logo.jpg" id="logo" alt="Logo">
                <h2 class="title title-primary">Bem Vindo De Volta!</h2>
                <p class="description description-primary">Para se manter conectado</p>
                <p class="description description-primary">Por favor faça o login</p>
                <button id="signin" class="btn btn-primary">Login</button>
            </div>    
            <div class="second-column">
                <h2 class="title title-second">Crie sua Conta</h2>
                <div class="social-media">
                    <ul class="list-social-media">
                        <a class="link-social-media" href="#"><li class="item-social-media"><i class="fab fa-facebook-f"></i></li></a>
                        <a class="link-social-media" href="#"><li class="item-social-media"><i class="fab fa-google-plus-g"></i></li></a>
                        <a class="link-social-media" href="#"><li class="item-social-media"><i class="fab fa-linkedin-in"></i></li></a>
                    </ul>
                </div><!-- social media -->
                <p class="description description-second">Ou use seu e-mail para se registrar:</p>
                <form action="register.php" method="POST">
                    <label class="label-input" for="nome">
                        <i class="fas fa-user icon-modify"></i>
                        <input type="text" name="nome" id="registerNome" placeholder="Nome Completo" required>
                    </label>
                    
                    <label class="label-input" for="email">
                        <i class="far fa-envelope icon-modify"></i>
                        <input type="email" name="email" id="registerEmail" placeholder="Email" required>
                    </label>
                    
                    <label class="label-input" for="senha">
                        <i class="fas fa-lock icon-modify"></i>
                        <input type="password" name="senha" id="registerPassword" placeholder="Senha" required>
                    </label>
                    
                    <label class="label-input" for="perfil">
                        <i class="fas fa-users icon-modify"></i>
                        <select name="perfil" id="registerPerfil" required>
                            <option value="professor">Professor</option>
                            <option value="coordenador">Coordenador</option>
                        </select>
                    </label>
                    
                    <button type="submit" class="btn btn-second" name="register">Registre-se</button>        
                </form>
            </div><!-- second column -->
        </div><!-- first content -->

        <!-- Formulário de Login -->
        <div class="content second-content">
            <div class="first-column">
                <img src="img/logo.jpg" id="logo" alt="Logo">
                <h2 class="title title-primary">Olá!</h2>
                <p class="description description-primary">Entre com suas informações</p>
                <p class="description description-primary">para dar início ao Sistema!</p>
                <button id="signup" class="btn btn-primary">Registrar</button>
            </div>
            <div class="second-column">
                <h2 class="title title-second">Login</h2>
                <form action="login.php" method="POST">
                    <label class="label-input" for="email">
                        <i class="far fa-envelope icon-modify"></i>
                        <input type="email" name="email" id="loginEmail" placeholder="Email" required>
                    </label>
                
                    <label class="label-input" for="senha">
                        <i class="fas fa-lock icon-modify"></i>
                        <input type="password" name="senha" id="loginPassword" placeholder="Senha" required>
                    </label>
                
                    <a class="password" href="reset_password.php" id="resetPasswordLink">Esqueceu sua Senha?</a>
                    <button type="submit" class="btn btn-second" name="login">Entrar</button>
                </form>
            </div><!-- second column -->
        </div><!-- second-content -->
    </div>
    </div>
    <script src="app.js"></script>
    <script>
        // Mostrar/Esconder Modal de recuperação de senha
        document.getElementById('resetPasswordLink').addEventListener('click', function() {
            document.getElementById('resetPasswordModal').style.display = 'block';
        });

        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('resetPasswordModal').style.display = 'none';
        });
    </script>
</body>
</html>
