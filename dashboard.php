<?php
session_start();

// Verificar se o usuário está logado e se o perfil é 'coordenador'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'coordenador') {
    header("Location: login.php"); // Redireciona para o login se não for coordenador
    exit;
}

require 'db.php'; // Conexão com o banco de dados

// Obter informações do coordenador
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

// Obter dados para os gráficos e indicadores
$totalJustificativas = $pdo->query("SELECT COUNT(*) FROM Justificativas")->fetchColumn();
$totalAprovadas = $pdo->query("SELECT COUNT(*) FROM Justificativas WHERE status = 'aprovado'")->fetchColumn();
$totalRejeitadas = $pdo->query("SELECT COUNT(*) FROM Justificativas WHERE status = 'rejeitado'")->fetchColumn();
$totalPendentes = $totalJustificativas - $totalAprovadas - $totalRejeitadas;
$totalReposicoesPendentes = $pdo->query("SELECT COUNT(*) FROM reposicoes WHERE status = 'pendente'")->fetchColumn();
$totalReposicoesRealizadas = $pdo->query("SELECT COUNT(*) FROM reposicoes WHERE status = 'realizado'")->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard do Coordenador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="dashboard_professor.css">
</head>
<body>
<div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.jpg" id="logo" alt="Logo">
                <h2>Bem-vindo, <?php echo $usuario_nome; ?>!</h2>
                <p>Email: <?php echo $usuario_email; ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                <li class="nav-item"><a href="coordenador_dashboard.php" class="nav-link text-light"><i class="fas fa-home"></i> Início</a></li>
                <li class="nav-item"><a href="justificativa.php" class="nav-link text-light"><i class="fas fa-clipboard-check"></i> Justificativas</a></li>
                <li class="nav-item"><a href="reposicao.php" class="nav-link text-light"><i class="fas fa-sync-alt"></i> Reposições</a></li>
                <li class="nav-item"><a href="usuarios.php" class="nav-link text-light"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>
                <li class="nav-item"><a href="relatorios.php" class="nav-link text-light"><i class="fas fa-file-alt"></i> Relatórios</a></li>
                <li class="nav-item"><a href="notificacoes.php" class="nav-link text-light"><i class="fas fa-bell"></i> Notificações</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-light"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>

      
        <!-- Main Content -->
        <div class="main-content p-4 w-100">
            <div class="container">
                <h2 class="mb-4">Dashboard do Coordenador</h2>
                
                <!-- Indicadores -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total de Justificativas</h5>
                                <p class="card-text fs-2"><?php echo $totalJustificativas; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Aprovadas</h5>
                                <p class="card-text fs-2"><?php echo $totalAprovadas; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Rejeitadas</h5>
                                <p class="card-text fs-2"><?php echo $totalRejeitadas; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pendentes</h5>
                                <p class="card-text fs-2"><?php echo $totalPendentes; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Status das Justificativas</h5>
                        <canvas id="graficoJustificativas"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">Status das Reposições</h5>
                        <canvas id="graficoReposicoes"></canvas>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="mt-4">
                    <h5>Ações Rápidas</h5>
                    <a href="aprovar_justificativas.php" class="btn btn-success me-2">Aprovar Justificativas</a>
                    <a href="gerenciar_reposicoes.php" class="btn btn-info">Gerenciar Reposições</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de Justificativas
        var ctxJustificativas = document.getElementById('graficoJustificativas').getContext('2d');
        var graficoJustificativas = new Chart(ctxJustificativas, {
            type: 'pie',
            data: {
                labels: ['Aprovadas', 'Rejeitadas', 'Pendentes'],
                datasets: [{
                    data: [<?= $totalAprovadas ?>, <?= $totalRejeitadas ?>, <?= $totalPendentes ?>],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                }]
            }
        });

        // Gráfico de Reposições
        var ctxReposicoes = document.getElementById('graficoReposicoes').getContext('2d');
        var graficoReposicoes = new Chart(ctxReposicoes, {
            type: 'doughnut',
            data: {
                labels: ['Pendentes', 'Realizadas'],
                datasets: [{
                    data: [<?= $totalReposicoesPendentes ?>, <?= $totalReposicoesRealizadas ?>],
                    backgroundColor: ['#ffc107', '#007bff']
                }]
            }
        });
    </script>
</body>
</html>
