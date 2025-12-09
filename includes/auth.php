<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = trim($_POST['rut'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id_personal, nombre, rol FROM PERSONAL WHERE rut = ? AND password = ?");
    $stmt->execute([$rut, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id_personal'];
        $_SESSION['user'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        
        // Redirigir según rol
        if ($user['rol'] === 'admin') {
            header("Location: pages/vehiculos_view.php");
        } else {
            header("Location: pages/dashboard_basico.php");
        }
        exit;
    } else {
        // Credenciales inválidas → volver al login con error
        header("Location: login.php?error=1");
        exit;
    }
} else {
    // Acceso directo a auth.php → redirigir a login
    header("Location: login.php");
    exit;
}
?>