// Função para criar sensores no mapa
function criarSensores() {
    const mapContainer = document.getElementById('sensor-map');

    // Exemplo de localização de sensores com nome e umidade
    const sensores = [
        { id: 1, nome: "Sensor A", umidade: "45%" },
        { id: 2, nome: "Sensor B", umidade: "60%" },
        { id: 3, nome: "Sensor C", umidade: "30%" },
        { id: 4, nome: "Sensor D", umidade: "50%" },
        { id: 5, nome: "Sensor E", umidade: "55%" },
        { id: 6, nome: "Sensor F", umidade: "48%" },
        { id: 7, nome: "Sensor G", umidade: "70%" },
        { id: 8, nome: "Sensor H", umidade: "65%" }
    ];

    // Criar os quadrados representando os sensores
    sensores.forEach(sensor => {
        const sensorDiv = document.createElement('div');
        sensorDiv.classList.add('sensor');

        // Adiciona as informações do sensor
        const sensorInfo = document.createElement('div');
        sensorInfo.classList.add('sensor-info');

        const sensorName = document.createElement('span');
        sensorName.textContent = `${sensor.nome.slice(0, 10)}...`; // Exibe apenas os primeiros 10 caracteres do nome
        sensorInfo.appendChild(sensorName);

        const sensorHumidity = document.createElement('div');
        sensorHumidity.classList.add('sensor-humidity');
        sensorHumidity.textContent = sensor.umidade;
        sensorInfo.appendChild(sensorHumidity);

        sensorDiv.appendChild(sensorInfo);

        // Adicionar evento de clique para alterar o estado (ativo/inativo)
        sensorDiv.addEventListener('click', () => {
            sensorDiv.classList.toggle('active');
            const umidade = sensorDiv.querySelector('.sensor-humidity');
            umidade.style.display = sensorDiv.classList.contains('active') ? 'block' : 'none';
        });

        mapContainer.appendChild(sensorDiv);
    });
}

// Inicia a criação dos sensores ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    criarSensores();
});

// Alterna entre o tema claro e escuro
const toggleButton = document.getElementById('toggle-theme');
if (toggleButton) {
    toggleButton.addEventListener('click', () => {
        document.body.classList.toggle('tema-escuro');
    });
}

// Alterna o menu lateral
const toggleMenuButton = document.getElementById('toggle-menu');
const sidebar = document.querySelector('.sidebar');
if (toggleMenuButton && sidebar) {
    toggleMenuButton.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}

// Função para mostrar alertas
function mostrarAlerta(tipo, mensagem) {
    const alerta = document.getElementById('alerta');
    if (alerta) {
        alerta.classList.remove('erro', 'sucesso');
        alerta.classList.add(tipo);
        alerta.innerText = mensagem;
        alerta.style.display = 'block';

        setTimeout(() => {
            alerta.style.display = 'none';
        }, 5000);
    }
}

// Exemplo de alerta
mostrarAlerta('erro', 'A umidade do solo está muito baixa!');

// Configuração de gráfico de umidade
const humidityChart = new Chart(document.getElementById('humidity-chart').getContext('2d'), {
    type: 'line',
    data: {
        labels: ['1h', '2h', '3h', '4h', '5h'],
        datasets: [{
            label: 'Umidade do Solo (%)',
            data: [60, 65, 70, 75, 80],
            borderColor: '#2ecc71',
            fill: false
        }]
    },
    options: {
        responsive: true
    }
});

// Configuração de gráfico de uso de água
const waterUsageChart = new Chart(document.getElementById('water-usage-chart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['1h', '2h', '3h', '4h', '5h'],
        datasets: [{
            label: 'Uso de Água (L)',
            data: [5, 6, 4, 7, 5],
            backgroundColor: '#3498db'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Atualiza os gráficos periodicamente
function atualizarGraficos() {
    const novosDadosUmidade = [65, 67, 72, 74, 80];
    const novosDadosAgua = [4, 5, 6, 7, 3];

    humidityChart.data.datasets[0].data = novosDadosUmidade;
    waterUsageChart.data.datasets[0].data = novosDadosAgua;

    humidityChart.update();
    waterUsageChart.update();
}

setInterval(atualizarGraficos, 5000);

// Função para controlar a bomba de água
const togglePumpButton = document.getElementById('toggle-pump');
const pumpStatus = document.getElementById('pump-status');
if (togglePumpButton && pumpStatus) {
    togglePumpButton.addEventListener('click', () => {
        if (pumpStatus.classList.contains('status-on')) {
            pumpStatus.classList.remove('status-on');
            pumpStatus.classList.add('status-off');
            pumpStatus.innerText = 'Desligada';
        } else {
            pumpStatus.classList.remove('status-off');
            pumpStatus.classList.add('status-on');
            pumpStatus.innerText = 'Ligada';
        }
    });
}

// Atualiza o status de umidade do solo
const humidityThresholdInput = document.getElementById('humidity-threshold');
if (humidityThresholdInput) {
    humidityThresholdInput.addEventListener('input', (e) => {
        const newThreshold = e.target.value;
        console.log('Novo limite de umidade:', newThreshold);
    });
}
