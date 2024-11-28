<?php
session_start();
require 'db.php'; // Conexão com o banco de dados

// Verificar se o usuário está logado e se o perfil é 'professor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'professor') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

$erro = "";
$sucesso = "";

// Processar o formulário de cadastro de justificativa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['justificativa_id'])) {
    // Receber e validar os dados do formulário
    $curso = isset($_POST['curso']) ? $_POST['curso'] : null;
    $data_falta = isset($_POST['data_falta']) ? $_POST['data_falta'] : null;
    $data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $tipo_justificativa = isset($_POST['tipo_justificativa']) ? $_POST['tipo_justificativa'] : null;
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : null;
    $comprovante = isset($_FILES['comprovante']) ? $_FILES['comprovante'] : null;

    // Verificar se os campos obrigatórios foram preenchidos
    if (!$curso || !$data_falta || !$data_inicio || !$data_fim || !$tipo_justificativa || !$motivo) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Processar o upload do comprovante, se houver
        $caminho_comprovante = null;
        if ($comprovante && $comprovante['error'] === UPLOAD_ERR_OK) {
            $diretorio = 'uploads/';
            $nome_arquivo = uniqid() . '_' . basename($comprovante['name']);
            $caminho_comprovante = $diretorio . $nome_arquivo;

            if (!move_uploaded_file($comprovante['tmp_name'], $caminho_comprovante)) {
                $erro = "Erro ao fazer upload do comprovante.";
            }
        }

        // Se não houver erro, inserir no banco
        if (!$erro) {
            // Transforma os cursos em uma string separada por vírgulas para salvar no banco
            $cursos_str = implode(',', $curso);

            $stmt = $pdo->prepare("INSERT INTO justificativas (curso, data_falta, data_inicio, data_fim, tipo_justificativa, motivo, comprovante, usuario_id) 
                                    VALUES (:curso, :data_falta, :data_inicio, :data_fim, :tipo_justificativa, :motivo, :comprovante, :usuario_id)");
            $stmt->bindParam(':curso', $cursos_str); // Cursos separados por vírgula
            $stmt->bindParam(':data_falta', $data_falta);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->bindParam(':tipo_justificativa', $tipo_justificativa);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':comprovante', $caminho_comprovante);
            $stmt->bindParam(':usuario_id', $usuario_id);

            if ($stmt->execute()) {
                $sucesso = "Justificativa cadastrada com sucesso.";
            } else {
                $erro = "Erro ao cadastrar a justificativa.";
            }
        }
    }
}

// Processar o formulário de edição de justificativa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['justificativa_id'])) {
    // Receber e validar os dados de edição
    $justificativa_id = $_POST['justificativa_id'];
    $curso_edit = isset($_POST['cursos_edit']) ? $_POST['cursos_edit'] : [];
    $tipo_justificativa_edit = isset($_POST['tipo_justificativa_edit']) ? $_POST['tipo_justificativa_edit'] : null;
    $motivo_edit = isset($_POST['motivo_edit']) ? $_POST['motivo_edit'] : null;
    $comprovante_edit = isset($_FILES['comprovante_edit']) ? $_FILES['comprovante_edit'] : null;

    // Verificar se os campos obrigatórios foram preenchidos
    if (!$curso_edit || !$tipo_justificativa_edit || !$motivo_edit) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Processar o upload do comprovante, se houver
        $caminho_comprovante_edit = null;
        if ($comprovante_edit && $comprovante_edit['error'] === UPLOAD_ERR_OK) {
            $diretorio = 'uploads/';
            $nome_arquivo = uniqid() . '_' . basename($comprovante_edit['name']);
            $caminho_comprovante_edit = $diretorio . $nome_arquivo;

            if (!move_uploaded_file($comprovante_edit['tmp_name'], $caminho_comprovante_edit)) {
                $erro = "Erro ao fazer upload do comprovante.";
            } 
        }

        // Se não houver erro, atualizar no banco
        if (!$erro) {
            // Transforma os cursos em uma string separada por vírgulas para salvar no banco
            $cursos_str_edit = implode(',', $curso_edit);

            // Verificar o status da justificativa antes de atualizar
            $stmt_status = $pdo->prepare("SELECT status FROM justificativas WHERE id = :id AND usuario_id = :usuario_id");
            $stmt_status->bindParam(':id', $justificativa_id);
            $stmt_status->bindParam(':usuario_id', $usuario_id);
            $stmt_status->execute();
            $status_atual = $stmt_status->fetchColumn();

            // Se o status for "rejeitado", alterar para "em análise"
            if ($status_atual === 'rejeitado') {
                $novo_status = 'em análise';
            } else {
                $novo_status = $status_atual; // Se o status não for "rejeitado", mantemos o atual
            }

            // Atualizar a justificativa
            $stmt = $pdo->prepare("UPDATE justificativas SET curso = :curso, tipo_justificativa = :tipo_justificativa, motivo = :motivo, comprovante = :comprovante, status = :status WHERE id = :id AND usuario_id = :usuario_id");
            $stmt->bindParam(':curso', $cursos_str_edit);
            $stmt->bindParam(':tipo_justificativa', $tipo_justificativa_edit);
            $stmt->bindParam(':motivo', $motivo_edit);
            $stmt->bindParam(':comprovante', $caminho_comprovante_edit);
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':id', $justificativa_id);
            $stmt->bindParam(':usuario_id', $usuario_id);

            if ($stmt->execute()) {
                $sucesso = "Justificativa editada com sucesso.";
            } else {
                $erro = "Erro ao editar a justificativa.";
            }
        }
    }
}

