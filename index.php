<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="shortcut icon" type="imagex/png" href="assets/img/logo.jpg">
</head>
<body>
    <div class="container">
        <!-- Formulário de Cadastro -->
        <div class="content first-content">
            <div class="first-column">
                <img src="assets/img/logo.jpg" id="logo" alt="Logo">
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
                <form id="registerForm">
                    <label class="label-input" for="email">
                        <i class="far fa-envelope icon-modify"></i>
                        <input type="email" name="email" id="registerEmail" placeholder="Email" required>
                    </label>
                    
                    <label class="label-input" for="password">
                        <i class="fas fa-lock icon-modify"></i>
                        <input type="password" name="password" id="registerPassword" placeholder="Senha" required>
                    </label>
                    
                    <button type="submit" id="registerBtn" class="btn btn-second">Registre-se</button>        
                </form>
                <div id="message"></div>
            </div><!-- second column -->
        </div><!-- first content -->

        <!-- Formulário de Login -->
        <div class="content second-content">
            <div class="first-column">
                <img src="assets/img/logo.jpg" id="logo" alt="Logo">
                <h2 class="title title-primary">Olá, Agricultor!</h2>
                <p class="description description-primary">Entre com suas informações</p>
                <p class="description description-primary">para dar início ao Monitoramento!</p>
                <button id="signup" class="btn btn-primary">Sign Up</button>
            </div>
            <div class="second-column">
                <h2 class="title title-second">Login</h2>
                <form id="loginForm">
                    <label class="label-input" for="email">
                        <i class="far fa-envelope icon-modify"></i>
                        <input type="email" name="email" id="loginEmail" placeholder="Email" required>
                    </label>
                
                    <label class="label-input" for="password">
                        <i class="fas fa-lock icon-modify"></i>
                        <input type="password" name="password" id="loginPassword" placeholder="Senha" required>
                    </label>
                
                    <a class="password" href="#" id="resetPasswordLink">Esqueceu sua Senha?</a>
                    <button type="submit" id="loginBtn" class="btn btn-second">Entrar</button>
                </form>
                <div id="loginMessage"></div>
            </div><!-- second column -->
        </div><!-- second-content -->
    </div>

    <!-- Modal de recuperação de senha -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Recuperar Senha</h3>
            <form id="resetForm">
                <label for="email">
                    <i class="far fa-envelope icon-modify"></i>
                    <input type="email" name="email" id="resetEmail" placeholder="Email" required>
                </label>
                <button type="submit" id="resetBtn" class="btn btn-second">Recuperar Senha</button>
            </form>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.0.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.2/firebase-auth.js"></script>
    <script src="assets/js/app.js"></script>
    <script type="module">
    import { 
        initializeApp 
    } from 'https://www.gstatic.com/firebasejs/9.0.2/firebase-app.js';
    import { 
        getAuth, 
        createUserWithEmailAndPassword, 
        signInWithEmailAndPassword, 
        sendPasswordResetEmail, 
        onAuthStateChanged 
    } from 'https://www.gstatic.com/firebasejs/9.0.2/firebase-auth.js';

    // Configuração do Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyBKxeTlLyWREGLcENJD4iMiM8_feKKiQO0",
        authDomain: "pi3semestre-23089.firebaseapp.com",
        databaseURL: "https://pi3semestre-23089-default-rtdb.firebaseio.com",
        projectId: "pi3semestre-23089",
        storageBucket: "pi3semestre-23089.firebasestorage.app",
        messagingSenderId: "115992833105",
        appId: "1:115992833105:web:6743b38c81aa66dd5edc70",
        measurementId: "G-HBJCYZEP19"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    // Validações de entrada
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function validatePassword(password) {
        return password.length >= 6; // Firebase exige no mínimo 6 caracteres
    }

    function showMessage(elementId, message, isError = true) {
        const messageElement = document.getElementById(elementId);
        messageElement.innerHTML = message;
        messageElement.style.color = isError ? 'red' : 'green';
    }

    // Registro de usuário
    document.getElementById('registerForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;

        // Validações
        if (!validateEmail(email)) {
            showMessage('message', 'Email inválido.', true);
            return;
        }

        if (!validatePassword(password)) {
            showMessage('message', 'A senha deve ter pelo menos 6 caracteres.', true);
            return;
        }

        try {
            await createUserWithEmailAndPassword(auth, email, password);
            showMessage('message', 'Usuário registrado com sucesso!', false);
            // Redireciona para a dashboard
            window.location.href = 'dashboard.php';
        } catch (error) {
            showMessage('message', `Erro ao registrar: ${error.message}`, true);
        }
    });

    // Login de usuário
    document.getElementById('loginForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        // Validações
        if (!validateEmail(email)) {
            showMessage('loginMessage', 'Email inválido.', true);
            return;
        }

        try {
            await signInWithEmailAndPassword(auth, email, password);
            showMessage('loginMessage', 'Login realizado com sucesso!', false);
            // Redireciona para a dashboard
            window.location.href = 'dashboard.php';
        } catch (error) {
            showMessage('loginMessage', `Erro ao fazer login: ${error.message}`, true);
        }
    });

    // Recuperar senha
    document.getElementById('resetForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const email = document.getElementById('resetEmail').value;

        if (!validateEmail(email)) {
            alert('Email inválido.');
            return;
        }

        try {
            await sendPasswordResetEmail(auth, email);
            alert(`Link de recuperação enviado para ${email}`);
            document.getElementById('resetPasswordModal').style.display = 'none';
        } catch (error) {
            alert(`Erro ao enviar link de recuperação de senha: ${error.message}`);
        }
    });

    // Garantir que o usuário esteja autenticado antes de acessar a dashboard
    onAuthStateChanged(auth, (user) => {
        if (user) {
            console.log('Usuário autenticado:', user.email);
        } else {
            console.log('Nenhum usuário autenticado.');
            // Redireciona para a página de login se o usuário não estiver autenticado
            if (window.location.pathname.includes('dashboard.php')) {
                window.location.href = 'index.php';
            }
        }
    });

    // Mostrar/Esconder Modal de recuperação de senha
    document.getElementById('resetPasswordLink').addEventListener('click', function () {
        document.getElementById('resetPasswordModal').style.display = 'block';
    });

    document.querySelector('.close').addEventListener('click', function () {
        document.getElementById('resetPasswordModal').style.display = 'none';
    });
</script>

</body>
</html>
