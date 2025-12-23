<?php
// LOG: Inicio de ejecución
error_log("[MANTENCION_VIEW] Inicio de carga de página");

require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    error_log("[MANTENCION_VIEW] Acceso denegado: rol no es admin");
    die('Acceso denegado');
}

error_log("[MANTENCION_VIEW] Sesión verificada, rol=admin. Renderizando HTML.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantención - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <?php 
        error_log("[MANTENCION_VIEW] Incluyendo header.php");
        require '../includes/header.php'; 
    ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-wrench"></i> Mantención de Vehículos</h2>
        </div>

        <!-- Búsqueda de vehículo -->
        <div class="card">
            <h3><i class="fas fa-search"></i> Seleccionar Vehículo</h3>
            <input type="text" id="busquedaVehiculo" placeholder="Buscar por patente, marca, modelo o nombre..." style="width: 100%; padding: 0.5rem; margin: 0.5rem 0;">
            <div id="sugerenciasVehiculo" class="sugerencias" style="position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto;"></div>
        </div>

        <!-- Datos del vehículo -->
        <div id="panelVehiculo" class="card" style="display: none;">
            <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
            <div id="datosVehiculo" class="datos-vehiculo"></div>
            <button id="btnAgregarMantencion" class="btn-save" style="margin-top: 1.2rem; padding: 0.5rem 1.2rem;">
                <i class="fas fa-plus"></i> Agregar Registro
            </button>
        </div>

        <!-- Tabla de mantenciones -->
        <div id="panelMantenciones" class="card" style="display: none;">
            <h3><i class="fas fa-history"></i> Historial de Mantenciones</h3>
            <div class="table-container">
                <table class="data-table" id="tablaMantenciones">
                    <thead>
                        <tr>
                            <th class="sortable" data-col="fecha_mant">Fecha</th>
                            <th class="sortable" data-col="nombre_vehiculo">Nombre</th>
                            <th class="sortable" data-col="kilometraje">Kilometraje</th>
                            <th class="sortable" data-col="tipo_mant">Tipo</th>
                            <th class="sortable" data-col="taller">Taller</th>
                            <th class="sortable" data-col="costo">Costo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoMantenciones"></tbody>
                </table>
                <div class="totalizador" id="totalCostos">Total Costos: $0</div>
            </div>
        </div>
    </div>

    <!-- Submodal -->
    <div id="submodalMantencion" class="submodal">
        <div class="submodal-content">
            <span class="submodal-close" id="cerrarSubmodal">&times;</span>
            <h3>Registro de Mantenciones / Gastos</h3>
            <form id="formMantencion">
                <input type="hidden" id="id_mantencion">
                <input type="hidden" id="id_vehiculo">

                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" id="fecha_mant" required>
                </div>

                <div class="form-group">
                    <label>Tipo Mantención *</label>
                    <select id="tipo_mant" required>
                        <option value="Carga Petróleo">Carga Petróleo</option>
                        <option value="Correctiva">Correctiva</option>
                        <option value="Preventiva">Preventiva</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kilometraje</label>
                    <input type="number" id="kilometraje" min="0">
                </div>

                <div class="form-group">
                    <label>Taller</label>
                    <input type="text" id="taller">
                </div>

                <div class="form-group">
                    <label>Reparación</label>
                    <input type="text" id="reparacion">
                </div>

                <div class="form-group">
                    <label>Notas</label>
                    <textarea id="notas_mant" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label>Costo *</label>
                    <input type="number" id="costo" required min="0" step="0.01">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" id="btnCancelarSubmodal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("[DEBUG] DOM listo. Iniciando script de mantención.");

            try {
                // 1. Verificar que el input de búsqueda exista
                const inputBusqueda = document.getElementById('busquedaVehiculo');
                if (!inputBusqueda) {
                    console.error("[ERROR] No se encontró el elemento #busquedaVehiculo");
                    return;
                }
                console.log("[DEBUG] Elemento #busquedaVehiculo encontrado.");

                // 2. Agregar evento de búsqueda
                inputBusqueda.addEventListener('input', function(e) {
                    console.log("[DEBUG] Input recibido:", e.target.value);
                    const term = e.target.value.trim();
                    const contenedor = document.getElementById('sugerenciasVehiculo');
                    if (contenedor) contenedor.innerHTML = '';

                    if (term.length >= 2) {
                        console.log("[DEBUG] Llamando a API con término:", term);
                        fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`)
                            .then(res => {
                                console.log("[DEBUG] API respondió con estado:", res.status);
                                return res.json();
                            })
                            .then(data => {
                                console.log("[DEBUG] Datos recibidos de API:", data);
                                const contenedor = document.getElementById('sugerenciasVehiculo');
                                if (!contenedor) return;
                                contenedor.innerHTML = data.map(item => 
                                    `<div style="padding:8px;cursor:pointer;border-bottom:1px solid #eee;">${item.patente} - ${item.marca} ${item.modelo}</div>`
                                ).join('');
                            })
                            .catch(err => {
                                console.error("[ERROR] Fallo en llamada a API:", err);
                            });
                    }
                });

                console.log("[DEBUG] Script de mantención inicializado correctamente.");

            } catch (error) {
                console.error("[ERROR CRÍTICO EN SCRIPT]", error);
            }
        });
        </script>
</body>
</html>