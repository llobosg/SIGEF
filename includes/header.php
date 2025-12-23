<?php
// includes/header.php - Menú superior con íconos y roles
$rol = $_SESSION['rol'] ?? 'basico';
?>
<div class="main-header">
    <div class="logo">SIGEF</div>
    <nav>
        <?php if ($rol === 'admin'): ?>
            <a href="/pages/dashboard_admin.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="/pages/mantencion_view.php">
                <i class="fas fa-wrench"></i> Gastos Flota
            </a>
            <a href="/pages/facturacion_view.php">
                <i class="fas fa-file-invoice"></i> Facturación
            </a>
            <a href="/pages/vehiculos_view.php">
                <i class="fas fa-truck"></i> Vehículos
            </a>
            <a href="/pages/personas_view.php">
                <i class="fas fa-user-friends"></i> Personal
            </a>
            <a href="/pages/monto_view.php">
                <i class="fas fa-money-bill-wave"></i> Montos
            </a>
        <?php else: ?>
            <a href="/pages/dashboard_basico.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        <?php endif; ?>
        <a href="/logout.php" style="color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </nav>
</div>