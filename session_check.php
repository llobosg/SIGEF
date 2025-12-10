<?php
// session_check.php - Solo verifica sesión, NO procesa login
session_start();

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    die("Acceso denegado: sesión no iniciada.");
}
?>