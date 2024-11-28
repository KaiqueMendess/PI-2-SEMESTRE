<?php
include 'db.php';
include 'middleware.php';

// Verificação de acesso para garantir que o usuário seja professor
verificarAcesso('professor');

// Processando a edição da solicitação se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['justificativa_id'])) {
    $justificativa_id = $_POST['justificativa_id'];
    $motivo = $_POST['motivo'];
    $data_falta = $_POST['data_falta'];
    $data_reposicao = $_POST['data_reposicao'] ?? null;
    $horario_inicio = $_POST['horario_inicio'] ?? null;
    $horario_termino = $_POST['horario_termino'] ?? null;
    $disciplina = $_POST['disciplina'] ?? null;
    $descricao = $_POST['descricao'] ?? null;

    // Verificando se um novo arquivo foi enviado
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        // Processando o novo arquivo de comprovante
        $arquivoTmp = $_FILES['arquivo']['tmp_name'];
        $arquivoNome = $_FILES['arquivo']['name'];
        $diretorio = "uploads/"; // Diretório onde o arquivo será armazenado

        // Garantir um nome único para o arquivo
        $arquivoNomeUnico = uniqid() . "_" . basename($arquivoNome);

        // Movendo o arquivo para o diretório
        if (move_uploaded_file($arquivoTmp, $diretorio . $arquivoNomeUnico)) {
            $caminhoArquivo = $diretorio . $arquivoNomeUnico;
        } else {
            echo "Erro ao mover o arquivo.";
            exit();
        }
    } else {
        // Caso não tenha sido enviado um arquivo, manter o arquivo atual
        $caminhoArquivo = $_POST['comprovante_atual'] ?? null;
    }

    // Preparando a consulta para atualizar a justificativa
    $stmt = $pdo->prepare("
        UPDATE justificativas 
        SET motivo = :motivo, data_falta = :data_falta, comprovante = :comprovante
        WHERE id = :justificativa_id
    ");
    $stmt->bindParam(':motivo', $motivo);
    $stmt->bindParam(':data_falta', $data_falta);
    $stmt->bindParam(':comprovante', $caminhoArquivo); // Atualiza o arquivo de comprovante
    $stmt->bindParam(':justificativa_id', $justificativa_id, PDO::PARAM_INT);
    $stmt->execute();

    // Se houver plano de reposição, atualiza também
    if ($data_reposicao) {
        $stmt = $pdo->prepare("
            UPDATE reposicoes 
            SET data_reposicao = :data_reposicao, horario_inicio = :horario_inicio, 
                horario_termino = :horario_termino, disciplina = :disciplina, 
                descricao = :descricao 
            WHERE usuario_id = (SELECT usuario_id FROM justificativas WHERE id = :justificativa_id)
        ");
        $stmt->bindParam(':data_reposicao', $data_reposicao);
        $stmt->bindParam(':horario_inicio', $horario_inicio);
        $stmt->bindParam(':horario_termino', $horario_termino);
        $stmt->bindParam(':disciplina', $disciplina);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':justificativa_id', $justificativa_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Redireciona ou mostra uma mensagem de sucesso
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Preparando a consulta para obter as justificativas, planos de reposição e comentários do professor logado
$stmt = $pdo->prepare("
    SELECT j.id AS justificativa_id, j.data_falta, j.motivo, j.status AS justificativa_status, 
           p.id AS plano_id, p.data_reposicao, p.horario_inicio, p.horario_termino, p.disciplina, 
           p.status AS reposicao_status, p.descricao AS plano_descricao,
           c.comentario, c.data_hora AS comentario_data, j.comprovante
    FROM justificativas j
    LEFT JOIN reposicoes p ON j.usuario_id = p.usuario_id  
    LEFT JOIN comentarios c ON j.id = c.justificativa_id
    WHERE j.usuario_id = :usuario_id
    ORDER BY j.data_falta DESC
");

// Associando o valor do ID do usuário da sessão
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);

// Executando a consulta
$stmt->execute();

// Armazenando os resultados
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes das Solicitações</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Detalhes das Solicitações</h2>
        <a href="dashboard_professor.php" class="btn btn-danger mb-3">Voltar</a>

        <?php if (count($solicitacoes) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data da Falta</th>
                        <th>Motivo</th>
                        <th>Status da Justificativa</th>
                        <th>Plano de Reposição</th>
                        <th>Comentário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <tr>
                            <td><?= htmlspecialchars($solicitacao['data_falta']) ?></td>
                            <td><?= htmlspecialchars($solicitacao['motivo']) ?></td>
                            <td><?= htmlspecialchars($solicitacao['justificativa_status']) ?></td>
                            <td>
                                <?php if ($solicitacao['plano_id']): ?>
                                    <strong>Data:</strong> <?= htmlspecialchars($solicitacao['data_reposicao']) ?><br>
                                    <strong>Início:</strong> <?= htmlspecialchars($solicitacao['horario_inicio']) ?><br>
                                    <strong>Término:</strong> <?= htmlspecialchars($solicitacao['horario_termino']) ?><br>
                                    <strong>Disciplina:</strong> <?= htmlspecialchars($solicitacao['disciplina']) ?><br>
                                    <strong>Status:</strong> <?= htmlspecialchars($solicitacao['reposicao_status']) ?><br>
                                    <strong>Descrição:</strong> <?= htmlspecialchars($solicitacao['plano_descricao']) ?>
                                <?php else: ?>
                                    Não há plano de reposição
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($solicitacao['comentario']): ?>
                                    <strong>Comentário:</strong> <?= htmlspecialchars($solicitacao['comentario']) ?><br>
                                    <strong>Data:</strong> <?= htmlspecialchars($solicitacao['comentario_data']) ?>
                                <?php else: ?>
                                    Nenhum comentário registrado
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Botão para Ver Detalhes -->
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetalhes<?= $solicitacao['justificativa_id'] ?>">Ver Detalhes</button>

                                <!-- Modal de Detalhes -->
                                <div class="modal fade" id="modalDetalhes<?= $solicitacao['justificativa_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalDetalhesLabel">Detalhes da Solicitação</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <strong>Data da Falta:</strong> <?= htmlspecialchars($solicitacao['data_falta']) ?><br>
                                                <strong>Motivo:</strong> <?= htmlspecialchars($solicitacao['motivo']) ?><br>
                                                <strong>Status da Justificativa:</strong> <?= htmlspecialchars($solicitacao['justificativa_status']) ?><br>
                                                <strong>Plano de Reposição:</strong><br>
                                                <?php if ($solicitacao['plano_id']): ?>
                                                    <strong>Data:</strong> <?= htmlspecialchars($solicitacao['data_reposicao']) ?><br>
                                                    <strong>Início:</strong> <?= htmlspecialchars($solicitacao['horario_inicio']) ?><br>
                                                    <strong>Término:</strong> <?= htmlspecialchars($solicitacao['horario_termino']) ?><br>
                                                    <strong>Disciplina:</strong> <?= htmlspecialchars($solicitacao['disciplina']) ?><br>
                                                    <strong>Status:</strong> <?= htmlspecialchars($solicitacao['reposicao_status']) ?><br>
                                                    <strong>Descrição:</strong> <?= htmlspecialchars($solicitacao['plano_descricao']) ?>
                                                <?php else: ?>
                                                    Não há plano de reposição
                                                <?php endif; ?><br>
                                                <strong>Comentário:</strong><br>
                                                <?php if ($solicitacao['comentario']): ?>
                                                    <?= htmlspecialchars($solicitacao['comentario']) ?><br>
                                                    <strong>Data:</strong> <?= htmlspecialchars($solicitacao['comentario_data']) ?>
                                                <?php else: ?>
                                                    Nenhum comentário registrado
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botão para Editar -->
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditar<?= $solicitacao['justificativa_id'] ?>">Editar</button>

   <!-- Modal de Edição -->
<div class="modal fade" id="modalEditar<?= $solicitacao['justificativa_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Justificativa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="justificativa_id" value="<?= $solicitacao['justificativa_id'] ?>">
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <input type="text" class="form-control" name="motivo" value="<?= htmlspecialchars($solicitacao['motivo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="data_falta">Data da Falta</label>
                        <input type="date" class="form-control" name="data_falta" value="<?= htmlspecialchars($solicitacao['data_falta']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="data_reposicao">Data de Reposição</label>
                        <input type="date" class="form-control" name="data_reposicao" value="<?= htmlspecialchars($solicitacao['data_reposicao']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="horario_inicio">Horário de Início</label>
                        <input type="time" class="form-control" name="horario_inicio" value="<?= htmlspecialchars($solicitacao['horario_inicio']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="horario_termino">Horário de Término</label>
                        <input type="time" class="form-control" name="horario_termino" value="<?= htmlspecialchars($solicitacao['horario_termino']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="disciplina">Disciplina</label>
                        <input type="text" class="form-control" name="disciplina" value="<?= htmlspecialchars($solicitacao['disciplina']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" name="descricao"><?= htmlspecialchars($solicitacao['plano_descricao']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="arquivo">Arquivo (opcional)</label>
                        <input type="file" class="form-control" name="arquivo">
                        <?php if (!empty($solicitacao['comprovante'])): ?>
                            <small><a href="<?= htmlspecialchars($solicitacao['comprovante']) ?>" target="_blank">Ver Arquivo Atual</a></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma solicitação encontrada.</p>
        <?php endif; ?>
    </div>
</body>
</html>
