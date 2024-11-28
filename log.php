<?php
function registrarLog($usuario_id, $acao, $descricao) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, acao, descricao) VALUES (:usuario_id, :acao, :descricao)");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':acao', $acao);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->execute();
}
?>
