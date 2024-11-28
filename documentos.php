<?php
session_start();

// Verificar se o usuário está logado e se o perfil é 'professor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'professor') {
    header("Location: login.php");
    exit;
}

require 'db.php'; // Conexão com o banco de dados

// Obter informações do professor
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

// Consultar documentos enviados (Justificativas)
try {
    // Justificativas
    $stmt_justificativas = $pdo->prepare("
        SELECT data_falta, tipo_justificativa, comprovante AS documento_nome 
        FROM justificativas WHERE usuario_id = :usuario_id
    ");
    $stmt_justificativas->bindParam(':usuario_id', $usuario_id);
    $stmt_justificativas->execute();
    $justificativas = $stmt_justificativas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao buscar informações: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Documentos - Professor</title>
    <link rel="stylesheet" href="dashboard_professor.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <style>/* Ajustes para centralizar e aumentar o tamanho do card */

.dashboard-cards {
    display: flex;
    justify-content: center; /* Centraliza os cards */
    flex-wrap: wrap; /* Permite que os cards se ajustem em várias linhas, se necessário */
}

.card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%; /* Card ocupa toda a largura disponível */
    max-width: 900px; /* Limita a largura do card */
    padding: 20px;
    margin: 20px;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.card h3 {
    font-size: 1.5em;
    margin-bottom: 10px;
}

.card ul {
    list-style-type: none;
    padding-left: 0;
}

.card ul li {
    margin-bottom: 10px;
}
</style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.jpg" id="logo" alt="Logo">
                <h2>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>
                <p>Email: <?php echo htmlspecialchars($usuario_email); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                <li><a href="professor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Início</a></li>
                    <li><a href="perfil.php"><i class="fas fa-user"></i> Perfil</a></li>
                    <li><a href="justificativa.php"><i class="fas fa-clipboard-check"></i> Justificativas</a></li>
                    <li><a href="reposicao.php"><i class="fas fa-sync-alt"></i> Reposições</a></li>
                    <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                    <li><a href="documentos.php"><i class="fas fa-file-alt"></i> Documentos</a></li>
                    <li class="sobre"><a href="sobre.php"><i class="fas fa-info-circle"></i> Sobre</a></li>
                    <li class="ajuda"><a href="ajuda.php"><i class="fas fa-question-circle"></i> Ajuda</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </nav>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="header">
                <h1>Documentos Enviados</h1>
                <p>Visualize todos os documentos que você enviou.</p>
            </div>
            <div class="dashboard-cards">
                <!-- Card: Documentos Enviados -->
                <div class="card">
                    <h3><i class="fas fa-folder"></i> Documentos Enviados</h3>
                    <ul>
                        <?php if (isset($justificativas) && !empty($justificativas)): ?>
                            <?php foreach ($justificativas as $justificativa): ?>
                                <?php if (!empty($justificativa['documento_nome'])): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($justificativa['documento_nome']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($justificativa['documento_nome']); ?> - Enviado em: <?php echo htmlspecialchars($justificativa['data_falta']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Nenhum documento enviado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
