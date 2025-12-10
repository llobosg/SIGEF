<?php
session_start();
require_once 'utils.php';

log_debug("index.php: inicio. Sesión actual: " . json_encode($_SESSION));

if (isset($_SESSION['user_id'])) {
    $rol = $_SESSION['rol'] ?? 'basico';
    error_log("index.php: usuario autenticado. Rol: $rol");
    if ($rol === 'admin') {
        header("Location: /pages/vehiculos_view.php");
    } else {
        header("Location: /pages/dashboard_basico.php");
    }
    exit;
} else {
    error_log("index.php: usuario NO autenticado. Redirigiendo a login.php");
    header("Location: /login.php");
    exit;
}
?>