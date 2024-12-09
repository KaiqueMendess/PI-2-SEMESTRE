<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Sistema de Irrigação</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="shortcut icon" type="image/png" href="assets/img/logo.jpg">
  <style>
    .chart {
  width: 600px !important; /* Faz o gráfico ocupar toda a largura disponível */
  height: 200px !important; /* Ajusta a altura conforme necessário */
}

  </style>
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
        <li><a href="http://10.0.0.139">Sensor ativo</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="vinculo.php">Sensores</a></li>
        <li><a href="ajuda.php">Ajuda</a></li>
        <li><a href="index.php" id="logout">Sair</a></li>
      </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
      <header class="header">
        <h1>Dashboard - Sistema de Irrigação de Alface</h1>
        <section class="sensor-map-container" id="sensor-map-container">
          <h2>Mapeamento dos Sensores</h2>
          <div class="sensor-map" id="sensor-map"></div>
        </section>          
      </header>

      <!-- Visão geral -->
      <section class="overview" id="overview">
        <h2>Visão Geral do Sistema</h2>
        <div class="status">
          <div class="status-item">
            <p>Status dos Sensores:</p>
            <span id="sensor-status" class="status-on">Carregando...</span>
          </div>
          <div class="status-item">
            <p>Total de Sensores Conectados:</p>
            <span id="total-sensors">Carregando...</span>
          </div>
          <div class="status-item">
            <p>Sensores Offline:</p>
            <span id="offline-sensors">Carregando...</span>
          </div>
        </div>
      </section>

      <!-- Gráficos e Estatísticas -->
      <section class="charts" id="charts">
        <h2>Gráficos e Estatísticas</h2>
        <div class="chart-container">
          <div class="chart-item">
            <h3>Histórico de Umidade</h3>
            <canvas id="humidity-chart" class="chart" width="800" height="400"></canvas>
          </div>
        </div>
      </section>
    </div>

    <!-- Rodapé -->
    <footer class="footer">
      <p>&copy; 2024 Sistema de Irrigação - Todos os direitos reservados.</p>
    </footer>
  </div>

  <!-- Importando módulos do Firebase -->
  <script type="module">
    // Importação dos módulos do Firebase
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js";
    import { getDatabase, ref, onValue, get, child } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-database.js";

    // Configuração do Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyBKxeTlLyWREGLcENJD4iMiM8_feKKiQO0",
        authDomain: "pi3semestre-23089.firebaseapp.com",
        databaseURL: "https://pi3semestre-23089-default-rtdb.firebaseio.com",
        projectId: "pi3semestre-23089",
        storageBucket: "pi3semestre-23089.appspot.com",
        messagingSenderId: "115992833105",
        appId: "1:115992833105:web:6743b38c81aa66dd5edc70"
    };

    // Inicializando o Firebase
    const app = initializeApp(firebaseConfig);
    const database = getDatabase(app);

    // Função para buscar dados dos sensores
    function buscarDadosSensores() {
        const sensoresRef = ref(database, 'sensores');
        onValue(sensoresRef, (snapshot) => {
            const sensores = snapshot.val();
            const totalSensores = Object.keys(sensores || {}).length;
            const offlineSensores = Object.values(sensores || {}).filter(sensor => !sensor.ativo).length;

            document.getElementById('sensor-status').textContent = totalSensores > 0 ? 'Ativos' : 'Inativos';
            document.getElementById('total-sensors').textContent = totalSensores;
            document.getElementById('offline-sensors').textContent = offlineSensores;
        });
    }

    // Carregar sensores no mapa
    function carregarMapaSensores() {
        const mapContainer = document.getElementById('sensor-map');
        mapContainer.innerHTML = '';

        const sensoresRef = ref(database, 'sensores');
        get(sensoresRef).then((snapshot) => {
            const sensores = snapshot.val();
            Object.keys(sensores || {}).forEach((sensorId) => {
                const sensorDiv = document.createElement('div');
                sensorDiv.classList.add('sensor');
                sensorDiv.textContent = `Sensor ${sensorId}`;
                mapContainer.appendChild(sensorDiv);
            });
        });
    }

    // Gráfico de umidade
    const ctx = document.getElementById('humidity-chart').getContext('2d');
    const humidityChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [], // Labels para o eixo X (tempo)
        datasets: [{
          label: 'Umidade',
          data: [], // Dados de umidade
          borderColor: 'rgb(75, 192, 192)',
          fill: false
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: { type: 'linear', position: 'bottom' },
          y: { min: 0, max: 100 }
        }
      }
    });

    // Atualiza o gráfico com os dados de umidade em tempo real
    function atualizarGraficoDeUmidade() {
        const sensoresRef = ref(database, 'sensores');
        onValue(sensoresRef, (snapshot) => {
            const sensores = snapshot.val();
            const timestamps = [];
            const umidades = [];

            Object.values(sensores || {}).forEach((sensor) => {
                if (sensor.umidade != null) {
                    timestamps.push(Date.now()); // Timestamp de cada leitura
                    umidades.push(sensor.umidade); // Valor de umidade
                }
            });

            // Atualiza os dados do gráfico
            humidityChart.data.labels = timestamps;
            humidityChart.data.datasets[0].data = umidades;
            humidityChart.update();
        });
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', () => {
        buscarDadosSensores();
        carregarMapaSensores();
        atualizarGraficoDeUmidade(); // Inicia a atualização do gráfico em tempo real
    });
  </script>
</body>
</html>
