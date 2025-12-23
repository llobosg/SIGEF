<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    header("Location: /pages/dashboard_basico.php");
    exit;
}

require '../config.php';
$pdo = getDBConnection();

// Totales
$totalVehiculos = $pdo->query("SELECT COUNT(*) FROM VEHICULO")->fetchColumn();
$totalPersonal = $pdo->query("SELECT COUNT(*) FROM PERSONAL")->fetchColumn();
$totalMantenciones = $pdo->query("SELECT COUNT(*) FROM mantencion")->fetchColumn();

// Últimas mantenciones
$stmt = $pdo->prepare("
    SELECT m.id_mantencion, m.fecha_mant, m.nombre_vehiculo, m.kilometraje, 
           m.tipo_mant, m.taller, m.costo, p.nombre AS nombre_chofer
    FROM mantencion m
    LEFT JOIN PERSONAL p ON p.id_personal = (
        SELECT id_personal FROM PERSONAL LIMIT 1 -- placeholder; ajustar si se vincula
    )
    ORDER BY m.fecha_mant DESC
    LIMIT 10
");
$stmt->execute();
$ultimasMantenciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.2rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 0.8rem;
            color: var(--secondary);
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark);
        }
        .search-section {
            margin: 1.5rem 0;
        }
        .search-section input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
        }
        .prospectos-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1.5rem 0 1rem;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard Administrativo</h2>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-truck"></i>
                <div class="number"><?= $totalVehiculos ?></div>
                <div>Vehículos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends"></i>
                <div class="number"><?= $totalPersonal ?></div>
                <div>Personal</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-wrench"></i>
                <div class="number"><?= $totalMantenciones ?></div>
                <div>Mantenciones</div>
            </div>
        </div>

        <!-- Búsqueda inteligente -->
        <div class="search-section">
            <h3><i class="fas fa-search"></i> Búsqueda Rápida</h3>
            <input type="text" id="busquedaGlobal" 
                   placeholder="Buscar por RUT, patente o nombre de vehículo...">
            <div id="resultadosBusqueda" style="margin-top: 0.5rem; display: none;"></div>
        </div>

        <!-- Últimas mantenciones (interpretado como "prospectos") -->
        <div class="prospectos-title">
            <i class="fas fa-history"></i>
            <h3>Últimas Mantenciones</h3>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vehículo</th>
                        <th>Kilometraje</th>
                        <th>Tipo</th>
                        <th>Taller</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasMantenciones as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['fecha_mant']) ?></td>
                            <td><?= htmlspecialchars($m['nombre_vehiculo']) ?></td>
                            <td><?= $m['kilometraje'] ?: '-' ?></td>
                            <td><?= htmlspecialchars($m['tipo_mant']) ?></td>
                            <td><?= htmlspecialchars($m['taller']) ?: '-' ?></td>
                            <td>$<?= number_format($m['costo'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Búsqueda inteligente (simulada por ahora)
        document.getElementById('busquedaGlobal').addEventListener('input', function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosBusqueda');
            div.style.display = term ? 'block' : 'none';
            if (term) {
                div.innerHTML = `<div style="padding: 0.5rem; background: #ecf0f1; border-radius: 4px;">
                    Resultados para: <strong>${term}</strong> (implementar en versión futura)
                </div>`;
            }
        });

        // Notificación al cargar
        if (window.location.search.includes('msg=welcome')) {
            Toastify({
                text: "👋 Bienvenido, <?= $_SESSION['user'] ?>",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#3498db"
            }).showToast();
        }
    </script>

    <!-- Si usas toastify en otras páginas, asegúrate de incluirlo -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>