<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'basico') {
    // Si no es rol básico, redirigir a vehículos (admin)
    header("Location: /pages/vehiculos_view.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <h2>
                <i class="fas fa-tachometer-alt"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user'] ?? 'Usuario') ?>
            </h2>
            <p>Este es tu panel de control como usuario con rol <strong>Básico</strong>.</p>
            <p>Próximamente se agregarán funcionalidades como:</p>
            <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                <li>Visualización de tus próximas mantenciones programadas.</li>
                <li>Alertas de vencimiento de licencia.</li>
                <li>Acceso a guías y montos asignados.</li>
            </ul>
        </div>

        <!-- Tarjetas futuras (opcional) -->
        <!--
        <div class="card">
            <h3><i class="fas fa-exclamation-triangle"></i> Alertas</h3>
            <p>No hay alertas pendientes.</p>
        </div>
        -->
    </div>
</body>
</html>