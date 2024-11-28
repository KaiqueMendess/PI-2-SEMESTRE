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

// Consultar notificações de reposições próximas (data entre hoje e 7 dias)
try {
    $stmt_notificacoes = $pdo->prepare("
        SELECT data_reposicao, descricao 
        FROM reposicoes 
        WHERE usuario_id = :usuario_id 
        AND status = 'Aprovada' 
        AND data_reposicao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt_notificacoes->bindParam(':usuario_id', $usuario_id);
    $stmt_notificacoes->execute();
    $notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar notificações: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Agenda - Professor</title>
    <link rel="stylesheet" href="dashboard_professor.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
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
                <h1>Agenda de Reposição</h1>
                <p>Gerencie suas aulas de reposição e esteja atento aos prazos.</p>
            </div>

            <div class="dashboard-section">
                <h3><i class="fas fa-calendar-alt"></i> Reposições Aprovadas nos Próximos 7 Dias</h3>
                <ul>
                    <?php if ($notificacoes): ?>
                        <?php foreach ($notificacoes as $reposicao): ?>
                            <li>
                                <strong>Data:</strong> <?php echo htmlspecialchars($reposicao['data_reposicao']); ?> <br>
                                <strong>Descrição:</strong> <?php echo htmlspecialchars($reposicao['descricao']); ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Nenhuma reposição agendada nos próximos 7 dias.</li>
                    <?php endif; ?>
                </ul>
            </div>
                <!-- Notificações (Reposições próximas) -->
                <div class="notification">
                <h3><i class="fas fa-bell"></i> Notificações</h3>
                <ul>
                    <?php if ($notificacoes): ?>
                        <?php foreach ($notificacoes as $notificacao): ?>
                            <li>Reposição em breve: <?php echo htmlspecialchars($notificacao['descricao']); ?> - <?php echo htmlspecialchars($notificacao['data_reposicao']); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Não há reposições próximas.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .dashboard-section ul li {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
    </style>
</body>
</html>
