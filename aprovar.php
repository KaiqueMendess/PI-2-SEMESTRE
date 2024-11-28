<?php
session_start();

// Verificar se o usuário está logado e se o perfil é 'professor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'coordenador') {
    header("Location: login.php");  // Se não for professor, redireciona para o login
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

    // Registrar o comentário
    if (!empty($comentario)) {
        $stmtComentario = $pdo->prepare("INSERT INTO comentarios (justificativa_id, usuario_id, comentario) VALUES (:justificativa_id, :usuario_id, :comentario)");
        $stmtComentario->bindParam(':justificativa_id', $id);
        $stmtComentario->bindParam(':usuario_id', $_SESSION['usuario_id']);
        $stmtComentario->bindParam(':comentario', $comentario);
        $stmtComentario->execute();
    }

    header("Location: aprovar.php");
    exit;
}

// Recuperar justificativas pendentes
$stmt = $pdo->query("SELECT * FROM Justificativas WHERE status = 'em análise'");
$justificativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
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
                <li><a href="professor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Início</a></li>
                    <li><a href="justificativa.php"><i class="fas fa-clipboard-check"></i> Justificativas</a></li>
                    <li><a href="reposicao.php"><i class="fas fa-sync-alt"></i> Reposições</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>

    <h2>Justificativas Pendentes</h2>
    <?php foreach ($justificativas as $j): ?>
        <p>
            ID: <?= $j['id']; ?> | Data: <?= $j['data_falta']; ?> | Tipo: <?= $j['tipo_justificativa']; ?> <br>
            Motivo: <?= $j['motivo']; ?> <br>
            <?php if ($j['comprovante']): ?>
                <a href="<?= $j['comprovante']; ?>" target="_blank">Ver Comprovante</a>
            <?php endif; ?>
        </p>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $j['id']; ?>">
            <label>Comentário (opcional):</label>
            <textarea name="comentario"></textarea><br>
            <button type="submit" name="acao" value="aprovar">Aprovar</button>
            <button type="submit" name="acao" value="rejeitar">Rejeitar</button>
            <input type="text" name="motivo_rejeicao" placeholder="Motivo da rejeição (obrigatório se rejeitado)">
        </form>
    <?php endforeach; ?>
</body>
</html>
