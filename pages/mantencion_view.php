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

        <!-- Búsqueda inteligente -->
        <div style="height: 4rem;"></div>
        <!-- Contenedor padre del input y resultados -->
        <div style="margin: 1rem 0; position: relative;"> <!-- Añadido position: relative al contenedor padre -->
            <label><i class="fas fa-search"></i> Búsqueda Inteligente</label>
            <!-- El input ocupa el 100% del ancho disponible (hereda del contenedor padre) -->
            <input type="text" id="busquedaVehiculo" placeholder="Buscar por concepto..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
            <!-- El contenedor de resultados ahora se posiciona absolutamente respecto al contenedor padre -->
            <!-- Su ancho será el 100% del contenedor padre (el que tiene el margin), menos el padding del input y bordes -->
            <div id="resultados-busqueda" style="
                position: absolute;
                top: 100%; /* Colocar justo debajo del input */
                left: 0;   /* Alinear a la izquierda del contenedor padre */
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                max-height: 300px;
                overflow-y: auto;
                width: 100%; /* ✅ Ancho 100% del contenedor padre (ajustado por padding/border si es necesario) */
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                display: none;
                ">
            </div>
        </div>
        <div id="resultadosBusqueda" 
             style="position: absolute; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000;"></div>

        <!-- DATOS DEL VEHÍCULO (siempre visible) -->
        <div class="card">
            <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
            <div class="datos-vehiculo-container">
                <!-- Fila 1: Labels (5 campos + 1 vacío) -->
                <div class="label-item">Marca</div>
                <div class="label-item">Modelo</div>
                <div class="label-item">Año</div>
                <div class="label-item">Patente</div>
                <div class="label-item">Nombre Vehículo</div>
                <div class="label-item empty-cell"></div>
                
                <!-- Fila 2: Valores (5 campos + 1 vacío) -->
                <div class="value-item" id="veh-marca">-</div>
                <div class="value-item" id="veh-modelo">-</div>
                <div class="value-item" id="veh-year">-</div>
                <div class="value-item" id="veh-patente">-</div>
                <div class="value-item" id="veh-nombre">-</div>
                <div class="value-item empty-cell"></div>
                
                <!-- Fila 3: Labels (6 campos) -->
                <div class="label-item">Permiso Circulación</div>
                <div class="label-item">Revisión Técnica</div>
                <div class="label-item">N° SOAP</div>
                <div class="label-item">Seguro</div>
                <div class="label-item">Aseguradora</div>
                <div class="label-item">N° Póliza</div>
                
                <!-- Fila 4: Valores (6 campos) -->
                <div class="value-item" id="veh-permiso">-</div>
                <div class="value-item" id="veh-revision">-</div>
                <div class="value-item" id="veh-soap">-</div>
                <div class="value-item" id="veh-seguro">-</div>
                <div class="value-item" id="veh-aseguradora">-</div>
                <div class="value-item" id="veh-poliza">-</div>
            </div>

            <button id="btnAgregarMantencion" class="btn-comment" style="margin-top: 1.2rem; padding: 0.5rem 1.2rem;">
                <i class="fas fa-plus"></i> Agregar Registro
            </button>
        </div>

        <!-- HISTORIAL DE MANTENCIONES (siempre visible) -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Historial de Mantenciones</h3>
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
                <div id="totalCostos" style="text-align: right; font-weight: bold; margin-top: 0.5rem; color: #27ae60;">
                    Total Costos: $0
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMODAL -->
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
                    <button type="submit" class="btn-primary">
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
        // Notificaciones
        function notify(msg, type = 'info') {
            const colors = { info: '#3498db', error: '#e74c3c', success: '#27ae60', warning: '#f39c12' };
            Toastify({ text: msg, backgroundColor: colors[type], duration: 3000 }).showToast();
        }
        window.error = msg => notify(msg, 'error');
        window.success = msg => notify(msg, 'success');

        let vehiculoActual = null;
        let mantenciones = [];

        document.addEventListener('DOMContentLoaded', () => {
            configurarBusqueda();
            document.getElementById('btnAgregarMantencion').onclick = () => {
                if (!vehiculoActual) {
                    window.error('Seleccione un vehículo primero');
                    return;
                }
                abrirSubmodal();
            };
            document.getElementById('cerrarSubmodal').onclick = cerrarSubmodal;
            document.getElementById('btnCancelarSubmodal').onclick = cerrarSubmodal;
            document.getElementById('formMantencion').onsubmit = guardarMantencion;
        });

        function configurarBusqueda() {
            const input = document.getElementById('busquedaVehiculo');
            const cont = document.getElementById('resultadosBusqueda');

            input.addEventListener('input', async () => {
                cont.innerHTML = '';
                if (input.value.length < 2) {
                    cont.style.display = 'none';
                    return;
                }

                try {
                    const r = await fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(input.value)}`);
                    const data = await r.json();

                    data.forEach(v => {
                        const div = document.createElement('div');
                        div.textContent = `${v.patente} - ${v.marca} ${v.modelo} (${v.nombre_vehiculo})`;
                        div.style.padding = '8px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        div.onclick = () => seleccionarVehiculo(v);
                        cont.appendChild(div);
                    });
                    cont.style.display = 'block';
                } catch (err) {
                    console.error(err);
                    window.error('Error en búsqueda');
                }
            });

            // Cerrar resultados al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !cont.contains(e.target)) {
                    cont.style.display = 'none';
                }
            });
        }

        function seleccionarVehiculo(v) {
            vehiculoActual = v;
            
            // Mapeo de campos: [id_del_elemento, valor_del_vehiculo]
            const campos = [
                ['veh-marca', v.marca || '-'],
                ['veh-modelo', v.modelo || '-'],
                ['veh-year', v.year || '-'],
                ['veh-patente', v.patente || '-'],
                ['veh-nombre', v.nombre_vehiculo || '-'],
                ['veh-permiso', v.permiso_circ || '-'],
                ['veh-revision', v.rev_tecnica || '-'],
                ['veh-soap', v.nro_soap || '-'],
                ['veh-seguro', v.seguro || '-'],
                ['veh-aseguradora', v.aseguradora || '-'],
                ['veh-poliza', v.nro_poliza || '-']
            ];
            
            // Actualizar cada campo con verificación
            campos.forEach(([id, valor]) => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.textContent = valor;
                } else {
                    console.warn(`[WARNING] Elemento con ID "${id}" no encontrado`);
                }
            });
            
            cargarMantenciones(v.id_vehiculo);
            document.getElementById('resultadosBusqueda').style.display = 'none';
        }

        function renderTabla() {
            const tbody = document.getElementById('cuerpoMantenciones');
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
                        <button type="button" onclick="editarMantencion(${m.id_mantencion})" style="background: none; border: none; color: #27ae60; cursor: pointer; margin-right: 8px;">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" onclick="eliminarMantencion(${m.id_mantencion})" style="background: none; border: none; color: #e74c3c; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.getElementById('totalCostos').textContent = `Total Costos: $${total.toLocaleString()}`;
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
            
            const data = {
                id_mantencion: document.getElementById('id_mantencion').value || null,
                id_vehiculo: vehiculoActual.id_vehiculo,
                fecha_mant: document.getElementById('fecha_mant').value,
                tipo_mant: document.getElementById('tipo_mant').value,
                kilometraje: document.getElementById('kilometraje').value || null,
                taller: document.getElementById('taller').value || null,
                reparacion: document.getElementById('reparacion').value || null,
                notas_mant: document.getElementById('notas_mant').value || null,
                costo: document.getElementById('costo').value
            };

            if (!data.fecha_mant || !data.tipo_mant || !data.costo) {
                window.error('Campos obligatorios incompletos');
                return;
            }

            try {
                const r = await fetch('../api/mantencion_logic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const res = await r.json();
                
                if (res.success) {
                    success(res.message || 'Guardado exitosamente');
                    cerrarSubmodal();
                    cargarMantenciones(vehiculoActual.id_vehiculo);
                } else {
                    window.error(res.message || 'Error al guardar');
                }
            } catch (err) {
                window.error('Error de conexión');
            }
        }

        function editarMantencion(id) {
            const mantencion = mantenciones.find(m => m.id_mantencion == id);
            if (mantencion) {
                abrirSubmodal(mantencion);
            }
        }

        async function eliminarMantencion(id) {
            if (!confirm('¿Eliminar este registro de mantención?')) return;
            
            try {
                await fetch(`../api/mantencion_logic.php?id=${id}`, { method: 'DELETE' });
                success('Registro eliminado');
                cargarMantenciones(vehiculoActual.id_vehiculo);
            } catch (err) {
                window.error('Error al eliminar');
            }
        }
    </script>
</body>
</html>