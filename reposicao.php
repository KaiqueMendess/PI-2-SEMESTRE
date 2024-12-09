<?php
session_start();

// Verificar se o usuário está logado e se o perfil é 'professor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'professor') {
    header("Location: login.php"); // Redireciona para o login se não for professor
    exit;
}

require 'db.php'; // Conexão com o banco de dados

$erro = "";
$sucesso = "";

// Obter informações do professor
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];
$usuario_email = $_SESSION['user_email'];

// Consultar justificativas aprovadas para o professor
$stmt = $pdo->prepare("SELECT id, data_falta, motivo FROM justificativas WHERE usuario_id = :usuario_id AND status = 'aprovado'");
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$justificativasAprovadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Horários fixos por turno
$horariosPorTurno = [
    'Manhã' => [
        '07:40 - 08:30', '08:30 - 09:20', '09:20 - 10:10', '10:10 - 11:00', '11:00 - 11:50'
    ],
    'Tarde' => [
        '13:00 - 13:50', '13:50 - 14:40', '14:40 - 15:30', '15:30 - 16:20', '16:20 - 17:10'
    ],
    'Noite' => [
        '18:10 - 19:00', '19:00 - 19:50', '19:50 - 20:40', '20:40 - 21:30', '21:30 - 22:20'
    ]
];

// Filtro de disciplina
$filtroDisciplina = $_GET['filtro_disciplina'] ?? '';
$filtroQuery = $filtroDisciplina ? "AND disciplina = :filtro_disciplina" : "";

// Consulta para listar reposições do professor
$stmt_reposicoes = $pdo->prepare("
    SELECT id, data_reposicao, horario_inicio, horario_termino, disciplina, status, motivo_rejeicao, descricao 
    FROM reposicoes 
    WHERE usuario_id = :usuario_id $filtroQuery 
    ORDER BY data_reposicao DESC
");
$stmt_reposicoes->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

if ($filtroDisciplina) {
    $stmt_reposicoes->bindParam(':filtro_disciplina', $filtroDisciplina, PDO::PARAM_STR);
}

$stmt_reposicoes->execute();
$reposicoes = $stmt_reposicoes->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Registrar novo plano de reposição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plano_reposicao'])) {
    $data_reposicao = filter_input(INPUT_POST, 'data_reposicao', FILTER_SANITIZE_STRING);
    $horario_selecionado = filter_input(INPUT_POST, 'horario', FILTER_SANITIZE_STRING);
    $disciplina = filter_input(INPUT_POST, 'disciplina', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING); // Novo campo

    // Validação: Garantir que a data não seja domingo
    $diaSemana = date('w', strtotime($data_reposicao));
    if ($diaSemana == 0) { // 0 = Domingo
        $erro = "A data da reposição não pode ser em um domingo.";
    } elseif (!$data_reposicao || !$horario_selecionado || !$disciplina) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reposicoes (usuario_id, data_reposicao, horario_inicio, horario_termino, disciplina, descricao, status) 
                               VALUES (:usuario_id, :data_reposicao, :horario_inicio, :horario_termino, :disciplina, :descricao, 'Em análise')");
        $horarios = explode(' - ', $horario_selecionado);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT); // Substitui justificativa_id por usuario_id
        $stmt->bindParam(':data_reposicao', $data_reposicao);
        $stmt->bindParam(':horario_inicio', $horarios[0]);
        $stmt->bindParam(':horario_termino', $horarios[1]);
        $stmt->bindParam(':disciplina', $disciplina);
        $stmt->bindParam(':descricao', $descricao); // Bind do novo campo

        if ($stmt->execute()) {
            $sucesso = "Plano de reposição registrado com sucesso!";
        } else {
            $erro = "Erro ao registrar o plano de reposição.";
        }
    }
}