// Consultar justificativas existentes
$stmt = $pdo->prepare("SELECT id, curso, data_falta, tipo_justificativa, motivo, status, comprovante FROM justificativas WHERE usuario_id = :usuario_id");
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$justificativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificativas - Professor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Link para o arquivo de CSS externo -->
    <link rel="stylesheet" href="dashboard_professor.css">
    <link rel="stylesheet" href="justificativa.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar já existente, sem alteração -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="img/logo.jpg" id="logo" alt="Logo">
            <h2>Bem-vindo, <?php echo $usuario_nome; ?>!</h2>
            <p>Email: <?php echo $usuario_email; ?></p>
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

    <!-- Conteúdo -->
    <div class="content">
        <div class="header text-center">
            <h1>Justificativas</h1>
            <p>Aqui você pode cadastrar e visualizar suas justificativas de falta.</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= $sucesso ?></div>
        <?php endif; ?>

        <!-- Formulário para cadastrar justificativa -->
        <div class="form-container">
            <h2>Cadastrar Justificativa</h2>
            <form action="justificativa.php" method="POST" enctype="multipart/form-data">
                <!-- Cursos - Caixa de Seleção -->
                <div class="form-group">
                    <label for="curso">Cursos envolvidos na ausência (selecione mais de um, se necessário):</label>
                    <div class="checkbox-container">
                        <label class="checkbox-label">
                            <input type="checkbox" name="curso[]" value="DSM">
                            DSM
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="curso[]" value="GTI">
                            GTI
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="curso[]" value="GPI">
                            GPI
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="curso[]" value="GE">
                            GE
                        </label>
                    </div>
                </div>

                <!-- Data da Falta -->
                <div class="form-group">
                    <label for="data_falta">Data da Falta:</label>
                    <input type="date" id="data_falta" name="data_falta" class="form-control" required>
                </div>

                <!-- Período de Falta -->
                <div class="form-group">
                    <label for="data_inicio">Período de Falta - Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="data_fim">Período de Falta - Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" required>
                </div>

                <!-- Tipo de Justificativa -->
                <div class="form-group">
                    <label for="tipo_justificativa">Tipo de Justificativa:</label>
                    <select id="tipo_justificativa" name="tipo_justificativa" class="form-control" required>
                        <option value="1">LICENÇA E FALTA MÉDICA</option>
                        <option value="2">FALTA INJUSTIFICADA</option>
                        <option value="3">FALTAS JUSTIFICADAS</option>
                        <option value="4">FALTAS PREVISTAS NA LEGISLAÇÃO</option>
                    </select>
                </div>

                <!-- Motivo -->
                <div class="form-group">
                    <label for="motivo">Motivo:</label>
                    <textarea id="motivo" name="motivo" class="form-control" rows="4" required></textarea>
                </div>

                <!-- Comprovante -->
                <div class="form-group">
                    <label for="comprovante">Comprovante (opcional):</label>
                    <input type="file" name="comprovante" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Cadastrar Justificativa</button>
            </form>
        </div>
