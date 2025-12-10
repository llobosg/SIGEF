<?php
// includes/header.php - Menú superior reutilizable
$rol = $_SESSION['rol'] ?? 'basico';
?>
<div class="main-header">
    <div class="logo">SIGEF</div>
    <nav>
        <?php if ($rol === 'admin'): ?>
            <a href="/pages/vehiculos_view.php">Vehículos</a>
            <a href="/pages/personas_view.php">Personal</a>
            <a href="/pages/monto_view.php">Montos</a>
        <?php else: ?>
            <a href="/pages/dashboard_basico.php">Inicio</a>
        <?php endif; ?>
        <a href="/logout.php" style="color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </nav>
</div>