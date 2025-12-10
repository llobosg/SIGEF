<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$monto = null;
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM MONTO WHERE id_monto = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $monto = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Montos - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-money-bill-wave"></i> Configuración de Montos</h2>
        </div>

        <div class="card">
            <form method="POST" action="monto_logic.php">
                <input type="hidden" name="id_monto" value="<?= $monto['id_monto'] ?? '' ?>">

                <div class="form-grid-4">
                    <label>Tipo Monto</label>
                    <label>Tipo Personal</label>
                    <label>Monto ($)</label>
                    <label></label>

                    <select name="tipo_monto" required>
                        <option value="">Seleccionar</option>
                        <option value="Guía" <?= ($monto['tipo_monto'] ?? '') === 'Guía' ? 'selected' : '' ?>>Guía</option>
                        <option value="Distancia" <?= ($monto['tipo_monto'] ?? '') === 'Distancia' ? 'selected' : '' ?>>Distancia</option>
                        <option value="día" <?= ($monto['tipo_monto'] ?? '') === 'día' ? 'selected' : '' ?>>Día</option>
                    </select>
                    <select name="tipo_personal" required>
                        <option value="">Seleccionar</option>
                        <option value="Chofer" <?= ($monto['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                        <option value="Peoneta" <?= ($monto['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                    </select>
                    <input type="number" name="monto" value="<?= $monto['monto'] ?? '' ?>" required min="0">
                    <div></div>
                </div>

                <!-- Campo de búsqueda de vehículo -->
                <div class="form-row" style="margin-top: 1.2rem;">
                    <label>Vehículo</label>
                    <div style="position: relative; width: 100%;">
                        <input type="text" id="busquedaVehiculo" 
                               placeholder="Buscar por patente, marca o modelo..." 
                               autocomplete="off"
                               value="<?= htmlspecialchars($monto['nombre_vehiculo'] ?? '') ?>"
                               style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                        <input type="hidden" name="id_vehiculo" id="id_vehiculo" value="<?= $monto['id_vehiculo'] ?? '' ?>">
                        <input type="hidden" name="nombre_vehiculo" id="nombre_vehiculo_display" value="<?= htmlspecialchars($monto['nombre_vehiculo'] ?? '') ?>">
                        <div id="sugerencias" class="sugerencias"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de montos -->
        <div class="card">
            <h3>Montos Configurados</h3>
            <table class="data-table" id="tablaMontos">
                <thead>
                    <tr>
                        <th>Vehículo</th><th>Tipo Monto</th><th>Tipo Personal</th><th>Monto</th><th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Búsqueda de vehículos
        let busquedaTimeout;
        document.getElementById('busquedaVehiculo').addEventListener('input', function() {
            const term = this.value.trim();
            const sugerencias = document.getElementById('sugerencias');
            sugerencias.innerHTML = '';

            clearTimeout(busquedaTimeout);
            if (term.length < 2) return;

            busquedaTimeout = setTimeout(() => {
                fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`)
                    .then(r => r.json())
                    .then(vehiculos => {
                        sugerencias.innerHTML = vehiculos.map(v => `
                            <div onclick="seleccionarVehiculo(${v.id_vehiculo}, '${v.nombre_vehiculo.replace(/'/g, "\\'")}')">
                                ${v.patente} - ${v.marca} ${v.modelo} (${v.nombre_vehiculo})
                            </div>
                        `).join('');
                    });
            }, 300);
        });

        function seleccionarVehiculo(id, nombre) {
            document.getElementById('id_vehiculo').value = id;
            document.getElementById('nombre_vehiculo_display').value = nombre;
            document.getElementById('busquedaVehiculo').value = nombre;
            document.getElementById('sugerencias').innerHTML = '';
        }

        // Cargar tabla
        fetch('../api/get_monto.php')
            .then(r => r.json())
            .then(data => {
                const tbody = document.querySelector('#tablaMontos tbody');
                tbody.innerHTML = data.map(m => `
                    <tr>
                        <td>${m.nombre_vehiculo || '-'}</td>
                        <td>${m.tipo_monto}</td>
                        <td>${m.tipo_personal}</td>
                        <td>$${m.monto}</td>
                        <td>
                            <a href="?edit=${m.id_monto}">Editar</a>
                            <a href="monto_logic.php?delete=${m.id_monto}" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                        </td>
                    </tr>
                `).join('');
            });

        // Notificaciones
        (function() {
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (!msg) return;
            let text = "", bg = "#27ae60";
            switch(msg) {
                case 'success': text = "✅ Monto guardado"; break;
                case 'delete_success': text = "🗑️ Monto eliminado"; break;
                case 'error': text = "⚠️ Error al guardar"; bg = "#e74c3c"; break;
                case 'error_vehiculo': text = "⚠️ Debe seleccionar un vehículo"; bg = "#e74c3c"; break;
                default: return;
            }
            Toastify({text,duration:3000,gravity:"top",position:"right",backgroundColor:bg}).showToast();
            history.replaceState({}, '', location.pathname);
        })();
    </script>

    <style>
        .page-title h2 {
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 1.2rem;
        }

        .form-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }

        .form-grid-4 label {
            text-align: center;
            font-weight: normal;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .form-grid-4 input,
        .form-grid-4 select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .form-row {
            display: flex;
            gap: 0.8rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .form-row label {
            font-weight: normal;
            color: var(--dark);
            min-width: 120px;
        }

        .sugerencias {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            margin-top: -1px;
        }
        .sugerencias div {
            padding: 0.5rem;
            cursor: pointer;
        }
        .sugerencias div:hover {
            background: #f0f0f0;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .form-actions .btn-save {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-actions .btn-save:hover {
            background: var(--secondary-hover);
        }

        @media (max-width: 768px) {
            .form-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            .form-grid-4 label {
                grid-column: span 2;
                text-align: left;
            }
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }
            .form-row label {
                margin-bottom: 0.3rem;
            }
        }
    </style>
</body>
</html>