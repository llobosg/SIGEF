<?php require '../auth.php'; if ($_SESSION['rol'] !== 'admin') die('Acceso denegado'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Ficha Camión - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="main-header">
        <div class="logo">SIGEF</div>
        <nav>
            <a href="pages/vehiculos_view.php">Vehículos</a>
            <a href="pages/personas_view.php">Personal</a>
            <a href="../logout.php">Salir</a>
        </nav>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= isset($_GET['edit']) ? 'Editar Vehículo' : 'Nuevo Vehículo' ?></h2>
            <?php if (isset($alert_msg)): ?>
                <div class="alert alert-warning"><?= $alert_msg ?></div>
            <?php endif; ?>

            <form method="POST" action="vehiculos_logic.php">
                <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?? '' ?>">
                <input type="text" name="marca" placeholder="Marca" value="<?= $vehiculo['marca'] ?? '' ?>" required>
                <input type="text" name="modelo" placeholder="Modelo" value="<?= $vehiculo['modelo'] ?? '' ?>" required>
                <input type="number" name="year" placeholder="Año" value="<?= $vehiculo['year'] ?? '' ?>" required>
                <input type="text" name="patente" placeholder="Patente" value="<?= $vehiculo['patente'] ?? '' ?>" required>
                <input type="text" name="nombre_vehiculo" placeholder="Nombre Vehículo" value="<?= $vehiculo['nombre_vehiculo'] ?? '' ?>" required>
                <input type="date" name="rev_tecnica" value="<?= $vehiculo['rev_tecnica'] ?? '' ?>">
                <input type="date" name="permiso_circ" value="<?= $vehiculo['permiso_circ'] ?? '' ?>">
                <input type="number" name="nro_soap" placeholder="N° SOAP" value="<?= $vehiculo['nro_soap'] ?? '' ?>">
                <select name="seguro">
                    <option value="Si" <?= ($vehiculo['seguro'] ?? 'No') === 'Si' ? 'selected' : '' ?>>Sí</option>
                    <option value="No" <?= ($vehiculo['seguro'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option>
                </select>
                <input type="text" name="aseguradora" placeholder="Aseguradora" value="<?= $vehiculo['aseguradora'] ?? '' ?>">
                <input type="text" name="nro_poliza" placeholder="N° Póliza" value="<?= $vehiculo['nro_poliza'] ?? '' ?>">
                <button type="submit"><?= isset($_GET['edit']) ? 'Actualizar' : 'Guardar' ?></button>
                <?php if (isset($_GET['edit'])): ?>
                    <a href="vehiculos_view.php">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>Lista de Vehículos</h3>
            <table class="data-table" id="tablaVehiculos">
                <thead>
                    <tr><th>Patente</th><th>Marca</th><th>Modelo</th><th>Rev. Técnica</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <!-- Cargado vía JS desde api/get_vehiculo.php -->
                </tbody>
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