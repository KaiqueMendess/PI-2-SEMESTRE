<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vínculo de Sensores - Sistema de Irrigação</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database.js"></script>
  <link rel="shortcut icon" type="imagex/png" href="assets/img/logo.jpg">
</head>
<body>
  <div class="container">
    <!-- Barra Lateral -->
    <nav class="sidebar">
      <div class="sidebar-header">
        <img src="assets/img/logo.jpg">
      </div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.html">Visão Geral</a></li>
        <li><a href="perfil.html">Perfil</a></li>
        <li><a href="estatisticas.html">Gráficos e Estatísticas</a></li>
        <li><a href="mapeamento.html">Mapeamento de Sensores</a></li>
        <li><a href="notificacoes.html">Notificações</a></li>
        <li><a href="historico.html">Histórico de Manutenção</a></li>
        <li><a href="ajuda.html">Ajuda</a></li>
        <li><a href="index.php" id="logout">Sair</a></li>
      </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
      <header class="header">
        <h1>Vínculo de Sensores</h1>
      </header>

      <!-- Página de Vínculo de Sensores -->
      <section class="sensor-linking">
        <h2>Selecione o Sensor para Vincular</h2>
        <p>Escolha um sensor da lista abaixo para vinculá-lo ao seu sistema de irrigação.</p>
        
        <!-- Lista de Sensores Não Vinculados -->
        <div id="sensors-list">
          <ul id="sensor-list">
            <!-- Sensores serão carregados aqui -->
            <li>Carregando sensores...</li>
          </ul>
        </div>

        <div id="sensor-details">
          <h3>Detalhes do Sensor</h3>
          <p><strong>Nome:</strong> <span id="sensor-name"></span></p>
          <p><strong>Local:</strong> <span id="sensor-location"></span></p>
          <p><strong>Status:</strong> <span id="sensor-status"></span></p>
          <button id="link-sensor" class="btn" disabled>Vincular Sensor</button>
        </div>
      </section>

      <!-- Rodapé -->
      <footer class="footer">
        <p>&copy; 2024 Sistema de Irrigação - Todos os direitos reservados.</p>
      </footer>
    </div>
  </div>

  <script>
  // Função para carregar os sensores do Firebase
  function carregarSensores() {
    const sensorListElement = document.getElementById('sensor-list');
    const sensoresRef = database.ref('sensores'); // Ajuste para o caminho correto dos sensores

    sensoresRef.once('value', snapshot => {
      const sensores = snapshot.val(); // Obtém os dados dos sensores

      console.log("Sensores retornados:", sensores); // Adicionando log para verificar os dados

      if (sensores) {
        sensorListElement.innerHTML = ''; // Limpa a lista
        Object.keys(sensores).forEach(sensorId => {
          const sensor = sensores[sensorId];
          const sensorItem = document.createElement('li');
          sensorItem.textContent = `Sensor ${sensorId} - Local: ${sensor.local || 'Desconhecido'}`;
          sensorItem.classList.add('sensor-item');
          sensorItem.dataset.sensorId = sensorId;

          // Adiciona o item à lista
          sensorListElement.appendChild(sensorItem);

          // Adiciona o evento de clique para carregar os detalhes do sensor
          sensorItem.addEventListener('click', () => {
            mostrarDetalhesSensor(sensorId);
          });
        });
      } else {
        sensorListElement.innerHTML = '<li>Nenhum sensor encontrado.</li>';
      }
    }).catch(error => {
      console.error("Erro ao carregar sensores:", error); // Log de erro
    });
  }

  // Função para exibir os detalhes do sensor selecionado
  function mostrarDetalhesSensor(sensorId) {
    const sensorRef = database.ref(`sensores/${sensorId}`);
    
    sensorRef.once('value', snapshot => {
      const sensor = snapshot.val();
      
      console.log("Detalhes do sensor:", sensor); // Log de detalhes do sensor

      if (sensor) {
        document.getElementById('sensor-name').textContent = `Sensor ${sensorId}`;
        document.getElementById('sensor-location').textContent = sensor.local || 'Desconhecido';
        document.getElementById('sensor-status').textContent = sensor.status || 'Desconhecido';

        // Exibe o botão para vincular
        document.getElementById('link-sensor').disabled = false;

        // Ao clicar no botão, vinculamos o sensor
        document.getElementById('link-sensor').onclick = () => {
          vincularSensor(sensorId);
        };
      }
    }).catch(error => {
      console.error("Erro ao carregar detalhes do sensor:", error); // Log de erro
    });
  }

  // Função para vincular o sensor ao sistema
  function vincularSensor(sensorId) {
    const sensorRef = database.ref(`sensores/${sensorId}`);
    
    // Atualizando o status do sensor para "Vinculado"
    sensorRef.update({
      status: 'Vinculado',
      sistema: 'Irrigação de Alface'
    }).then(() => {
      alert('Sensor vinculado com sucesso!');
      carregarSensores(); // Recarrega a lista de sensores
    }).catch(error => {
      console.error('Erro ao vincular sensor:', error);
      alert('Erro ao vincular sensor.');
    });
  }

  // Inicializa o Firebase e chama a função para carregar os sensores
  const firebaseConfig = {
    apiKey: "AIzaSyBKxeTlLyWREGLcENJD4iMiM8_feKKiQO0",
    authDomain: "pi3semestre-23089.firebaseapp.com",
    databaseURL: "https://pi3semestre-23089-default-rtdb.firebaseio.com",
    projectId: "pi3semestre-23089",
    storageBucket: "pi3semestre-23089.appspot.com",
    messagingSenderId: "115992833105",
    appId: "1:115992833105:web:6743b38c81aa66dd5edc70"
  };

  const app = firebase.initializeApp(firebaseConfig);
  const database = firebase.database();

  // Carrega os sensores ao carregar a página
  window.onload = () => {
    carregarSensores();
  };
</script>

</body>
</html>