<!-- Justificativas já enviadas -->
<div class="justificativas-list">
    <h2>Minhas Justificativas</h2>
    <?php if (count($justificativas) > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Data da Falta</th>
                    <th>Tipo de Justificativa</th>
                    <th>Motivo</th>
                    <th>Status</th>
                    <th>Detalhes</th> <!-- Coluna para o botão de detalhes -->
                    <th>Editar</th> <!-- Coluna para o botão de editar -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($justificativas as $justificativa): ?>
                    <tr>
                        <td><?php echo $justificativa['curso']; ?></td>
                        <td><?php echo $justificativa['data_falta']; ?></td>
                        <td><?php echo $justificativa['tipo_justificativa']; ?></td>
                        <td><?php echo $justificativa['motivo']; ?></td>
                        <td><?php echo $justificativa['status']; ?></td>
                        <td>
                            <!-- Botões de Detalhes -->
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $justificativa['id']; ?>">Detalhes</button>
                        </td>
                        <td>
                            <!-- Exibe o botão de editar somente para justificativas com status 'Rejeitada' -->
                            <?php if ($justificativa['status'] === 'rejeitado'): ?>
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $justificativa['id']; ?>">Editar</button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Editar</button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal para detalhes -->
                    <div class="modal fade" id="detalhesModal<?php echo $justificativa['id']; ?>" tabindex="-1" aria-labelledby="detalhesModalLabel<?php echo $justificativa['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detalhesModalLabel<?php echo $justificativa['id']; ?>">Detalhes da Justificativa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Curso:</strong> <?php echo $justificativa['curso']; ?></p>
                                    <p><strong>Data da Falta:</strong> <?php echo $justificativa['data_falta']; ?></p>
                                    <p><strong>Tipo de Justificativa:</strong> <?php echo $justificativa['tipo_justificativa']; ?></p>
                                    <p><strong>Motivo:</strong> <?php echo $justificativa['motivo']; ?></p>
                                    <p><strong>Status:</strong> <?php echo $justificativa['status']; ?></p>
                                    <?php if ($justificativa['comprovante']): ?>
                                        <p><strong>Comprovante:</strong> <a href="<?php echo $justificativa['comprovante']; ?>" target="_blank">Visualizar</a></p>
                                    <?php else: ?>
                                        <p><strong>Comprovante:</strong> Não enviado</p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

            <!-- Modal para edição -->
<div class="modal fade" id="editModal<?php echo $justificativa['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $justificativa['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $justificativa['id']; ?>">Reenviar Justificativa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário de Edição -->
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="justificativa_id" value="<?php echo $justificativa['id']; ?>">

                    <div class="form-group">
                        <label for="curso_edit<?php echo $justificativa['id']; ?>">Cursos</label>
                        <?php
                        $cursos = ['DSM', 'GTI', 'GPI','GE'];
                        $cursos_selecionados = explode(',', $justificativa['curso']);
                        foreach ($cursos as $curso):
                        ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="cursos_edit[]" value="<?php echo $curso; ?>" <?php echo in_array($curso, $cursos_selecionados) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="curso_edit<?php echo $curso; ?>"><?php echo $curso; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <label for="motivo_edit<?php echo $justificativa['id']; ?>">Motivo</label>
                        <textarea name="motivo_edit" class="form-control" required><?php echo $justificativa['motivo']; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="comprovante_edit<?php echo $justificativa['id']; ?>">Novo Comprovante</label>
                        <input type="file" name="comprovante_edit" class="form-control">
                        <small class="form-text text-muted">Se desejar enviar um novo comprovante, selecione-o acima. Caso contrário, o antigo será mantido.</small>
                    </div>

                    <div class="form-group">
                        <label for="tipo_justificativa_edit<?php echo $justificativa['id']; ?>">Tipo de Justificativa</label>
                        <select name="tipo_justificativa_edit" class="form-control" required>
                            <option value="1" <?php echo ($justificativa['tipo_justificativa'] === '1') ? 'selected' : ''; ?>>LICENÇA E FALTA MÉDICA</option>
                            <option value="2" <?php echo ($justificativa['tipo_justificativa'] === '2') ? 'selected' : ''; ?>>FALTA INJUSTIFICADA</option>
                            <option value="3" <?php echo ($justificativa['tipo_justificativa'] === '3') ? 'selected' : ''; ?>>FALTAS JUSTIFICADAS</option>
                            <option value="4" <?php echo ($justificativa['tipo_justificativa'] === '4') ? 'selected' : ''; ?>>FALTAS PREVISTAS NA LEGISLAÇÃO</option>
                        </select>
                    </div>

                    <!-- Alterando o status para "Em Análise" -->
                    <input type="hidden" name="status_edit" value="Em Análise">

                    <button type="submit" class="btn btn-primary mt-3">Reenviar Justificativa</button>
                </form>
            </div>
        </div>
    </div>
</div>



                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não cadastrou nenhuma justificativa.</p>
    <?php endif; ?>
</div>

</body>
</html>
