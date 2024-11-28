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

// Consultar dados relacionados
try {
    // Justificativas
    $stmt_justificativas = $pdo->prepare("
        SELECT data_falta, tipo_justificativa, comprovante AS documento_nome, 
               CASE 
                   WHEN status = 'aprovado' THEN 'aprovado' 
                   WHEN status = 'reprovado' THEN 'reprovado'
                   ELSE 'Em análise' 
               END AS status 
        FROM justificativas WHERE usuario_id = :usuario_id
    ");
    $stmt_justificativas->bindParam(':usuario_id', $usuario_id);
    $stmt_justificativas->execute();
    $justificativas = $stmt_justificativas->fetchAll(PDO::FETCH_ASSOC);

// Reposições (não aprovadas: Reprovada ou Em análise)
$stmt_reposicoes = $pdo->prepare("
    SELECT data_reposicao, descricao, horario_inicio, 
           CASE 
               WHEN status = 'Aprovada' THEN 'Aprovada' 
               WHEN status = 'Reprovada' THEN 'Reprovada'
               ELSE 'Em análise' 
           END AS status 
    FROM reposicoes 
    WHERE usuario_id = :usuario_id 
    AND (status = 'Reprovada' OR status = 'Em análise' OR status = 'Aprovada')
");
$stmt_reposicoes->bindParam(':usuario_id', $usuario_id);
$stmt_reposicoes->execute();
$reposicoes = $stmt_reposicoes->fetchAll(PDO::FETCH_ASSOC);


    // Reposições de aulas aprovadas (Agenda)
    $stmt_eventos = $pdo->prepare("
        SELECT data_reposicao, descricao, horario_inicio, disciplina 
        FROM reposicoes 
        WHERE usuario_id = :usuario_id 
        AND status = 'Aprovada' 
        AND data_reposicao >= CURDATE() 
        ORDER BY data_reposicao ASC
    ");
    $stmt_eventos->bindParam(':usuario_id', $usuario_id);
    $stmt_eventos->execute();
    $eventos = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);

    // Notificações para reposições próximas
    $stmt_notificacoes = $pdo->prepare("
        SELECT data_reposicao, descricao 
        FROM reposicoes 
        WHERE usuario_id = :usuario_id AND status = 'Aprovada' AND data_reposicao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt_notificacoes->bindParam(':usuario_id', $usuario_id);
    $stmt_notificacoes->execute();
    $notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Dashboard - Professor</title>
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
                <h1>Dashboard do Professor</h1>
                <p>Gerencie faltas, reposições, tarefas e mais.</p>
            </div>
            <div class="dashboard-cards">
    <!-- Card: Justificativas -->
    <div class="card">
        <h3><i class="fas fa-file-alt"></i> Justificativas Enviadas</h3>
        <ul>
            <?php if ($justificativas): ?>
                <?php foreach ($justificativas as $justificativa): ?>
                    <li>Falta: <?php echo htmlspecialchars($justificativa['data_falta']); ?> - <?php echo htmlspecialchars($justificativa['tipo_justificativa']); ?> - <?php echo htmlspecialchars($justificativa['status']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Nenhuma justificativa enviada.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Card: Reposições -->
    <div class="card">
        <h3><i class="fas fa-clipboard-list"></i> Reposições de Aulas</h3>
        <ul>
            <?php if ($reposicoes): ?>
                <?php foreach ($reposicoes as $reposicao): ?>
                    <li><?php echo htmlspecialchars($reposicao['data_reposicao']); ?> - <?php echo htmlspecialchars($reposicao['status']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Nenhuma reposição registrada.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Card: Estatísticas -->
    <div class="card">
        <h3><i class="fas fa-chart-pie"></i> Estatísticas</h3>
        <ul>
            <li><strong>Justificativas Enviadas:</strong> <?php echo count($justificativas); ?></li>
            <li><strong>Justificativas Aprovadas:</strong> <?php echo count(array_filter($justificativas, fn($j) => $j['status'] === 'aprovado')); ?></li>
            <li><strong>Reposições Registradas:</strong> <?php echo count($reposicoes); ?></li>
            <li><strong>Reposições Aprovadas:</strong> <?php echo count(array_filter($reposicoes, fn($r) => $r['status'] === 'Aprovada')); ?></li>
        </ul>
    </div>

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


            <!-- Agenda de Aulas -->
            <div class="dashboard-section">
                <h3><i class="fas fa-calendar-alt"></i> Agenda de Aulas</h3>
                <ul>
                    <?php if ($eventos): ?>
                        <?php foreach ($eventos as $evento): ?>
                            <li><?php echo htmlspecialchars($evento['data_reposicao']); ?> - <?php echo htmlspecialchars($evento['descricao']); ?> - <?php echo htmlspecialchars($evento['horario_inicio']); ?> - <?php echo htmlspecialchars($evento['disciplina']); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Sem reposições aprovadas na agenda.</li>
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
</body>
</html>