// Reenviar reposição e alterar status para "Em análise"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reenviar_reposicao'])) {
    $reposicao_id = filter_input(INPUT_POST, 'reposicao_id', FILTER_SANITIZE_NUMBER_INT);
    $horario_inicio = filter_input(INPUT_POST, 'horario_inicio', FILTER_SANITIZE_STRING);
    $horario_termino = filter_input(INPUT_POST, 'horario_termino', FILTER_SANITIZE_STRING);
    $disciplina = filter_input(INPUT_POST, 'disciplina', FILTER_SANITIZE_STRING);
    $data_reposicao = filter_input(INPUT_POST, 'data_aula', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);

    if ($reposicao_id && $horario_inicio && $horario_termino && $disciplina && $data_reposicao && $descricao) {
        // Atualizar status para "Em análise" e os outros campos
        $stmt_reenviar = $pdo->prepare("UPDATE reposicoes 
                                        SET status = 'Em análise', 
                                            horario_inicio = :horario_inicio,
                                            horario_termino = :horario_termino,
                                            disciplina = :disciplina,
                                            data_reposicao = :data_reposicao,
                                            descricao = :descricao 
                                        WHERE id = :reposicao_id");
        $stmt_reenviar->bindParam(':reposicao_id', $reposicao_id, PDO::PARAM_INT);
        $stmt_reenviar->bindParam(':horario_inicio', $horario_inicio);
        $stmt_reenviar->bindParam(':horario_termino', $horario_termino);
        $stmt_reenviar->bindParam(':disciplina', $disciplina);
        $stmt_reenviar->bindParam(':data_reposicao', $data_reposicao);
        $stmt_reenviar->bindParam(':descricao', $descricao);

        if ($stmt_reenviar->execute()) {
            $sucesso = "Reposição reenviada com sucesso! Status atualizado para 'Em análise'.";
        } else {
            $erro = "Erro ao reenviar reposição.";
        }
    } else {
        $erro = "Todos os campos devem ser preenchidos.";
    }
}

?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Reposições</title>
    <link rel="stylesheet" href="dashboard_professor.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <style>/* Estilos Gerais da Página (excluindo sidebar) */
.page-content {
    margin-left: 270px; /* Espaço para acomodar a sidebar */
    padding: 20px;
    background-color: #f9f9f9;
    min-height: 100vh;
    overflow-x: hidden;
    font-family: Arial, sans-serif;
    color: #333;
}

/* Cabeçalho */
.header {
    text-align: center;
    margin-bottom: 20px;
}

.header h1 {
    font-size: 1.8em;
    color: #000000;
}

/* Formulários */
.form-container {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
}

.form-container h2 {
    margin-bottom: 15px;
    font-size: 1.5em;
    color: #333;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

select, input[type="text"], input[type="date"], input[type="time"], textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
}

button[type="submit"] {
    background-color: #c53c3c;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
}

button[type="submit"]:hover {
    background-color: #c53c3c;
}

/* Alertas */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-weight: bold;
    text-align: center;
}

.alert-success {
    background-color: #dff0d8;
    color: #3c763d;
}

.alert-danger {
    background-color: #f2dede;
    color: #a94442;
}

/* Tabela */
.reposicoes-list h2 {
    font-size: 1.5em;
    margin-bottom: 15px;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
    font-size: 0.95em;
}

table th {
    background-color: #c53c3c;
    color: #fff;
    text-transform: uppercase;
}

table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Status Dinâmico */
td:last-child {
    font-weight: bold;
    text-transform: uppercase;
}

td:last-child:contains("Pendente") {
    color: #ff9800;
}

td:last-child:contains("Aprovada") {
    color: #4caf50;
}

/* Modal */
.modal {
    display: none; /* Esconde o modal por padrão */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Fundo escuro */
    overflow: auto;
    padding-top: 60px;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 8px;
}

.modal-header {
    font-size: 1.5em;
    color: #333;
    margin-bottom: 10px;
}

.modal-footer {
    margin-top: 20px;
    text-align: right;
}

