<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Vehículo - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <!-- Font Awesome para íconos (como en CRM_ELOG) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="main-header">
        <div class="logo">SIGEF</div>
        <nav>
            <a href="vehiculos_view.php">Vehículos</a>
            <a href="personas_view.php">Personal</a>
            <a href="monto_view.php">Montos</a>
            <a href="../logout.php">Salir</a>
        </nav>
    </div>

    <div class="container">
        <div class="card">
            <!-- Ícono + Título -->
            <h2>
                <i class="fas fa-truck"></i> Ficha Vehículo
            </h2>

            <form method="POST" action="vehiculos_logic.php" id="formVehiculo">
                <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?? '' ?>">

                <!-- Fila 1: Labels -->
                <div class="form-row">
                    <label>Marca</label>
                    <label>Modelo</label>
                    <label>Año</label>
                    <label>Patente</label>
                    <label>Nombre Vehículo</label>
                </div>

                <!-- Fila 2: Campos -->
                <div class="form-row">
                    <input type="text" name="marca" value="<?= htmlspecialchars($vehiculo['marca'] ?? '') ?>" required>
                    <input type="text" name="modelo" value="<?= htmlspecialchars($vehiculo['modelo'] ?? '') ?>" required>
                    <input type="number" name="year" value="<?= $vehiculo['year'] ?? '' ?>" required>
                    <input type="text" name="patente" value="<?= htmlspecialchars($vehiculo['patente'] ?? '') ?>" required>
                    <input type="text" name="nombre_vehiculo" value="<?= htmlspecialchars($vehiculo['nombre_vehiculo'] ?? '') ?>" required>
                </div>

                <!-- Fila 3: Labels (con permiso_circ al inicio) -->
                <div class="form-row">
                    <label>Permiso Circulación</label>
                    <label>Revisión Técnica</label>
                    <label>N° SOAP</label>
                    <label>Seguro</label>
                    <label>Aseguradora</label>
                    <label>N° Póliza</label>
                </div>

                <!-- Fila 4: Campos + Botón Guardar al final -->
                <div class="form-row">
                    <input type="date" name="permiso_circ" value="<?= $vehiculo['permiso_circ'] ?? '' ?>">
                    <input type="date" name="rev_tecnica" value="<?= $vehiculo['rev_tecnica'] ?? '' ?>">
                    <input type="number" name="nro_soap" value="<?= $vehiculo['nro_soap'] ?? '' ?>">
                    <select name="seguro">
                        <option value="Si" <?= ($vehiculo['seguro'] ?? 'No') === 'Si' ? 'selected' : '' ?>>Sí</option>
                        <option value="No" <?= ($vehiculo['seguro'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option>
                    </select>
                    <input type="text" name="aseguradora" value="<?= htmlspecialchars($vehiculo['aseguradora'] ?? '') ?>">
                    <input type="text" name="nro_poliza" value="<?= htmlspecialchars($vehiculo['nro_poliza'] ?? '') ?>">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>

            <?php if (isset($alert_msg)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($alert_msg) ?></div>
            <?php endif; ?>
        </div>

        <!-- Tabla de vehículos (cargada vía JS) -->
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
        // Cargar tabla desde API
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

    <style>
        /* Estilos para el formulario en filas */
        .form-row {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .form-row label, .form-row input, .form-row select, .form-row button {
            flex: 1;
            min-width: 120px;
        }
        .form-row input[type="number"], .form-row input[type="date"] {
            min-width: 140px;
        }
        .form-row button {
            flex: 0 0 auto;
            background: #27ae60;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-row button:hover {
            background: #219653;
        }
        .btn-save {
            margin-left: auto; /* empuja el botón a la derecha */
        }
    </style>
</body>
</html>