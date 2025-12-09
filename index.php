<?php
// index.php - Punto de entrada con redirección inteligente
session_start();

// Si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    $rol = $_SESSION['rol'] ?? 'basico';
    if ($rol === 'admin') {
        header("Location: pages/vehiculos_view.php");
    } else {
        header("Location: pages/dashboard_basico.php");
    }
    exit;
}

// Si no está logueado, va al login
header("Location: login.php");
exit;
?>