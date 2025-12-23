<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}
require '../config.php';

// Cargar datos si es edición
$vehiculo = null;
$alert_msg = '';
if (isset($_GET['edit'])) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM VEHICULO WHERE id_vehiculo = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $vehiculo = $stmt->fetch();

    if ($vehiculo && !empty($vehiculo['rev_tecnica'])) {
        $hoy = new DateTime();
        $venc = new DateTime($vehiculo['rev_tecnica']);
        $diff = $hoy->diff($venc);
        if ($venc < $hoy) {
            $alert_msg = "⚠️ Rev. Técnica vencida hace {$diff->days} días.";
        } elseif ($diff->days <= 30) {
            $alert_msg = "⚠️ Rev. Técnica vence en {$diff->days} días.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Vehículo - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <!-- Título fuera del formulario -->
        <div class="page-title">
            <h2><i class="fas fa-truck"></i> Ficha Vehículo</h2>
        </div>

        <div class="card">
            <form method="POST" action="vehiculos_logic.php">
                <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?? '' ?>">

                <div class="form-grid">
                    <!-- Fila 1: Labels superiores -->
                    <label>Marca</label>
                    <label>Modelo</label>
                    <label>Año</label>
                    <label>Patente</label>
                    <label>Nombre Vehículo</label>
                    <label></label> <!-- Espaciador si se necesita -->

                    <!-- Fila 2: Campos 1-5 -->
                    <input type="text" name="marca" value="<?= htmlspecialchars($vehiculo['marca'] ?? '') ?>" required>
                    <input type="text" name="modelo" value="<?= htmlspecialchars($vehiculo['modelo'] ?? '') ?>" required>
                    <input type="number" name="year" value="<?= $vehiculo['year'] ?? '' ?>" required>
                    <input type="text" name="patente" value="<?= htmlspecialchars($vehiculo['patente'] ?? '') ?>" required>
                    <input type="text" name="nombre_vehiculo" value="<?= htmlspecialchars($vehiculo['nombre_vehiculo'] ?? '') ?>" required>
                    <div></div> <!-- Espaciador -->

                    <!-- Fila 3: Labels inferiores -->
                    <label>Permiso Circulación</label>
                    <label>Revisión Técnica</label>
                    <label>N° SOAP</label>
                    <label>Seguro</label>
                    <label>Aseguradora</label>
                    <label>N° Póliza</label>

                    <!-- Fila 4: Campos inferiores -->
                    <input type="date" name="permiso_circ" value="<?= $vehiculo['permiso_circ'] ?? '' ?>">
                    <input type="date" name="rev_tecnica" value="<?= $vehiculo['rev_tecnica'] ?? '' ?>">
                    <input type="number" name="nro_soap" value="<?= $vehiculo['nro_soap'] ?? '' ?>">
                    <select name="seguro">
                        <option value="Si" <?= ($vehiculo['seguro'] ?? 'No') === 'Si' ? 'selected' : '' ?>>Sí</option>
                        <option value="No" <?= ($vehiculo['seguro'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option>
                    </select>
                    <input type="text" name="aseguradora" value="<?= htmlspecialchars($vehiculo['aseguradora'] ?? '') ?>">
                    <input type="text" name="nro_poliza" value="<?= htmlspecialchars($vehiculo['nro_poliza'] ?? '') ?>">
                </div>

                <!-- Botón Guardar fuera del grid, alineado a la derecha -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>

            <?php if (isset($alert_msg)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($alert_msg) ?></div>
            <?php endif; ?>
        </div>

        <!-- Tabla de vehículos -->
        <div class="card">
            <h3>Registro de Vehículos</h3>
            <table class="data-table" id="tablaVehiculos">
                <thead>
                    <tr>
                        <th>Patente</th><th>Marca</th><th>Modelo</th><th>Rev. Técnica</th><th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        fetch('../api/get_vehiculo.php')
            .then(r => r.json())
            .then(data => {
                const tbody = document.querySelector('#tablaVehiculos tbody');
                tbody.innerHTML = data.map(v => `
                    <tr>
                        <td>${v.patente}</td>
                        <td>${v.marca}</td>
                        <td>${v.modelo}</td>
                        <td>${v.rev_tecnica || '-'}</td>
                        <td>
                            <a href="?edit=${v.id_vehiculo}">Editar</a>
                            <a href="vehiculos_logic.php?delete=${v.id_vehiculo}" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                        </td>
                    </tr>
                `).join('');
            });
    </script>
</body>
</html>