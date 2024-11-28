<?php
session_start();

function verificarAcesso($perfilNecessario) {
    if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== $perfilNecessario) {
        header("Location: login.php");
        exit;
    }
}
?>