.close {
    color: #aaa;
    float: right;
    font-size: 1.5em;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Adicionando classe 'active' para exibir o modal */
.modal.active {
    display: block;
}

/* Responsividade */
@media (max-width: 768px) {
    .page-content {
        margin-left: 0; /* Sidebar será ocultada ou ajustada */
    }

    .form-container, .reposicoes-list {
        padding: 10px;
    }

    table th, table td {
        padding: 8px;
        font-size: 0.85em;
    }

    button[type="submit"] {
        width: 100%;
        padding: 10px;
    }

    .modal-content {
        width: 90%;
    }
}

</style>
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

    <div class="content">
        <div class="header">
            <h1>Gerenciar Reposições</h1>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= $erro; ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= $sucesso; ?></div>
        <?php endif; ?>

        <div class="form-container">
    <form method="POST">
        <h2>Registrar Novo Plano de Reposição</h2>

        <!-- Seleção de Justificativa -->
        <label for="justificativa_id">Justificativa Aprovada:</label>
        <select id="justificativa_id" name="justificativa_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($justificativasAprovadas as $justificativa): ?>
                <option value="<?= $justificativa['id']; ?>">
                    Data: <?= $justificativa['data_falta']; ?> - Motivo: <?= $justificativa['motivo']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Seleção de Curso -->
        <label for="disciplina">Disciplina:</label>
        <select id="disciplina" name="disciplina" required>
            <option value="">Selecione...</option>
            <option value="DSM">DSM</option>
            <option value="GPI">GPI</option>
            <option value="GTI">GTI</option>
        </select>

        <!-- Seleção de Data -->
        <label for="data_reposicao">Data da Reposição:</label>
        <input type="date" id="data_reposicao" name="data_reposicao" required>

        <!-- Seleção de Turno e Horário -->
        <label for="turno">Turno:</label>
        <select id="turno" name="turno" onchange="atualizarHorarios()" required>
            <option value="">Selecione...</option>
            <option value="Manhã">Manhã</option>
            <option value="Tarde">Tarde</option>
            <option value="Noite">Noite</option>
        </select>

        <label for="horario">Horário:</label>
        <select id="horario" name="horario" required>
            <option value="">Selecione um turno primeiro...</option>
        </select>

        <!-- Campo Descrição -->
        <label for="descricao">Descrição (opcional):</label>
        <textarea id="descricao" name="descricao" rows="4" placeholder="Detalhe mais sobre a reposição (opcional)"></textarea>

        <button type="submit" class="btn btn-primary btn-block" name="plano_reposicao">Registrar Reposição</button>
    </form>
</div>


        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Detalhes</th>
                    <th>Editar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reposicoes)): ?>
                    <?php foreach ($reposicoes as $reposicao): ?>
                        <tr>
                            <td><?php echo $reposicao['disciplina']; ?></td>
                            <td><?php echo $reposicao['data_reposicao']; ?></td>
                            <td><?php echo $reposicao['horario_inicio'] . ' - ' . $reposicao['horario_termino']; ?></td>
                            <td><?php echo $reposicao['descricao']; ?></td>
                            <td><?php echo $reposicao['status']; ?></td>
                            <td>
                                <!-- Botão de Detalhes -->
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $reposicao['id']; ?>">Detalhes</button>
                            </td>
                            <td>
                                <!-- Botão de Editar, só visível para reposições com status 'Rejeitada' -->
                                <?php if ($reposicao['status'] === 'Rejeitada'): ?>
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $reposicao['id']; ?>">Editar</button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Editar</button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal para Detalhes -->
                        <div class="modal fade" id="detalhesModal<?php echo $reposicao['id']; ?>" tabindex="-1" aria-labelledby="detalhesModalLabel<?php echo $reposicao['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detalhesModalLabel<?php echo $reposicao['id']; ?>">Detalhes da Reposição</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Disciplina:</strong> <?php echo $reposicao['disciplina']; ?></p>
                                        <p><strong>Data da Reposição:</strong> <?php echo $reposicao['data_reposicao']; ?></p>
                                        <p><strong>Horário:</strong> <?php echo $reposicao['horario_inicio'] . ' - ' . $reposicao['horario_termino']; ?></p>
                                        <p><strong>Descrição:</strong> <?php echo $reposicao['descricao']; ?></p>
                                        <p><strong>Status:</strong> <?php echo $reposicao['status']; ?></p>
                                        <?php if ($reposicao['motivo_rejeicao']): ?>
                                            <p><strong>Motivo da Rejeição:</strong> <?php echo $reposicao['motivo_rejeicao']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

