<?php
    require '../session_check.php';
    if ($_SESSION['rol'] !== 'admin') {
        header("Location: /pages/dashboard_basico.php");
        exit;
    }

    require '../config.php';
    $pdo = getDBConnection();

    // Obtener nombre del usuario
    $nombre_usuario = $_SESSION['user'] ?? 'Administrador';

    // Estadísticas
    $totalVehiculos = (int)$pdo->query("SELECT COUNT(*) FROM VEHICULO")->fetchColumn();
    $totalPersonal = (int)$pdo->query("SELECT COUNT(*) FROM PERSONAL")->fetchColumn();
    $totalMantenciones = (int)$pdo->query("SELECT COUNT(*) FROM MANTENCION")->fetchColumn();

    // Últimas mantenciones
    $stmt = $pdo->prepare("
        SELECT fecha_mant, nombre_vehiculo, tipo_mant, costo
        FROM MANTENCION
        ORDER BY fecha_mant DESC
        LIMIT 10
    ");
    $stmt->execute();
    $ultimasMantenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container" style="padding: 0 1%;">
        <!-- Saludo -->
        <div style="margin-bottom: 1.5rem; padding: 0.8rem; background-color: #e9ecef; border-radius: 6px;">
            <h2 style="margin: 0; font-size: 1.2rem; color: #2c3e50;">
                Bienvenido/a, <?= htmlspecialchars($nombre_usuario) ?>
            </h2>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #f8f9fa; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 0.8rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">
                    <i class="fas fa-truck"></i> Vehículos
                </h3>
                <p style="font-size: 2rem; font-weight: bold; color: #2c3e50; margin: 0;"><?= $totalVehiculos ?></p>
            </div>
            <div style="background: #f0f9ff; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 0.8rem 0; color: #27ae60; font-size: 1rem; font-weight: 600;">
                    <i class="fas fa-user-friends"></i> Personal
                </h3>
                <p style="font-size: 2rem; font-weight: bold; color: #27ae60; margin: 0;"><?= $totalPersonal ?></p>
            </div>
            <div style="background: #f0fdf4; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 0.8rem 0; color: #8e44ad; font-size: 1rem; font-weight: 600;">
                    <i class="fas fa-wrench"></i> Mantenciones
                </h3>
                <p style="font-size: 2rem; font-weight: bold; color: #8e44ad; margin: 0;"><?= $totalMantenciones ?></p>
            </div>
        </div>

        <!-- Búsqueda rápida -->
        <div style="margin-bottom: 1.5rem;">
            <input type="text" id="search-global" 
                   placeholder="Buscar vehículos por mantención"
                   style="width: 100%; max-width: 400px; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        </div>

        <!-- Últimas mantenciones -->
        <h3 style="margin: 1.5rem 0 1rem 0; color: #2c3e50; font-size: 1.1rem;">
            <i class="fas fa-history"></i> Últimas Mantenciones
        </h3>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasMantenciones as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['fecha_mant']) ?></td>
                            <td><?= htmlspecialchars($m['nombre_vehiculo']) ?></td>
                            <td><?= htmlspecialchars($m['tipo_mant']) ?></td>
                            <td>$<?= number_format($m['costo'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('search-global').addEventListener('keyup', function() {
            // Implementar búsqueda en futuro (por ahora solo UI)
        });
    </script>
</body>
</html>