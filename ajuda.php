<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login se não houver sessão ativa
    header("Location: index.php");
    exit();
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajuda - Sistema de Irrigação</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database.js"></script>
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
        <li><a href="vinculo.php">Sensores</a></li>
        <li><a href="ajuda.php">Ajuda</a></li>
        <li><a href="index.php" id="logout">Sair</a></li>
      </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
      <header class="header">
        <h1>Ajuda - Sistema de Irrigação</h1>
      </header>

      <!-- Seção de Ajuda -->
      <section class="help-section" id="help-section">
        <h2>Como Usar o Sistema</h2>
        <div class="help-item">
          <h3>Visão Geral</h3>
          <p>Este sistema permite monitorar e controlar a irrigação de plantações, como alface, com base nos níveis de umidade do solo. Utilize o painel para visualizar a status de sensores e bombas, configurar limites de umidade e visualizar gráficos de dados.</p>
        </div>
        <div class="help-item">
          <h3>Controle Manual</h3>
          <p>No painel de controle manual, você pode ligar ou desligar a bomba de água manualmente e ajustar o limite de umidade para ativar o sistema de irrigação automaticamente.</p>
        </div>
        <div class="help-item">
          <h3>Mapeamento de Sensores</h3>
          <p>Visualize a localização dos sensores conectados ao sistema e saiba em tempo real o status de cada um deles. Os sensores de umidade são vitais para o funcionamento correto do sistema de irrigação.</p>
        </div>
        <div class="help-item">
          <h3>Gráficos e Estatísticas</h3>
          <p>Acompanhe o histórico de umidade do solo e o uso de água ao longo do tempo. Utilize os gráficos para analisar os dados e tomar decisões informadas sobre o sistema de irrigação.</p>
        </div>
      </section>
    </div>

    <!-- Rodapé -->
    <footer class="footer">
      <p>&copy; 2024 Sistema de Irrigação - Todos os direitos reservados.</p>
    </footer>
  </div>

  <script>
    // Firebase Configuration
    const firebaseConfig = {
      apiKey: "AIzaSyBKxeTlLyWREGLcENJD4iMiM8_feKKiQO0",
      authDomain: "pi3semestre-23089.firebaseapp.com",
      databaseURL: "https://pi3semestre-23089-default-rtdb.firebaseio.com",
      projectId: "pi3semestre-23089",
      storageBucket: "pi3semestre-23089.firebasestorage.app",
      messagingSenderId: "115992833105",
      appId: "1:115992833105:web:6743b38c81aa66dd5edc70"
    };

    // Inicializar Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();
    const database = firebase.database();

    // Verificar se o usuário está autenticado
    auth.onAuthStateChanged(user => {
      if (!user) {
        // Usuário não está autenticado, redirecionar para login
        window.location.replace("login.html");
      }
    });
  </script>
</body>
</html>