<!-- Modal para Edição (apenas para status 'Rejeitado') -->
<div class="modal fade" id="editModal<?php echo $reposicao['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $reposicao['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $reposicao['id']; ?>">Reenviar Reposição</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário de Edição -->
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="reposicao_id" value="<?php echo $reposicao['id']; ?>">

                    <!-- Campo para alterar o horário de início -->
                    <div class="form-group">
                        <label for="horario_inicio">Horário da Aula (Início):</label>
                        <select class="form-control" id="horario_inicio" name="horario_inicio" required>
                            <?php 
                            // Exibe as opções de horários de acordo com o turno
                            foreach ($horariosPorTurno as $turno => $horarios): ?>
                                <optgroup label="<?php echo $turno; ?>">
                                    <?php foreach ($horarios as $horario): ?>
                                        <option value="<?php echo $horario; ?>" <?php echo ($horario == $reposicao['horario_inicio']) ? 'selected' : ''; ?>><?php echo $horario; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo para alterar o horário de término -->
                    <div class="form-group">
                        <label for="horario_termino">Horário da Aula (Término):</label>
                        <select class="form-control" id="horario_termino" name="horario_termino" required>
                            <?php 
                            // Exibe as opções de horários de acordo com o turno
                            foreach ($horariosPorTurno as $turno => $horarios): ?>
                                <optgroup label="<?php echo $turno; ?>">
                                    <?php foreach ($horarios as $horario): ?>
                                        <option value="<?php echo $horario; ?>" <?php echo ($horario == $reposicao['horario_termino']) ? 'selected' : ''; ?>><?php echo $horario; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo para alterar a disciplina -->
                    <div class="form-group">
                        <label for="disciplina">Disciplina:</label>
                        <select class="form-control" id="disciplina" name="disciplina" required>
                            <option value="">Selecione...</option>
                            <option value="DSM" <?php echo ($reposicao['disciplina'] == 'DSM') ? 'selected' : ''; ?>>DSM</option>
                            <option value="GPI" <?php echo ($reposicao['disciplina'] == 'GPI') ? 'selected' : ''; ?>>GPI</option>
                            <option value="GTI" <?php echo ($reposicao['disciplina'] == 'GTI') ? 'selected' : ''; ?>>GTI</option>
                            <!-- Adicione mais disciplinas conforme necessário -->
                        </select>
                    </div>

                    <!-- Campo para alterar a data (sem domingos) -->
                    <div class="form-group">
                        <label for="data_aula">Data da Aula:</label>
                        <input type="date" class="form-control" id="data_aula" name="data_aula" value="<?php echo $reposicao['data_reposicao']; ?>" required>
                    </div>

                    <!-- Campo para descrição -->
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?php echo $reposicao['descricao']; ?></textarea>
                    </div>

                    <!-- Botão para reenviar a reposição -->
                    <button type="submit" class="btn btn-primary" name="reenviar_reposicao">Reenviar Reposição</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhuma reposição registrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Importar Bootstrap CSS e JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
const horariosPorTurno = {
    'Manhã': ['07:40 - 08:30', '08:30 - 09:20', '09:20 - 10:10', '10:10 - 11:00', '11:00 - 11:50'],
    'Tarde': ['13:00 - 13:50', '13:50 - 14:40', '14:40 - 15:30', '15:30 - 16:20', '16:20 - 17:10'],
    'Noite': ['18:10 - 19:00', '19:00 - 19:50', '19:50 - 20:40', '20:40 - 21:30', '21:30 - 22:20']
};

function atualizarHorarios() {
    const turno = document.getElementById('turno').value;
    const horarioSelect = document.getElementById('horario');
    horarioSelect.innerHTML = '<option value="">Selecione...</option>';

    if (turno && horariosPorTurno[turno]) {
        horariosPorTurno[turno].forEach(horario => {
            const option = document.createElement('option');
            option.value = horario;
            option.textContent = horario;
            horarioSelect.appendChild(option);
        });
    }
}
</script>
</body>
</html>
