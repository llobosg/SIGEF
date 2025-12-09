<?php
// Asegúrate de que $_SESSION esté activa
$rol = $_SESSION['rol'] ?? 'basico';
?>
<header class="main-header">
    <div class="logo">SIGEF</div>
    <nav>
        <?php if ($rol === 'admin'): ?>
            <a href="vehiculos.php">Ficha Camión</a>
            <a href="personal.php">Personal</a>
            <a href="dashboard_admin.php">Dashboard</a>
        <?php else: ?>
            <a href="dashboard_basico.php">Inicio</a>
        <?php endif; ?>
        <a href="logout.php" style="color: #e74c3c;">Salir</a>
    </nav>
</header>