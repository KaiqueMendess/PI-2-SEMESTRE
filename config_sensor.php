<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Se não estiver logado, redireciona para o login
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sensor_id = uniqid(); // Gerando um ID único para o sensor
    $ssid = $_POST['ssid'];
    $password = $_POST['password'];
    $interval = $_POST['interval'];

    // Vinculando o sensor ao usuário no Firebase Database
    require_once 'firebase-config.php';

    $database = $firebase->createDatabase();
    $sensor_data = [
        'user_id' => $_SESSION['user_id'],
        'name' => 'Sensor Horta Alface',
        'wifi_config' => [
            'ssid' => $ssid,
            'password' => $password
        ],
        'interval' => $interval,
        'status' => 'offline',
        'last_reading' => 0,
        'last_updated' => time()
    ];

    $database->getReference('sensors/' . $sensor_id)->set($sensor_data);

    $success_message = "Sensor configurado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração do Sensor</title>
</head>
<body>
    <h2>Configuração do Sensor</h2>
    <form method="POST" action="config_sensor.php">
        <label for="ssid">Nome da Rede Wi-Fi</label>
        <input type="text" name="ssid" required>

        <label for="password">Senha da Rede Wi-Fi</label>
        <input type="password" name="password" required>

        <label for="interval">Intervalo de Medição (minutos)</label>
        <input type="number" name="interval" required>

        <button type="submit">Salvar Configurações</button>
    </form>

    <?php if (isset($success_message)) { echo "<p>$success_message</p>"; } ?>
    <p><a href="dashboard.php">Voltar ao Painel</a></p>
</body>
</html>
