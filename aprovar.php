<?php
session_start();

// Verificar se o usuário está logado e tem o perfil de coordenador
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'coordenador') {
    header("Location: login.php");
    exit;
}

require 'db.php'; // Conexão com o banco de dados

// Obter informações do coordenador
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

// Aprovação ou rejeição de justificativas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $acao = $_POST['acao'];
    $comentario = htmlspecialchars($_POST['comentario'], ENT_QUOTES, 'UTF-8');

    if ($acao === 'aprovar') {
        $stmt = $pdo->prepare("UPDATE Justificativas SET status = 'aprovado', motivo_rejeicao = NULL WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } elseif ($acao === 'rejeitar') {
        $motivo_rejeicao = htmlspecialchars($_POST['motivo_rejeicao'], ENT_QUOTES, 'UTF-8');
        $stmt = $pdo->prepare("UPDATE Justificativas SET status = 'rejeitado', motivo_rejeicao = :motivo WHERE id = :id");
        $stmt->bindParam(':motivo', $motivo_rejeicao);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Registrar comentário
    if (!empty($comentario)) {
        $stmtComentario = $pdo->prepare("INSERT INTO comentarios (justificativa_id, usuario_id, comentario) VALUES (:justificativa_id, :usuario_id, :comentario)");
        $stmtComentario->bindParam(':justificativa_id', $id);
        $stmtComentario->bindParam(':usuario_id', $usuario_id);
        $stmtComentario->bindParam(':comentario', $comentario);
        $stmtComentario->execute();
    }

    header("Location: aprovar_justificativas.php");
    exit;
}

// Recuperar justificativas pendentes
$stmt = $pdo->query("SELECT * FROM Justificativas WHERE status = 'em análise'");
$justificativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovar Justificativas</title>
    <link rel="stylesheet" href="dashboard_professor.css">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Bem-vindo, <?php echo $usuario_nome; ?>!</h2>
            <p>Email: <?php echo $usuario_email; ?></p>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="coordenador_dashboard.php"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="justificativa.php"><i class="fas fa-clipboard-check"></i>Aprovar Justificativas</a></li>
                <li><a href="aprovar.php"><i class="fas fa-sync-alt"></i>Aprovar Reposições</a></li>
                <li><a href="usuarios.php"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>
                <li><a href="relatorios.php"><i class="fas fa-file-alt"></i> Relatórios</a></li>
                <li><a href="notificacoes.php"><i class="fas fa-bell"></i> Notificações</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <h2>Justificativas Pendentes</h2>
        <?php if (empty($justificativas)): ?>
            <p>Nenhuma justificativa pendente.</p>
        <?php else: ?>
            <?php foreach ($justificativas as $j): ?>
                <div class="justificativa">
                    <p><strong>ID:</strong> <?= $j['id']; ?></p>
                    <p><strong>Data da Falta:</strong> <?= $j['data_falta']; ?></p>
                    <p><strong>Tipo:</strong> <?= $j['tipo_justificativa']; ?></p>
                    <p><strong>Motivo:</strong> <?= $j['motivo']; ?></p>
                    <?php if ($j['comprovante']): ?>
                        <p><a href="<?= $j['comprovante']; ?>" target="_blank">Ver Comprovante</a></p>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $j['id']; ?>">
                        <label for="comentario">Comentário (opcional):</label>
                        <textarea name="comentario" id="comentario"></textarea>
                        <label for="motivo_rejeicao">Motivo da Rejeição:</label>
                        <input type="text" name="motivo_rejeicao" placeholder="Obrigatório se rejeitado">
                        <button type="submit" name="acao" value="aprovar">Aprovar</button>
                        <button type="submit" name="acao" value="rejeitar">Rejeitar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
