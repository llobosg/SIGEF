<?php
// index.php - Punto de entrada básico para SIGEF
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGEF - Sistema de Gestión de Flota</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; }
        h1 { color: #2c3e50; }
        p { line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ SIGEF - Sistema de Gestión de Flota</h1>
        <p>Bienvenido al sistema de gestión de flota.</p>
        <p>Estado: <strong>Aplicación desplegada correctamente en Railway.</strong></p>
        <?php
        // Verificación opcional de conexión a base de datos
        if (file_exists('config.php')) {
            echo "<p style='color:green;'>✓ Archivo config.php detectado.</p>";
        } else {
            echo "<p style='color:orange;'>⚠ Archivo config.php no encontrado.</p>";
        }
        ?>
    </div>
</body>
</html>