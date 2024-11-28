<?php
include 'db.php';
session_start();

// Verifica se o usuário está logado e se é um professor
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'professor') {
    header("Location: login.php");
    exit;
}

// Obtém o ID do usuário
$usuario_id = $_SESSION['usuario_id'];

// Obter dados para as estatísticas (justificativas e reposições)
$totalItemsStmt = $pdo->prepare("SELECT COUNT(*) FROM justificativas WHERE usuario_id = :usuario_id");
$totalItemsStmt->execute(['usuario_id' => $usuario_id]);
$totalItems = $totalItemsStmt->fetchColumn();

$totalAprovadasStmt = $pdo->prepare("SELECT COUNT(*) FROM justificativas WHERE status = 'aprovado' AND usuario_id = :usuario_id");
$totalAprovadasStmt->execute(['usuario_id' => $usuario_id]);
$totalAprovadas = $totalAprovadasStmt->fetchColumn();

$totalRejeitadasStmt = $pdo->prepare("SELECT COUNT(*) FROM justificativas WHERE status = 'rejeitado' AND usuario_id = :usuario_id");
$totalRejeitadasStmt->execute(['usuario_id' => $usuario_id]);
$totalRejeitadas = $totalRejeitadasStmt->fetchColumn();

// Calcular pendentes
$totalPendentes = $totalItems - $totalAprovadas - $totalRejeitadas;

// Obter dados de justificativas e verificar se há reposição associada
$query = "
    SELECT j.id AS justificativa_id, j.data_falta, j.motivo
    FROM justificativas j
    WHERE j.usuario_id = :usuario_id
    ORDER BY j.data_envio DESC
";
$itemsStmt = $pdo->prepare($query);
$itemsStmt->execute(['usuario_id' => $usuario_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Professor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard do Professor</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        
        <!-- Ações -->
        <div class="mt-4">
            <h3>Ações Rápidas</h3>
            <div class="btn-group">
                <a href="justificativa.php" class="btn btn-primary">Enviar Justificativa</a>
                <a href="reposicao.php" class="btn btn-secondary">Enviar Plano de Reposição</a>
                <a href="chat.php" class="btn btn-info">Acessar Chat</a>
                <a href="agenda.php" class="btn btn-warning">Ver Agenda</a>
            </div>
        </div>

        <!-- Estatísticas -->
        <h3 class="mt-5">Estatísticas de Justificativas e Reposições</h3>
        <canvas id="graficoItems"></canvas>

        <script>
            var ctx = document.getElementById('graficoItems').getContext('2d');
            var graficoItems = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Aprovadas', 'Rejeitadas', 'Pendentes'],
                    datasets: [{
                        label: 'Status das Justificativas',
                        data: [<?= $totalAprovadas ?>, <?= $totalRejeitadas ?>, <?= $totalPendentes ?>],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                    }]
                },
                options: {
                    responsive: true
                }
            });
        </script>

        <!-- Histórico de Justificativas -->
        <h3 class="mt-5">Minhas Justificativas</h3>
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Motivo</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['justificativa_id']) ?></td>
                        <td><?= htmlspecialchars($item['data_falta']) ?></td>
                        <td><?= htmlspecialchars($item['motivo']) ?></td>
                        <td>
                            <span class="badge <?= $item['status'] === 'aprovado' ? 'badge-success' : ($item['status'] === 'rejeitado' ? 'badge-danger' : 'badge-warning') ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="detalhes_solicitacoes.php?id=<?= $item['justificativa_id'] ?>" class="btn btn-info btn-sm">Ver Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
