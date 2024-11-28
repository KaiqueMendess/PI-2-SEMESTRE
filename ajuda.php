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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ajuda - Sistema de Gestão</title>
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
                </ul>
            </nav>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="header">
                <h1>Ajuda</h1>
                <p>Saiba como usar o sistema de forma eficiente.</p>
            </div>
            <div class="help-content">
                <h2>Como usar o sistema?</h2>
                <ul>
                    <li><strong>Perfil:</strong> Edite suas informações pessoais, como nome e email.</li>
                    <li><strong>Justificativas:</strong> Cadastre justificativas para suas faltas e acompanhe o status (aprovado, reprovado ou em análise).</li>
                    <li><strong>Reposições:</strong> Registre reposições relacionadas às justificativas aprovadas e veja o status delas.</li>
                    <li><strong>Agenda:</strong> Visualize as reposições aprovadas agendadas para os próximos dias.</li>
                    <li><strong>Documentos:</strong> Faça upload de comprovantes relacionados às justificativas.</li>
                    <li><strong>Notificações:</strong> Receba alertas sobre reposições próximas diretamente no painel.</li>
                    <li><strong>Estatísticas:</strong> Veja o resumo das suas atividades, como justificativas e reposições enviadas e aprovadas.</li>
                </ul>
                <p>Para mais informações, entre em contato com o suporte técnico.</p>
            </div>
        </div>
    </div>
</body>
</html>
