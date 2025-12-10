<?php
require '../auth.php';
if ($_SESSION['rol'] !== 'basico') {
    header("Location: vehiculos_view.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="main-header">
        <div class="logo">SIGEF</div>
        <nav>
            <a href="dashboard_basico.php">Inicio</a>
            <a href="../logout.php">Salir</a>
        </nav>
    </div>
    <div class="container">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['user']) ?> (Rol: Básico)</h2>
        <p>Acceso limitado. Próximamente: tus próximas mantenciones, licencia, etc.</p>
    </div>
</body>
</html>