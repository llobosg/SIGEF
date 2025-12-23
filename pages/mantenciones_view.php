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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .submodal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
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
        .datos-vehiculo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .dato-item {
            font-size: 0.9rem;
        }
        .dato-item strong {
            display: block;
            color: #2c3e50;
        }
        .table-container {
            position: relative;
            overflow-x: auto;
        }
        .totalizador {
            text-align: right;
            font-weight: bold;
            margin-top: 0.5rem;
            color: #27ae60;
        }
        .sortable { cursor: pointer; user-select: none; }
        .sortable::after { content: " ↕"; opacity: 0.5; }
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
    </style>
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
            <input type="text" id="busquedaVehiculo" placeholder="Buscar por patente, marca, modelo o nombre..." style="width: 100%; padding: 0.5rem; margin: 0.5rem 0;">
            <div id="sugerenciasVehiculo" class="sugerencias" style="position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto;"></div>
        </div>

        <!-- Datos del vehículo -->
        <div id="panelVehiculo" class="card" style="display: none;">
            <h3>Datos del Vehículo</h3>
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
        let vehiculoActual = null;
        let mantenciones = [];

        // Búsqueda de vehículo
        let busquedaTimeout;
        document.getElementById('busquedaVehiculo').addEventListener('input', function() {
            const term = this.value.trim();
            const div = document.getElementById('sugerenciasVehiculo');
            div.innerHTML = '';
            if (term.length < 2) return;

            clearTimeout(busquedaTimeout);
            busquedaTimeout = setTimeout(() => {
                fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`)
                    .then(r => r.json())
                    .then(vehiculos => {
                        div.innerHTML = vehiculos.map(v => `
                            <div onclick="seleccionarVehiculo(${JSON.stringify(v).replace(/'/g, "\\'")})" style="padding: 0.5rem; cursor: pointer; border-bottom: 1px solid #eee;">
                                ${v.patente} - ${v.marca} ${v.modelo} (${v.nombre_vehiculo})
                            </div>
                        `).join('');
                    });
            }, 300);
        });

        function seleccionarVehiculo(veh) {
            vehiculoActual = veh;
            document.getElementById('busquedaVehiculo').value = `${veh.patente} - ${veh.marca} ${veh.modelo}`;
            document.getElementById('sugerenciasVehiculo').innerHTML = '';

            const datosDiv = document.getElementById('datosVehiculo');
            datosDiv.innerHTML = `
                <div class="dato-item"><strong>Marca</strong> ${veh.marca}</div>
                <div class="dato-item"><strong>Modelo</strong> ${veh.modelo}</div>
                <div class="dato-item"><strong>Año</strong> ${veh.year}</div>
                <div class="dato-item"><strong>Patente</strong> ${veh.patente}</div>
                <div class="dato-item"><strong>Nombre</strong> ${veh.nombre_vehiculo}</div>
                <div class="dato-item"><strong>Permiso Circ.</strong> ${veh.permiso_circ || '-'}</div>
                <div class="dato-item"><strong>Rev. Técnica</strong> ${veh.rev_tecnica || '-'}</div>
                <div class="dato-item"><strong>N° SOAP</strong> ${veh.nro_soap || '-'}</div>
                <div class="dato-item"><strong>Seguro</strong> ${veh.seguro || '-'}</div>
                <div class="dato-item"><strong>Aseguradora</strong> ${veh.aseguradora || '-'}</div>
                <div class="dato-item"><strong>N° Póliza</strong> ${veh.nro_poliza || '-'}</div>
            `;

            document.getElementById('panelVehiculo').style.display = 'block';
            cargarMantenciones();
        }

        function cargarMantenciones() {
            if (!vehiculoActual) return;
            fetch(`../api/get_mantenciones.php?id_vehiculo=${vehiculoActual.id_vehiculo}`)
                .then(r => r.json())
                .then(data => {
                    mantenciones = data;
                    renderizarTabla();
                    document.getElementById('panelMantenciones').style.display = 'block';
                });
        }

        function renderizarTabla() {
            const tbody = document.getElementById('cuerpoMantenciones');
            const total = mantenciones.reduce((sum, m) => sum + (parseFloat(m.costo) || 0), 0);
            tbody.innerHTML = mantenciones.map(m => `
                <tr>
                    <td>${m.fecha_mant}</td>
                    <td>${m.nombre_vehiculo}</td>
                    <td>${m.kilometraje || '-'}</td>
                    <td>${m.tipo_mant}</td>
                    <td>${m.taller || '-'}</td>
                    <td>$${parseFloat(m.costo).toLocaleString()}</td>
                    <td>
                        <a href="#" onclick="editarMantencion(${m.id_mantencion}); return false;">
                            <i class="fas fa-pencil-alt" style="color:#27ae60;"></i>
                        </a>
                        <a href="#" onclick="eliminarMantencion(${m.id_mantencion}); return false;" style="margin-left: 8px;">
                            <i class="fas fa-trash" style="color:#e74c3c;"></i>
                        </a>
                    </td>
                </tr>
            `).join('');
            document.getElementById('totalCostos').textContent = `Total Costos: $${total.toLocaleString()}`;
        }

        // Submodal
        document.getElementById('btnAgregarMantencion').addEventListener('click', () => {
            document.getElementById('formMantencion').reset();
            document.getElementById('id_mantencion').value = '';
            document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
            document.getElementById('submodalMantencion').style.display = 'flex';
        });

        document.getElementById('cerrarSubmodal').addEventListener('click', () => {
            document.getElementById('submodalMantencion').style.display = 'none';
        });

        document.getElementById('btnCancelarSubmodal').addEventListener('click', () => {
            document.getElementById('submodalMantencion').style.display = 'none';
        });

        // Guardar
        document.getElementById('formMantencion').addEventListener('submit', (e) => {
            e.preventDefault();
            const data = {
                id_mantencion: document.getElementById('id_mantencion').value || null,
                id_vehiculo: document.getElementById('id_vehiculo').value,
                fecha_mant: document.getElementById('fecha_mant').value,
                tipo_mant: document.getElementById('tipo_mant').value,
                kilometraje: document.getElementById('kilometraje').value || null,
                taller: document.getElementById('taller').value || null,
                reparacion: document.getElementById('reparacion').value || null,
                notas_mant: document.getElementById('notas_mant').value || null,
                costo: document.getElementById('costo').value
            };

            // Validación frontend básica
            if (!data.fecha_mant || !data.tipo_mant || !data.costo) {
                Toastify({
                    text: "⚠️ Campos obligatorios incompletos",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c"
                }).showToast();
                return;
            }

            fetch('../api/mantencion_logic.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#27ae60"
                    }).showToast();
                    document.getElementById('submodalMantencion').style.display = 'none';
                    cargarMantenciones();
                } else {
                    Toastify({
                        text: "❌ " + res.message,
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#e74c3c"
                    }).showToast();
                }
            })
            .catch(err => {
                Toastify({
                    text: "⚠️ Error de conexión",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c"
                }).showToast();
            });
        });

        function editarMantencion(id) {
            const m = mantenciones.find(m => m.id_mantencion == id);
            if (!m) return;
            document.getElementById('id_mantencion').value = m.id_mantencion;
            document.getElementById('id_vehiculo').value = vehiculoActual.id_vehiculo;
            document.getElementById('fecha_mant').value = m.fecha_mant;
            document.getElementById('tipo_mant').value = m.tipo_mant;
            document.getElementById('kilometraje').value = m.kilometraje || '';
            document.getElementById('taller').value = m.taller || '';
            document.getElementById('reparacion').value = m.reparacion || '';
            document.getElementById('notas_mant').value = m.notas_mant || '';
            document.getElementById('costo').value = m.costo;
            document.getElementById('submodalMantencion').style.display = 'flex';
        }

        function eliminarMantencion(id) {
            if (!confirm('¿Eliminar este registro de mantención?')) return;
            fetch(`../api/mantencion_logic.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Toastify({
                            text: res.message,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#27ae60"
                        }).showToast();
                        cargarMantenciones();
                    } else {
                        Toastify({
                            text: "❌ " + res.message,
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#e74c3c"
                        }).showToast();
                    }
                })
                .catch(err => {
                    Toastify({
                        text: "⚠️ Error al eliminar",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#e74c3c"
                    }).showToast();
                });
        }

        // Ordenamiento
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const col = th.dataset.col;
                const order = th.classList.contains('asc') ? 'desc' : 'asc';
                document.querySelectorAll('.sortable').forEach(t => t.classList.remove('asc', 'desc'));
                th.classList.add(order);
                mantenciones.sort((a, b) => {
                    let valA = a[col] || '', valB = b[col] || '';
                    if (!isNaN(valA) && !isNaN(valB)) {
                        valA = parseFloat(valA); valB = parseFloat(valB);
                    }
                    if (order === 'asc') return valA > valB ? 1 : -1;
                    else return valA < valB ? 1 : -1;
                });
                renderizarTabla();
            });
        });
    </script>
</body>
</html>