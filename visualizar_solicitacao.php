<?php
include 'db.php';
include 'middleware.php';
verificarAcesso('professor');

$justificativa_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Busque os detalhes da justificativa e do plano de reposição
$stmt = $pdo->prepare("
    SELECT j.data_falta, j.motivo, j.status, p.data_reposicao, p.descricao
    FROM justificativas j
    LEFT JOIN planos_reposicao p ON j.id = p.justificativa_id
    WHERE j.id = :justificativa_id AND j.usuario_id = :usuario_id
");
$stmt->bindParam(':justificativa_id', $justificativa_id, PDO::PARAM_INT);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->execute();
$solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitacao) {
    echo "Solicitação não encontrada ou acesso negado.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detalhes da Solicitação</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Detalhes da Solicitação</h2>
        <a href="detalhes_solicitacoes.php" class="btn btn-danger">Voltar</a>
        <div class="mt-4">
            <h4>Justificativa</h4>
            <p><strong>Data da Falta:</strong> <?= htmlspecialchars($solicitacao['data_falta']) ?></p>
            <p><strong>Motivo:</strong> <?= htmlspecialchars($solicitacao['motivo']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($solicitacao['status']) ?></p>
            
            <?php if ($solicitacao['data_reposicao']): ?>
                <h4>Plano de Reposição</h4>
                <p><strong>Data de Reposição:</strong> <?= htmlspecialchars($solicitacao['data_reposicao']) ?></p>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($solicitacao['descricao']) ?></p>
            <?php else: ?>
                <p>Não há plano de reposição associado a esta justificativa.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
