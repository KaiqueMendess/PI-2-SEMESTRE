<?php
include 'db.php';
include 'middleware.php';

// Verificando se o usuário é professor
verificarAcesso('professor');

// Verificar se o parâmetro usuario_id foi passado
if (isset($_GET['usuario_id'])) {
    $usuario_id = $_GET['usuario_id'];

    // Preparar a consulta para buscar os dados da justificativa e plano de reposição
    $stmt = $pdo->prepare("
        SELECT j.id AS justificativa_id, j.data_falta, j.motivo, j.status AS justificativa_status, 
               p.id AS plano_id, p.data_reposicao, p.horario_inicio, p.horario_termino, p.disciplina, 
               p.status AS reposicao_status, p.descricao AS plano_descricao
        FROM justificativas j
        LEFT JOIN reposicoes p ON j.usuario_id = p.usuario_id
        WHERE j.usuario_id = :usuario_id
    ");
    
    // Associando o parâmetro usuario_id
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    
    // Executando a consulta
    $stmt->execute();
    $justificativa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se não encontrar dados, redireciona para a página de solicitações
    if (!$justificativa) {
        header("Location: detalhes_solicitacoes.php");
        exit;
    }
} else {
    header("Location: detalhes_solicitacoes.php");
    exit;
}

// Verificar se o formulário foi enviado para editar os dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Atualizando a justificativa
    $data_falta = $_POST['data_falta'];
    $motivo = $_POST['motivo'];
    $status_justificativa = $_POST['status_justificativa'];
    
    // Atualizando o plano de reposição, se necessário
    $data_reposicao = $_POST['data_reposicao'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_termino = $_POST['horario_termino'];
    $disciplina = $_POST['disciplina'];
    $status_reposicao = $_POST['status_reposicao'];
    $descricao = $_POST['descricao'];

    // Iniciando a transação para garantir que todas as mudanças sejam feitas de forma atômica
    $pdo->beginTransaction();

    try {
        // Atualizando a justificativa
        $stmt = $pdo->prepare("UPDATE justificativas SET data_falta = :data_falta, motivo = :motivo, status = :status WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':data_falta', $data_falta);
        $stmt->bindParam(':motivo', $motivo);
        $stmt->bindParam(':status', $status_justificativa);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();

        // Atualizando o plano de reposição, se houver
        if ($justificativa['plano_id']) {
            $stmt = $pdo->prepare("UPDATE reposicoes SET data_reposicao = :data_reposicao, horario_inicio = :horario_inicio, horario_termino = :horario_termino, disciplina = :disciplina, status = :status_reposicao, descricao = :descricao WHERE usuario_id = :usuario_id");
            $stmt->bindParam(':data_reposicao', $data_reposicao);
            $stmt->bindParam(':horario_inicio', $horario_inicio);
            $stmt->bindParam(':horario_termino', $horario_termino);
            $stmt->bindParam(':disciplina', $disciplina);
            $stmt->bindParam(':status_reposicao', $status_reposicao);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
        }

        // Comitar as alterações
        $pdo->commit();

        // Redirecionar após a edição
        header("Location: detalhes_solicitacoes.php");
        exit;
    } catch (Exception $e) {
        // Se algo der errado, desfaz a transação
        $pdo->rollBack();
        echo "Erro ao atualizar dados: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Solicitação</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Solicitação</h2>
        <a href="detalhes_solicitacoes.php" class="btn btn-danger mb-3">Voltar</a>
        
        <form action="editar_solicitacao.php?usuario_id=<?= $justificativa['usuario_id'] ?>" method="post">
            <div class="form-group">
                <label for="data_falta">Data da Falta</label>
                <input type="date" name="data_falta" id="data_falta" class="form-control" value="<?= htmlspecialchars($justificativa['data_falta']) ?>" required>
            </div>

            <div class="form-group">
                <label for="motivo">Motivo</label>
                <textarea name="motivo" id="motivo" class="form-control" required><?= htmlspecialchars($justificativa['motivo']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="status_justificativa">Status da Justificativa</label>
                <select name="status_justificativa" id="status_justificativa" class="form-control" required>
                    <option value="Aprovado" <?= ($justificativa['justificativa_status'] == 'Aprovado') ? 'selected' : '' ?>>Aprovado</option>
                    <option value="Reprovado" <?= ($justificativa['justificativa_status'] == 'Reprovado') ? 'selected' : '' ?>>Reprovado</option>
                    <option value="Pendente" <?= ($justificativa['justificativa_status'] == 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>

            <h4>Plano de Reposição</h4>
            <div class="form-group">
                <label for="data_reposicao">Data da Reposição</label>
                <input type="date" name="data_reposicao" id="data_reposicao" class="form-control" value="<?= htmlspecialchars($justificativa['data_reposicao']) ?>" required>
            </div>

            <div class="form-group">
                <label for="horario_inicio">Horário de Início</label>
                <input type="time" name="horario_inicio" id="horario_inicio" class="form-control" value="<?= htmlspecialchars($justificativa['horario_inicio']) ?>" required>
            </div>

            <div class="form-group">
                <label for="horario_termino">Horário de Término</label>
                <input type="time" name="horario_termino" id="horario_termino" class="form-control" value="<?= htmlspecialchars($justificativa['horario_termino']) ?>" required>
            </div>

            <div class="form-group">
                <label for="disciplina">Disciplina</label>
                <input type="text" name="disciplina" id="disciplina" class="form-control" value="<?= htmlspecialchars($justificativa['disciplina']) ?>" required>
            </div>

            <div class="form-group">
                <label for="status_reposicao">Status do Plano de Reposição</label>
                <select name="status_reposicao" id="status_reposicao" class="form-control" required>
                    <option value="Aprovado" <?= ($justificativa['reposicao_status'] == 'Aprovado') ? 'selected' : '' ?>>Aprovado</option>
                    <option value="Reprovado" <?= ($justificativa['reposicao_status'] == 'Reprovado') ? 'selected' : '' ?>>Reprovado</option>
                    <option value="Pendente" <?= ($justificativa['reposicao_status'] == 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" class="form-control"><?= htmlspecialchars($justificativa['plano_descricao']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>
