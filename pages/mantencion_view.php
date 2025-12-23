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
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-wrench"></i> Gastos de Flota</h2>
        </div>

        <!-- BÚSQUEDA INTELIGENTE -->
        <div class="fas fa-search">Búsqueda inteligente</div>
        <input type="text" id="busquedaVehiculo" 
               placeholder="Buscar por patente, marca, modelo o nombre del vehículo..."
               style="width: 100%; padding: 0.6rem; margin: 0.5rem 0 1.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
        <div id="resultadosBusqueda" 
             style="position: absolute; background: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000;"></div>

        <!-- DATOS DEL VEHÍCULO -->
        <div class="card">
            <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
            
            <div class="datos-vehiculo-container">
                <!-- Fila 1: Labels (5) -->
                <div class="label-item">Marca</div>
                <div class="label-item">Modelo</div>
                <div class="label-item">Año</div>
                <div class="label-item">Patente</div>
                <div class="label-item">Nombre Vehículo</div>
                <div class="label-item empty-cell"></div>
                
                <!-- Fila 2: Valores (5) -->
                <div class="value-item" id="veh-marca">-</div>
                <div class="value-item" id="veh-modelo">-</div>
                <div class="value-item" id="veh-year">-</div>
                <div class="value-item" id="veh-patente">-</div>
                <div class="value-item" id="veh-nombre">-</div>
                <div class="value-item empty-cell"></div>
                
                <!-- Fila 3: Labels (6) -->
                <div class="label-item">Permiso Circulación</div>
                <div class="label-item">Revisión Técnica</div>
                <div class="label-item">N° SOAP</div>
                <div class="label-item">Seguro</div>
                <div class="label-item">Aseguradora</div>
                <div class="label-item">N° Póliza</div>
                
                <!-- Fila 4: Valores (6) -->
                <div class="value-item" id="veh-permiso">-</div>
                <div class="value-item" id="veh-revision">-</div>
                <div class="value-item" id="veh-soap">-</div>
                <div class="value-item" id="veh-seguro">-</div>
                <div class="value-item" id="veh-aseguradora">-</div>
                <div class="value-item" id="veh-poliza">-</div>
            </div>

            <div style="margin-top: 1.7rem;">
                <button id="btnAgregarMantencion" class="btn-primary">
                    <i class="fas fa-plus"></i> Agregar Gasto
                </button>
            </div>
        </div>

        <!-- HISTORIAL DE MANTENCIONES -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Historial de Gastos</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Kilometraje</th>
                            <th>Taller</th>
                            <th>Costo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoMantenciones"></tbody>
                </table>
                <div id="totalCostos" class="total-row" style="padding: 0.5rem; text-align: right; font-weight: bold;">
                    Total Costos: $0
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMODAL -->
    <div id="submodalMantencion" 
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                background-color: rgba(0, 0, 0, 0.5); z-index: 10000; 
                display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 1.4rem; border-radius: 12px; 
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); 
                    width: 90%; max-width: 800px; max-height: 90vh; 
                    overflow-y: auto; position: relative;">
            <span style="position: absolute; top: 1rem; right: 1.5rem; 
                        font-size: 1.5rem; font-weight: bold; 
                        color: #aaa; cursor: pointer;" 
                id="cerrarSubmodal">&times;</span>
            <h3 id="tituloSubmodal" style="margin-top: 0; margin-bottom: 1.5rem;">Registro de Mantenciones / Gastos</h3>
            
            <form id="formMantencion">
                <input type="hidden" id="id_mantencion">
                <input type="hidden" id="id_vehiculo">

                <!-- Fila 1 -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Fecha *</label>
                    <input type="date" id="fecha_mant" required 
                        style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Tipo Mantención *</label>
                    <select id="tipo_mant" required 
                            style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                        <option value="">Seleccionar</option>
                        <option value="carga_petroleo">Carga Petróleo</option>
                        <option value="correctiva">Correctiva</option>
                        <option value="preventiva">Preventiva</option>
                    </select>
                </div>

                <!-- Fila 2 -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Kilometraje</label>
                    <input type="number" id="kilometraje" min="0" 
                        style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Lugar/Taller</label>
                    <input type="text" id="taller" 
                        style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                </div>

                <!-- Fila 3 -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Reparación</label>
                    <input type="text" id="reparacion" 
                        style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Notas</label>
                    <textarea id="notas_mant" rows="2" 
                            style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white; min-height: 80px; resize: vertical;"></textarea>
                </div>

                <!-- Fila 4 -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <label style="flex: 0 0 140px; font-weight: 500; color: #444; text-align: right;">Costo *</label>
                    <input type="number" id="costo" required min="0" step="0.01" 
                        style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white;">
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 1.2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" id="btnCancelarSubmodal" class="btn-secondary" style="flex: 1;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast" style="display:none;">
        <i class="fas fa-info-circle"></i> 
        <span id="toast-message">Mensaje</span>
    </div>

    <script>
        // Sistema de notificaciones
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.getElementById('toast');
            const messageEl = document.getElementById('toast-message');
            const iconEl = toast.querySelector('i');
            
            messageEl.textContent = mensaje;
            toast.className = 'toast';
            
            let iconClass = 'fa-info-circle';
            switch(tipo) {
                case 'success':
                    toast.classList.add('success');
                    iconClass = 'fa-check-circle';
                    break;
                case 'error':
                    toast.classList.add('error');
                    iconClass = 'fa-times-circle';
                    break;
                case 'warning':
                    toast.classList.add('warning');
                    iconClass = 'fa-exclamation-triangle';
                    break;
                default:
                    toast.classList.add('info');
            }
            iconEl.className = `fas ${iconClass}`;
            
            toast.style.display = 'flex';
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 400);
            }, 4000);
        }

        window.exito = (msg) => mostrarNotificacion(msg, 'success');
        window.error = (msg) => mostrarNotificacion(msg, 'error');
        window.warning = (msg) => mostrarNotificacion(msg, 'warning');

        let vehiculoActual = null;
        let mantenciones = [];

        // Exponer funciones globalmente
        window.cargarMantenciones = null;
        window.renderTabla = null;
        window.seleccionarVehiculo = null;
        window.editarMantencion = null;
        window.eliminarMantencion = null;

        document.addEventListener('DOMContentLoaded', () => {
            // Configurar funciones globales
            window.cargarMantenciones = async function(id) {
                try {
                    const r = await fetch(`../api/get_mantenciones.php?id_vehiculo=${id}`);
                    mantenciones = await r.json();
                    window.renderTabla();
                } catch (err) {
                    console.error('Error al cargar mantenciones:', err);
                    mantenciones = [];
                    window.renderTabla();
                }
            };

            window.renderTabla = function() {
                const tbody = document.getElementById('cuerpoMantenciones');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                let total = 0;

                mantenciones.forEach(m => {
                    total += parseFloat(m.costo || 0);
                    tbody.innerHTML += `
                    <tr>
                        <td>${m.fecha_mant || '-'}</td>
                        <td>${m.tipo_mant || '-'}</td>
                        <td>${m.kilometraje || '-'}</td>
                        <td>${m.taller || '-'}</td>
                        <td>$${Number(m.costo || 0).toLocaleString()}</td>
                        <td>
                            <button type="button" onclick="editarMantencion(${m.id_mantencion})" class="btn-edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" onclick="eliminarMantencion(${m.id_mantencion})" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                const totalEl = document.getElementById('totalCostos');
                if (totalEl) {
                    totalEl.textContent = `Total Costos: $${total.toLocaleString()}`;
                }
            };

            window.seleccionarVehiculo = function(v) {
                vehiculoActual = v;
                
                const campos = [
                    ['veh-marca', v.marca || '-'],
                    ['veh-modelo', v.modelo || '-'],
                    ['veh-year', v.year ? String(v.year) : '-'],
                    ['veh-patente', v.patente || '-'],
                    ['veh-nombre', v.nombre_vehiculo || '-'],
                    ['veh-permiso', v.permiso_circ || '-'],
                    ['veh-revision', v.rev_tecnica || '-'],
                    ['veh-soap', v.nro_soap || '-'],
                    ['veh-seguro', v.seguro || '-'],
                    ['veh-aseguradora', v.aseguradora || '-'],
                    ['veh-poliza', v.nro_poliza || '-']
                ];
                
                campos.forEach(([id, valor]) => {
                    const elemento = document.getElementById(id);
                    if (elemento) elemento.textContent = valor;
                });
                
                window.cargarMantenciones(v.id_vehiculo);
                document.getElementById('resultadosBusqueda').style.display = 'none';
            };

            window.editarMantencion = function(id) {
                const mantencion = mantenciones.find(m => m.id_mantencion == id);
                if (mantencion) {
                    abrirSubmodal(mantencion);
                }
            };

            window.eliminarMantencion = async function(id) {
                if (!confirm('¿Eliminar este registro de mantención?')) return;
                
                try {
                    const response = await fetch(`../api/mantencion_logic.php?id=${id}`, { method: 'DELETE' });
                    const result = await response.json();
                    
                    if (result.success) {
                        exito(result.message || 'Registro eliminado');
                        if (vehiculoActual) {
                            window.cargarMantenciones(vehiculoActual.id_vehiculo);
                        }
                    } else {
                        error(result.message || 'Error al eliminar');
                    }
                } catch (err) {
                    console.error('Error al eliminar:', err);
                    error('Error de conexión al eliminar');
                }
            };

            // Configurar eventos
            configurarBusqueda();
            document.getElementById('btnAgregarMantencion').addEventListener('click', () => {
                if (!vehiculoActual) {
                    error('Seleccione un vehículo primero');
                    return;
                }
                abrirSubmodal();
            });
            document.getElementById('cerrarSubmodal').addEventListener('click', cerrarSubmodal);
            document.getElementById('btnCancelarSubmodal').addEventListener('click', cerrarSubmodal);
            document.getElementById('formMantencion').addEventListener('submit', guardarMantencion);
        });

        // --- Funciones auxiliares ---
        function configurarBusqueda() {
            const input = document.getElementById('busquedaVehiculo');
            const cont = document.getElementById('resultadosBusqueda');
            let abortController = null; // Para cancelar peticiones anteriores

            input.addEventListener('input', async () => {
                // Limpiar resultados inmediatamente
                cont.innerHTML = '';
                cont.style.display = 'none';
                
                const term = input.value.trim();
                if (term.length < 2) {
                    return;
                }

                // Cancelar petición anterior si existe
                if (abortController) {
                    abortController.abort();
                }
                abortController = new AbortController();

                try {
                    const response = await fetch(
                        `../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`,
                        { signal: abortController.signal }
                    );
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    // Verificar que no se haya cancelado
                    if (abortController.signal.aborted) {
                        return;
                    }

                    // Limpiar nuevamente (por si hay demora)
                    cont.innerHTML = '';
                    
                    if (data.length === 0) {
                        cont.innerHTML = '<div style="padding: 8px; color: #999;">No se encontraron resultados</div>';
                        cont.style.display = 'block';
                        return;
                    }

                    // Eliminar duplicados (por si acaso)
                    const uniqueData = data.filter((v, index, self) =>
                        index === self.findIndex(v2 => v2.id_vehiculo === v.id_vehiculo)
                    );

                    uniqueData.forEach(v => {
                        const div = document.createElement('div');
                        div.textContent = `${v.patente} - ${v.marca} ${v.modelo} (${v.nombre_vehiculo})`;
                        div.style.padding = '8px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        div.onclick = () => {
                            cont.style.display = 'none';
                            seleccionarVehiculo(v);
                        };
                        cont.appendChild(div);
                    });
                    
                    cont.style.display = 'block';

                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Error en búsqueda:', err);
                        error('Error al buscar vehículos');
                        cont.innerHTML = '<div style="padding: 8px; color: #e74c3c;">Error de conexión</div>';
                        cont.style.display = 'block';
                    }
                }
            });

            // Cerrar resultados al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !cont.contains(e.target)) {
                    cont.style.display = 'none';
                }
            });
        }

        function abrirSubmodal(mantencion = null) {
            const form = document.getElementById('formMantencion');
            form.reset();
            
            if (mantencion) {
                document.getElementById('id_mantencion').value = mantencion.id_mantencion;
                document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
                document.getElementById('fecha_mant').value = mantencion.fecha_mant;
                document.getElementById('tipo_mant').value = mantencion.tipo_mant;
                document.getElementById('kilometraje').value = mantencion.kilometraje || '';
                document.getElementById('taller').value = mantencion.taller || '';
                document.getElementById('reparacion').value = mantencion.reparacion || '';
                document.getElementById('notas_mant').value = mantencion.notas_mant || '';
                document.getElementById('costo').value = mantencion.costo;
                document.getElementById('tituloSubmodal').textContent = 'Editar Mantención';
            } else {
                document.getElementById('id_mantencion').value = '';
                document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
                document.getElementById('tituloSubmodal').textContent = 'Registro de Mantenciones / Gastos';
            }
            
            document.getElementById('submodalMantencion').style.display = 'flex';
        }

        function cerrarSubmodal() {
            document.getElementById('submodalMantencion').style.display = 'none';
        }

        async function guardarMantencion(e) {
            e.preventDefault();
            
            // Validar tipo_mant exactamente
            const tipoMant = document.getElementById('tipo_mant').value;
            const valoresPermitidos = ['carga_petroleo', 'correctiva', 'preventiva'];
            if (!valoresPermitidos.includes(tipoMant)) {
                error('Tipo de mantención no válido');
                return;
            }
            
            const data = {
                id_mantencion: document.getElementById('id_mantencion').value || null,
                id_vehiculo: vehiculoActual.id_vehiculo,
                fecha_mant: document.getElementById('fecha_mant').value,
                tipo_mant: tipoMant,
                kilometraje: document.getElementById('kilometraje').value || null,
                taller: document.getElementById('taller').value || null,
                reparacion: document.getElementById('reparacion').value || null,
                notas_mant: document.getElementById('notas_mant').value || null,
                costo: document.getElementById('costo').value
            };

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
                    exito(result.message || 'Guardado exitosamente');
                    cerrarSubmodal();
                    if (vehiculoActual) {
                        window.cargarMantenciones(vehiculoActual.id_vehiculo);
                    }
                } else {
                    error(result.message || 'Error al guardar');
                }
            } catch (err) {
                console.error('Error al guardar:', err);
                error('Error de conexión con el servidor');
            }
        }
    </script>

    <!-- Estilos específicos para datos del vehículo -->
    <style>
        .datos-vehiculo-container {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0.8rem;
            margin: 1rem 0;
        }
        .datos-vehiculo-container .label-item,
        .datos-vehiculo-container .value-item {
            text-align: center;
            padding: 0.3rem;
        }
        .datos-vehiculo-container .label-item {
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #0066cc;
            font-size: 0.85rem;
        }
        .datos-vehiculo-container .value-item {
            min-height: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        .datos-vehiculo-container .empty-cell {
            visibility: hidden;
        }
        @media (max-width: 768px) {
            .datos-vehiculo-container {
                grid-template-columns: 1fr;
            }
            .datos-vehiculo-container .empty-cell {
                display: none;
            }
        }
    </style>
</body>
</html>