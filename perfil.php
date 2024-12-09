<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login se não houver sessão ativa
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil - Sistema de Irrigação</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Scripts Firebase com Módulo -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js";
    import { getAuth, updateEmail, updatePassword, updateProfile } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-auth.js";
    import { getDatabase } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-database.js";

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

    // Inicializa o Firebase
    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const database = getDatabase(app);

    // Verifica se o usuário está autenticado
    auth.onAuthStateChanged(user => {
      if (user) {
        const userEmail = user.email || "Email não disponível";
        const userName = userEmail.slice(0, 5);  // Pega os primeiros 5 caracteres do email
        const lastLogin = user.metadata.lastSignInTime
          ? new Date(user.metadata.lastSignInTime).toLocaleString()
          : "Último acesso desconhecido";

        document.getElementById("user-name").textContent = userName;
        document.getElementById("user-email").textContent = userEmail;
        document.getElementById("last-login").textContent = lastLogin;
      } else {
        window.location.replace("login.html");
      }
    });

    // Função para atualizar email, nome ou senha
    window.updateUserInfo = function() {
      const user = auth.currentUser;

      const newEmail = document.getElementById("new-email").value;
      const newPassword = document.getElementById("new-password").value;
      const newName = document.getElementById("new-name").value;

      // Validação do nome (máximo 6 caracteres)
      if (newName.length > 6) {
        alert("O nome deve ter no máximo 6 caracteres.");
        return;
      }

      // Atualiza o nome
      if (newName) {
        updateProfile(user, {
          displayName: newName
        }).then(() => {
          alert("Nome atualizado com sucesso!");
        }).catch((error) => {
          console.error("Erro ao atualizar o nome:", error);
        });
      }

      // Atualiza o email
      if (newEmail) {
        updateEmail(user, newEmail).then(() => {
          alert("Email atualizado com sucesso!");
        }).catch((error) => {
          console.error("Erro ao atualizar o email:", error);
        });
      }

      // Atualiza a senha
      if (newPassword) {
        updatePassword(user, newPassword).then(() => {
          alert("Senha atualizada com sucesso!");
        }).catch((error) => {
          console.error("Erro ao atualizar a senha:", error);
        });
      }
    }
  </script>

  <link rel="shortcut icon" type="image/png" href="assets/img/logo.jpg">
</head>
<body>
  <div class="container">
    <!-- Barra Lateral -->
    <nav class="sidebar">
      <div class="sidebar-header">
        <img src="assets/img/logo.jpg">
      </div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.php">Visão Geral</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="sensores.php">Sensores</a></li>
        <li><a href="ajuda.php">Ajuda</a></li>
        <li><a href="index.php" id="logout">Sair</a></li>
      </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
      <header class="header">
        <h1>Perfil do Usuário</h1>
      </header>

      <!-- Informações do Perfil -->
      <section class="user-profile" id="user-profile">
        <h2>Detalhes do Perfil</h2>
        <div class="profile-info">
          <div class="profile-item">
            <p><strong>Nome:</strong> <span id="user-name">Carregando...</span></p>
          </div>
          <div class="profile-item">
            <p><strong>Email:</strong> <span id="user-email">Carregando...</span></p>
          </div>
          <div class="profile-item">
            <p><strong>Último Acesso:</strong> <span id="last-login">Carregando...</span></p>
          </div>
        </div>
      </section>

      <!-- Formulário para Atualização -->
      <section class="update-info">
        <h2>Atualizar Informações</h2>
        <form id="update-form" onsubmit="event.preventDefault(); updateUserInfo();">
          <div class="form-group">
            <label for="new-name">Novo Nome (máximo 6 caracteres):</label>
            <input type="text" id="new-name" maxlength="6" placeholder="Nome" />
          </div>
          <div class="form-group">
            <label for="new-email">Novo Email:</label>
            <input type="email" id="new-email" placeholder="Novo email" />
          </div>
          <div class="form-group">
            <label for="new-password">Nova Senha:</label>
            <input type="password" id="new-password" placeholder="Nova senha" />
          </div>
          <button type="submit" class="btn">Atualizar</button>
        </form>
      </section>
    </div>

    <!-- Rodapé -->
    <footer class="footer">
      <p>&copy; 2024 Sistema de Irrigação - Todos os direitos reservados.</p>
    </footer>
  </div>
</body>
</html>
