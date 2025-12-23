<?php
    require '../session_check.php';
    require '../includes/header.php';
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
    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-wrench"></i> Mantención de Vehículos..</h2>
        </div>

        <!-- Búsqueda de vehículo -->
        <div class="card">
            <h3><i class="fas fa-search"></i> Seleccionar Vehículo</h3>
            <input type="text" id="busquedaVehiculo" 
                   placeholder="Buscar por patente, marca, modelo o nombre..." 
                   autocomplete="off"
                   style="width: 100%; padding: 0.5rem; margin: 0.5rem 0; border: 1px solid var(--border); border-radius: 4px;">
            <div id="resultadosBusqueda" 
                 style="position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto; display: none;"></div>
        </div>

        <!-- Datos del vehículo -->
        <div id="panelVehiculo" class="card" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
                <button type="button" onclick="cerrarPanelVehiculo()" 
                        style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d;">
                    &times;
                </button>
            </div>
            <div id="datosVehiculo" class="datos-vehiculo" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;"></div>
            
            <button id="btnAgregarMantencion" class="btn-save" style="margin-top: 1.2rem; padding: 0.5rem 1.2rem;">
                <i class="fas fa-plus"></i> Agregar Registro
            </button>
        </div>

        <!-- Tabla de mantenciones -->
        <div id="panelMantenciones" class="card" style="display: none;">
            <h3><i class="fas fa-history"></i> Historial de Mantenciones</h3>
            <div class="table-container">
                <table class="data-table">
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
                <div class="totalizador" id="totalCostos" style="text-align: right; font-weight: bold; margin-top: 0.5rem; color: #27ae60;">
                    Total Costos: $0
                </div>
            </div>
        </div>
    </div>

    <!-- Submodal -->
    <div id="submodalMantencion" class="submodal">
        <div class="submodal-content">
            <span class="submodal-close" id="cerrarSubmodal">&times;</span>
            <h3 id="tituloSubmodal">Registro de Mantenciones / Gastos</h3>
            
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
        // ========== FUNCIONES GLOBALES DE NOTIFICACIÓN ==========
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const bg = tipo === 'exito' ? '#27ae60' : 
                       tipo === 'error' ? '#e74c3c' : 
                       tipo === 'warning' ? '#f39c12' : '#3498db';
            Toastify({
                text: mensaje,
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: bg
            }).showToast();
        }

        window.exito = (msg) => mostrarNotificacion(msg, 'exito');
        window.error = (msg) => mostrarNotificacion(msg, 'error');
        window.warning = (msg) => mostrarNotificacion(msg, 'warning');

        // ========== VARIABLES DE ESTADO ==========
        let vehiculoActual = null;
        let mantenciones = [];

        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[SIGEF] Iniciando módulo de Mantención');

            // Configurar elementos
            document.getElementById('btnAgregarMantencion').addEventListener('click', () => abrirSubmodal());
            document.getElementById('cerrarSubmodal').addEventListener('click', cerrarSubmodal);
            document.getElementById('btnCancelarSubmodal').addEventListener('click', cerrarSubmodal);
            document.getElementById('formMantencion').addEventListener('submit', guardarMantencion);

            // Configurar búsqueda
            configurarBusquedaInteligente();

            console.log('[SIGEF] Módulo inicializado');
        });

        // ========== BÚSQUEDA INTELIGENTE ==========
        function configurarBusquedaInteligente() {
            const input = document.getElementById('busquedaVehiculo');
            const resultadosDiv = document.getElementById('resultadosBusqueda');
            let timeoutBusqueda = null;

            input.addEventListener('input', function() {
                const termino = this.value.trim();
                resultadosDiv.style.display = 'none';
                resultadosDiv.innerHTML = '';

                if (timeoutBusqueda) clearTimeout(timeoutBusqueda);
                if (termino.length < 2) return;

                timeoutBusqueda = setTimeout(() => {
                    buscarVehiculos(termino, resultadosDiv);
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !resultadosDiv.contains(e.target)) {
                    resultadosDiv.style.display = 'none';
                }
            });
        }

        async function buscarVehiculos(termino, contenedor) {
            try {
                const response = await fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(termino)}`);
                if (!response.ok) throw new Error(`API error ${response.status}`);
                const vehiculos = await response.json();

                if (vehiculos.length === 0) {
                    contenedor.innerHTML = '<div style="padding: 8px; color: #999;">Sin resultados</div>';
                    contenedor.style.display = 'block';
                    return;
                }

                contenedor.innerHTML = vehiculos.map(v => {
                    const display = `${v.patente} - ${v.marca} ${v.modelo} (${v.nombre_vehiculo || ''})`;
                    return `
                        <div onclick="seleccionarVehiculo(${JSON.stringify(v).replace(/'/g, "\\'")})" 
                             style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;">
                            ${display}
                        </div>
                    `;
                }).join('');
                contenedor.style.display = 'block';

            } catch (error) {
                console.error('[SIGEF] Error en búsqueda:', error);
                error('Error al buscar vehículos');
                contenedor.innerHTML = '<div style="padding: 8px; color: #e74c3c;">Error de conexión</div>';
                contenedor.style.display = 'block';
            }
        }

        // ========== SELECCIÓN DE VEHÍCULO ==========
        function seleccionarVehiculo(vehiculo) {
            try {
                if (!vehiculo || !vehiculo.id_vehiculo) {
                    throw new Error('Vehículo inválido');
                }

                vehiculoActual = vehiculo;
                document.getElementById('busquedaVehiculo').value = `${vehiculo.patente} - ${vehiculo.marca} ${vehiculo.modelo}`;
                document.getElementById('resultadosBusqueda').style.display = 'none';

                mostrarDatosVehiculo(vehiculo);
                document.getElementById('panelVehiculo').style.display = 'block';
                cargarMantenciones(vehiculo.id_vehiculo);

            } catch (error) {
                console.error('[SIGEF] Error al seleccionar vehículo:', error);
                error('Error al procesar vehículo');
            }
        }

        function mostrarDatosVehiculo(veh) {
            const div = document.getElementById('datosVehiculo');
            if (!div) return;

            div.innerHTML = `
                <div><strong>Marca:</strong> ${veh.marca || '-'}</div>
                <div><strong>Modelo:</strong> ${veh.modelo || '-'}</div>
                <div><strong>Año:</strong> ${veh.year || '-'}</div>
                <div><strong>Patente:</strong> ${veh.patente || '-'}</div>
                <div><strong>Nombre:</strong> ${veh.nombre_vehiculo || '-'}</div>
                <div><strong>Permiso Circ.:</strong> ${veh.permiso_circ || '-'}</div>
                <div><strong>Rev. Técnica:</strong> ${veh.rev_tecnica || '-'}</div>
                <div><strong>N° SOAP:</strong> ${veh.nro_soap || '-'}</div>
                <div><strong>Seguro:</strong> ${veh.seguro || '-'}</div>
                <div><strong>Aseguradora:</strong> ${veh.aseguradora || '-'}</div>
                <div><strong>N° Póliza:</strong> ${veh.nro_poliza || '-'}</div>
            `;
        }

        function cerrarPanelVehiculo() {
            document.getElementById('panelVehiculo').style.display = 'none';
            document.getElementById('panelMantenciones').style.display = 'none';
            document.getElementById('busquedaVehiculo').value = '';
            vehiculoActual = null;
            mantenciones = [];
        }

        // ========== CARGA DE MANTENCIONES ==========
        async function cargarMantenciones(idVehiculo) {
            try {
                const response = await fetch(`../api/get_mantenciones.php?id_vehiculo=${idVehiculo}`);
                if (!response.ok) throw new Error(`API error ${response.status}`);
                mantenciones = await response.json();
                renderizarTablaMantenciones();
                document.getElementById('panelMantenciones').style.display = 'block';
            } catch (error) {
                console.error('[SIGEF] Error al cargar mantenciones:', error);
                warning('No se pudieron cargar las mantenciones');
                mantenciones = [];
                renderizarTablaMantenciones();
                document.getElementById('panelMantenciones').style.display = 'block';
            }
        }

        function renderizarTablaMantenciones() {
            const tbody = document.getElementById('cuerpoMantenciones');
            const total = mantenciones.reduce((sum, m) => sum + (parseFloat(m.costo) || 0), 0);
            
            tbody.innerHTML = mantenciones.map(m => `
                <tr>
                    <td>${m.fecha_mant || '-'}</td>
                    <td>${m.nombre_vehiculo || '-'}</td>
                    <td>${m.kilometraje || '-'}</td>
                    <td>${m.tipo_mant || '-'}</td>
                    <td>${m.taller || '-'}</td>
                    <td>$${parseFloat(m.costo || 0).toLocaleString()}</td>
                    <td>
                        <button type="button" onclick="editarMantencion(${m.id_mantencion})" style="background: none; border: none; color: #27ae60; cursor: pointer;">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" onclick="eliminarMantencion(${m.id_mantencion})" style="background: none; border: none; color: #e74c3c; cursor: pointer; margin-left: 8px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('totalCostos').textContent = `Total Costos: $${total.toLocaleString()}`;
        }

        // ========== SUBMODAL ==========
        function abrirSubmodal(mantencion = null) {
            const titulo = document.getElementById('tituloSubmodal');
            const form = document.getElementById('formMantencion');
            form.reset();
            
            if (mantencion) {
                // Modo edición
                document.getElementById('id_mantencion').value = mantencion.id_mantencion;
                document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
                document.getElementById('fecha_mant').value = mantencion.fecha_mant;
                document.getElementById('tipo_mant').value = mantencion.tipo_mant;
                document.getElementById('kilometraje').value = mantencion.kilometraje || '';
                document.getElementById('taller').value = mantencion.taller || '';
                document.getElementById('reparacion').value = mantencion.reparacion || '';
                document.getElementById('notas_mant').value = mantencion.notas_mant || '';
                document.getElementById('costo').value = mantencion.costo;
                titulo.textContent = 'Editar Mantención';
            } else {
                // Modo creación
                document.getElementById('id_mantencion').value = '';
                document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
                titulo.textContent = 'Registro de Mantenciones / Gastos';
            }
            
            document.getElementById('submodalMantencion').style.display = 'flex';
        }

        function cerrarSubmodal() {
            document.getElementById('submodalMantencion').style.display = 'none';
        }

        // ========== GUARDAR MANTENCIÓN ==========
        async function guardarMantencion(e) {
            e.preventDefault();
            
            const id_mantencion = document.getElementById('id_mantencion').value;
            const data = {
                id_mantencion: id_mantencion || null,
                id_vehiculo: document.getElementById('id_vehiculo').value,
                fecha_mant: document.getElementById('fecha_mant').value,
                tipo_mant: document.getElementById('tipo_mant').value,
                kilometraje: document.getElementById('kilometraje').value || null,
                taller: document.getElementById('taller').value || null,
                reparacion: document.getElementById('reparacion').value || null,
                notas_mant: document.getElementById('notas_mant').value || null,
                costo: document.getElementById('costo').value
            };

            // Validación frontend
            if (!data.fecha_mant || !data.tipo_mant || !data.costo) {
                error('Campos obligatorios incompletos');
                return;
            }

            try {
                const response = await fetch('../api/mantencion_logic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    exito(result.message);
                    cerrarSubmodal();
                    cargarMantenciones(vehiculoActual.id_vehiculo);
                } else {
                    error(result.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('[SIGEF] Error al guardar mantención:', error);
                error('Error de conexión al guardar');
            }
        }

        // ========== EDITAR Y ELIMINAR ==========
        function editarMantencion(id) {
            const mantencion = mantenciones.find(m => m.id_mantencion == id);
            if (mantencion) {
                abrirSubmodal(mantencion);
            }
        }

        async function eliminarMantencion(id) {
            if (!confirm('¿Eliminar este registro de mantención?')) return;
            
            try {
                const response = await fetch(`../api/mantencion_logic.php?id=${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();
                
                if (result.success) {
                    exito(result.message);
                    cargarMantenciones(vehiculoActual.id_vehiculo);
                } else {
                    error(result.message || 'Error al eliminar');
                }
            } catch (error) {
                console.error('[SIGEF] Error al eliminar mantención:', error);
                error('Error de conexión al eliminar');
            }
        }
    </script>
</body>
</html>