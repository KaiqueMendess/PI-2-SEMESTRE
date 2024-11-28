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
    <title>Sobre - Sistema de Gestão</title>
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
                <h1>Sobre o Sistema</h1>
                <p>Conheça mais sobre o propósito e funcionalidades deste sistema.</p>
            </div>
            <div class="about-content">
    <h2>O que é este sistema?</h2>
    <p>
        Este sistema foi desenvolvido como um trabalho de Projeto Integrador (PI) 
        com o objetivo de otimizar o processo de organização e gestão de justificativas e reposições para professores. 
    </p>
    <h3>Funcionalidades Principais:</h3>
    <ul>
        <li>Cadastrar justificativas para faltas com comprovantes.</li>
        <li>Registrar reposições vinculadas às justificativas aprovadas.</li>
        <li>Acompanhar o status de justificativas e reposições.</li>
        <li>Visualizar notificações de reposições próximas.</li>
        <li>Gerenciar estatísticas de atividades realizadas.</li>
    </ul>
    <h3>Por que foi criado?</h3>
    <p>
        O sistema busca reduzir o tempo gasto pelos professores em processos administrativos, 
        promovendo maior eficiência e organização no ambiente educacional.
    </p>
    <h3>Desenvolvedores:</h3>
    <p>
        Este projeto foi desenvolvido por uma equipe dedicada de estudantes:  
    </p>
    <ul>
        <li>Kaique Da Silva Mendes</li>
        <li>Luan Rocha</li>
        <li>Adriano Ferreira Junior</li>
        <li>João Pedro M. Lopes</li>
        <li>Bruno Henrique Castro Cardoso</li>
        <li>Yuri Felipe Machado Pimentel</li>
    </ul>
    </div>

        </div>
    </div>
</body>
</html>
