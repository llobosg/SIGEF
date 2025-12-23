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
    <style>
        .submodal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .submodal-content {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }
        .submodal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.4rem;
            cursor: pointer;
            color: #999;
        }
        .form-group {
            margin: 1rem 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: normal;
            color: var(--dark);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .form-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .form-actions button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-save { background: var(--secondary); color: white; }
        .btn-cancel { background: #95a5a6; color: white; }
        .btn-save:hover { background: var(--secondary-hover); }
        .btn-cancel:hover { background: #7f8c8d; }
        .datos-vehiculo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.8rem;
            margin: 1rem 0;
        }
        .dato-item {
            font-size: 0.9rem;
        }
        .dato-item strong {
            display: block;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-wrench"></i> Mantención de Vehículos</h2>
        </div>

        <!-- BÚSQUEDA INTELIGENTE (sin sección separada) -->
        <h3><i class="fas fa-search"></i> Búsqueda inteligente</h3>
        <input type="text" id="busquedaVehiculo" 
               placeholder="Buscar por patente, marca, modelo o nombre del vehículo..."
               style="width: 100%; padding: 0.5rem; margin: 0.5rem 0 1.5rem; border: 1px solid var(--border); border-radius: 4px;">
        <div id="resultadosBusqueda" 
             style="position: absolute; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000;"></div>

        <!-- DATOS DEL VEHÍCULO (siempre visible) -->
        <div class="card">
            <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
            <div id="datosVehiculo" class="datos-vehiculo-grid">
                <div class="dato-item"><strong>Marca</strong> -</div>
                <div class="dato-item"><strong>Modelo</strong> -</div>
                <div class="dato-item"><strong>Año</strong> -</div>
                <div class="dato-item"><strong>Patente</strong> -</div>
                <div class="dato-item"><strong>Nombre Vehículo</strong> -</div>
                <div class="dato-item"><strong>Permiso Circulación</strong> -</div>
                <div class="dato-item"><strong>Revisión Técnica</strong> -</div>
                <div class="dato-item"><strong>N° SOAP</strong> -</div>
                <div class="dato-item"><strong>Seguro</strong> -</div>
                <div class="dato-item"><strong>Aseguradora</strong> -</div>
                <div class="dato-item"><strong>N° Póliza</strong> -</div>
            </div>

            <button id="btnAgregarMantencion" class="btn-save" style="margin-top: 1.2rem; padding: 0.5rem 1.2rem;">
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
            document.getElementById('id_vehiculo').value = v.id_vehiculo;
            
            // Actualizar todos los campos del vehículo
            const campos = [
                { key: 'marca', value: v.marca || '-' },
                { key: 'modelo', value: v.modelo || '-' },
                { key: 'year', value: v.year || '-' },
                { key: 'patente', value: v.patente || '-' },
                { key: 'nombre_vehiculo', value: v.nombre_vehiculo || '-' },
                { key: 'permiso_circ', value: v.permiso_circ || '-' },
                { key: 'rev_tecnica', value: v.rev_tecnica || '-' },
                { key: 'nro_soap', value: v.nro_soap || '-' },
                { key: 'seguro', value: v.seguro || '-' },
                { key: 'aseguradora', value: v.aseguradora || '-' },
                { key: 'nro_poliza', value: v.nro_poliza || '-' }
            ];
            
            const contenedor = document.getElementById('datosVehiculo');
            contenedor.innerHTML = campos.map(c => 
                `<div class="dato-item"><strong>${c.key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong> ${c.value}</div>`
            ).join('');
            
            cargarMantenciones(v.id_vehiculo);
            document.getElementById('resultadosBusqueda').style.display = 'none';
        }

        async function cargarMantenciones(id) {
            try {
                const r = await fetch(`../api/get_mantenciones.php?id_vehiculo=${id}`);
                mantenciones = await r.json();
            } catch (err) {
                mantenciones = [];
            }
            renderTabla();
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