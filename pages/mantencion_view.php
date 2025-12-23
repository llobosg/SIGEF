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
    <title>Mantención - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-wrench"></i> Mantención de Vehículos</h2>
        </div>

        <!-- Búsqueda de vehículo -->
        <div class="card">
            <h3><i class="fas fa-search"></i> Seleccionar Vehículo</h3>
            <input type="text" id="busquedaVehiculo" 
                   placeholder="Buscar por patente, marca, modelo o nombre..." 
                   autocomplete="off"
                   style="width: 100%; padding: 0.5rem; margin: 0.5rem 0; border: 1px solid var(--border); border-radius: 4px;">
            <div id="sugerenciasVehiculo" class="sugerencias" 
                 style="position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto;"></div>
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
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th>Kilometraje</th>
                            <th>Tipo</th>
                            <th>Taller</th>
                            <th>Costo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoMantenciones"></tbody>
                </table>
                <div class="totalizador" id="totalCostos">Total Costos: $0</div>
            </div>
        </div>
    </div>

    <!-- Submodal (placeholder) -->
    <div id="submodalMantencion" class="submodal" style="display:none;">
        <div class="submodal-content">
            <span class="submodal-close">&times;</span>
            <h3>Registro de Mantenciones</h3>
            <p>Submodal en desarrollo.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("[DEBUG] DOM listo. Iniciando script de mantención.");

            // Delegación de eventos para búsqueda
            document.addEventListener('input', function(e) {
                if (e.target && e.target.id === 'busquedaVehiculo') {
                    console.log("[DEBUG] Input detectado:", e.target.value);
                    const term = e.target.value.trim();
                    const contenedor = document.getElementById('sugerenciasVehiculo');
                    if (contenedor) contenedor.innerHTML = '';

                    if (term.length >= 2) {
                        console.log("[DEBUG] Llamando a API con término:", term);
                        fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`)
                            .then(res => {
                                console.log("[DEBUG] API respondió con estado:", res.status);
                                if (!res.ok) throw new Error('API error');
                                return res.json();
                            })
                            .then(data => {
                                console.log("[DEBUG] Datos recibidos de API:", data);
                                const contenedor = document.getElementById('sugerenciasVehiculo');
                                if (!contenedor) return;

                                if (data.length === 0) {
                                    contenedor.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                                    return;
                                }

                                contenedor.innerHTML = data.map(item => {
                                    // Escapar comillas simples para onclick
                                    const nombreEscapado = (item.nombre_vehiculo || '').replace(/'/g, "\\'");
                                    const marcaEscapada = (item.marca || '').replace(/'/g, "\\'");
                                    const modeloEscapado = (item.modelo || '').replace(/'/g, "\\'");
                                    return `
                                        <div onclick="seleccionarVehiculoSimple(${item.id_vehiculo}, '${item.patente}', '${marcaEscapada}', '${modeloEscapado}', '${nombreEscapado}')"
                                             style="padding:8px;cursor:pointer;border-bottom:1px solid #eee;">
                                            ${item.patente} - ${item.marca} ${item.modelo} (${item.nombre_vehiculo || ''})
                                        </div>
                                    `;
                                }).join('');
                            })
                            .catch(err => {
                                console.error("[ERROR] Fallo en llamada a API:", err);
                                const contenedor = document.getElementById('sugerenciasVehiculo');
                                if (contenedor) {
                                    contenedor.innerHTML = '<div style="padding:8px;color:#e74c3c;">Error al cargar vehículos</div>';
                                }
                            });
                    }
                }
            });

            console.log("[DEBUG] Script de mantención inicializado correctamente.");
        });

        // Función global para selección
        window.seleccionarVehiculoSimple = function(id, patente, marca, modelo, nombre) {
            console.log("[DEBUG] Vehículo seleccionado:", {id, patente, marca, modelo, nombre});
            document.getElementById('busquedaVehiculo').value = `${patente} - ${marca} ${modelo}`;
            document.getElementById('sugerenciasVehiculo').innerHTML = '';

            // Mostrar datos básicos
            const datosDiv = document.getElementById('datosVehiculo');
            if (datosDiv) {
                datosDiv.innerHTML = `
                    <div class="dato-item"><strong>Patente</strong> ${patente}</div>
                    <div class="dato-item"><strong>Marca</strong> ${marca}</div>
                    <div class="dato-item"><strong>Modelo</strong> ${modelo}</div>
                    <div class="dato-item"><strong>Nombre</strong> ${nombre || '-'}</div>
                `;
            }

            document.getElementById('panelVehiculo').style.display = 'block';
            console.log("[DEBUG] Panel de vehículo mostrado.");
        };
    </script>
</body>
</html>