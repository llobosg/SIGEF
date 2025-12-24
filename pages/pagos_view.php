<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$pago = null;
$esEdicion = false;
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM PAGOS WHERE id_pago = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $pago = $stmt->fetch();
    $esEdicion = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos - SIGEF</title>
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
        /* Layout tradicional para Ficha de Pagos */
        .ficha-pagos-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin: 1rem 0;
        }
        .ficha-pagos-grid .label-item,
        .ficha-pagos-grid .field-item {
            text-align: center;
            padding: 0.3rem;
        }
        .ficha-pagos-grid .label-item {
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #0066cc;
            font-size: 0.85rem;
        }
        .ficha-pagos-grid .field-item {
            min-height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ficha-pagos-grid .field-item input {
            width: 95%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .ficha-pagos-grid .field-item input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .ficha-pagos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-money-check-alt"></i> Pagos</h2>
        </div>

        <!-- Búsqueda Personal -->
        <div class="card">
            <h3><i class="fas fa-user-search"></i> Búsqueda de Personal</h3>
            <input type="text" id="busquedaPersonal" 
                   placeholder="Buscar por nombre del personal..."
                   style="width: 100%; padding: 0.6rem; margin: 0.5rem 0; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
            <div id="resultadosBusquedaPersonal" 
                 style="position: absolute; background: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000;"></div>
        </div>

        <!-- Ficha Pagos -->
        <div class="card">
            <h3><i class="fas fa-file-invoice-dollar"></i> Ficha de Pagos</h3>
            <form id="formPagos">
                <input type="hidden" id="id_pago" value="<?= $pago['id_pago'] ?? '' ?>">
                <input type="hidden" id="id_personal" value="<?= $pago['id_personal'] ?? '' ?>">
                <input type="hidden" id="id_vehiculo" value="<?= $pago['id_vehiculo'] ?? '' ?>">

                <div class="ficha-pagos-grid">
                    <!-- Fila 1: Labels -->
                    <div class="label-item">Nombre</div>
                    <div class="label-item">Tipo Personal</div>
                    <div class="label-item">Fecha</div>
                    <div class="label-item">Total Monto</div>
                    
                    <!-- Fila 2: Campos -->
                    <div class="field-item">
                        <input type="text" id="nombre_display" 
                               value="<?= htmlspecialchars($pago['nombre'] ?? '') ?>" 
                               readonly required>
                    </div>
                    <div class="field-item">
                        <input type="text" id="tipo_personal_display" 
                               value="<?= htmlspecialchars($pago['tipo_personal'] ?? '') ?>" 
                               readonly required>
                    </div>
                    <div class="field-item">
                        <input type="date" id="fecha" 
                               value="<?= $pago['fecha'] ?? date('Y-m-d') ?>" 
                               required>
                    </div>
                    <div class="field-item">
                        <input type="number" id="total_monto" 
                               value="<?= $pago['total_monto'] ?? '0' ?>" 
                               readonly step="0.01">
                    </div>
                </div>

                <button type="button" id="btnAgregarPago" class="btn-primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-plus"></i> Agregar Pago
                </button>
            </form>
        </div>

        <!-- Submodal Pagos -->
        <div id="submodalPagos" class="submodal">
            <div class="submodal-content">
                <span class="submodal-close" id="cerrarSubmodal">&times;</span>
                <h3><i class="fas fa-plus-circle"></i> Registro de Pago</h3>
                
                <!-- Búsqueda Facturación -->
                <div class="form-group">
                    <label>Búsqueda de Facturación *</label>
                    <input type="text" id="busquedaFacturacion" 
                        placeholder="Buscar por N° Factura o Cliente..."
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    <div id="resultadosFacturacion" 
                        style="position: absolute; background: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000; margin-top: 0.2rem;"></div>
                </div>

                <!-- Búsqueda Vehículo -->
                <div class="form-group">
                    <label>Búsqueda de Vehículo *</label>
                    <input type="text" id="busquedaVehiculo" 
                        placeholder="Buscar por nombre del vehículo..."
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    <div id="resultadosVehiculo" 
                        style="position: absolute; background: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; display: none; z-index: 1000; margin-top: 0.2rem;"></div>
                </div>

                <!-- Campos del pago -->
                <input type="hidden" id="nro_factura_hidden">
                <input type="hidden" id="fecha_factura_hidden">
                <input type="hidden" id="saldo_hidden">
                <input type="hidden" id="monto_f_hidden">
                <input type="hidden" id="monto_p_hidden">
                <input type="hidden" id="id_vehiculo_pago">
                <input type="hidden" id="cliente_hidden">

                <!-- Layout: tipo_monto | monto_p -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;">
                    <div class="form-group">
                        <label>Tipo Monto *</label>
                        <select id="tipo_monto_pago" required>
                            <option value="">Seleccionar</option>
                            <option value="día">Día</option>
                            <option value="Guía">Guía</option>
                            <option value="Distancia">Distancia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Monto P *</label>
                        <input type="number" id="monto_p_display" readonly step="0.01">
                    </div>
                </div>

                <!-- Layout: cantidad | monto -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Cantidad *</label>
                        <input type="number" id="qty_pago_tipo_monto" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Monto *</label>
                        <input type="number" id="monto_pago" readonly step="0.01">
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" id="btnGuardarPago" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Pago
                    </button>
                    <button type="button" id="btnCancelarSubmodal" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Pagos -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Registro de Pagos</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Tipo Monto</th>
                            <th>Cantidad</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPagos"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast" style="display:none;">
        <i class="fas fa-info-circle"></i> 
        <span id="toast-message">Mensaje</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Notificaciones
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.getElementById('toast');
            const messageEl = document.getElementById('toast-message');
            const iconEl = toast.querySelector('i');
            
            messageEl.textContent = mensaje;
            toast.className = 'toast';
            
            let iconClass = 'fa-info-circle';
            switch(tipo) {
                case 'success': iconClass = 'fa-check-circle'; toast.classList.add('success'); break;
                case 'error': iconClass = 'fa-times-circle'; toast.classList.add('error'); break;
                case 'warning': iconClass = 'fa-exclamation-triangle'; toast.classList.add('warning'); break;
                default: toast.classList.add('info');
            }
            iconEl.className = `fas ${iconClass}`;
            
            toast.style.display = 'flex';
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.style.display = 'none', 400);
            }, 4000);
        }
        window.exito = (msg) => mostrarNotificacion(msg, 'success');
        window.error = (msg) => mostrarNotificacion(msg, 'error');

        let personalSeleccionado = null;
        let facturacionSeleccionada = null;
        let vehiculoSeleccionado = null;

        // Búsqueda de Personal
        document.getElementById('busquedaPersonal').addEventListener('input', async function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosBusquedaPersonal');
            div.innerHTML = '';
            div.style.display = 'none';

            if (term.length < 2) return;

            try {
                const res = await fetch(`../api/get_personal_busqueda.php?q=${encodeURIComponent(term)}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    div.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                } else {
                    data.forEach(p => {
                        const el = document.createElement('div');
                        el.style.padding = '8px';
                        el.style.cursor = 'pointer';
                        el.style.borderBottom = '1px solid #eee';
                        el.textContent = `${p.nombre} (${p.tipo_personal})`;
                        el.addEventListener('click', () => {
                            personalSeleccionado = p;
                            document.getElementById('id_personal').value = p.id_personal;
                            document.getElementById('nombre_display').value = p.nombre;
                            document.getElementById('tipo_personal_display').value = p.tipo_personal;
                            div.style.display = 'none';
                        });
                        div.appendChild(el);
                    });
                }
                div.style.display = 'block';
            } catch (err) {
                error('Error en búsqueda de personal');
            }
        });

        // Búsqueda de Facturación (en submodal)
        document.getElementById('busquedaFacturacion').addEventListener('input', async function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosFacturacion');
            div.innerHTML = '';
            div.style.display = 'none';

            if (term.length < 2) return;

            try {
                const res = await fetch(`../api/get_facturacion_pagos.php?q=${encodeURIComponent(term)}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    div.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                } else {
                    data.forEach(f => {
                        const el = document.createElement('div');
                        el.style.padding = '8px';
                        el.style.cursor = 'pointer';
                        el.style.borderBottom = '1px solid #eee';
                        el.textContent = `${f.nro_factura} - ${f.cliente || 'Sin cliente'} - $${parseFloat(f.saldo).toLocaleString()}`;
                        el.addEventListener('click', () => {
                            facturacionSeleccionada = f;
                            document.getElementById('nro_factura_hidden').value = f.nro_factura;
                            document.getElementById('fecha_factura_hidden').value = f.fecha_factura;
                            document.getElementById('saldo_hidden').value = f.saldo;
                            document.getElementById('monto_f_hidden').value = f.monto_f;
                            document.getElementById('monto_p_hidden').value = f.monto_p;
                            div.style.display = 'none';
                        });
                        div.appendChild(el);
                    });
                }
                div.style.display = 'block';
            } catch (err) {
                error('Error en búsqueda de facturación');
            }
        });

        // Búsqueda de Vehículo (en submodal)
        document.getElementById('busquedaVehiculo').addEventListener('input', async function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosVehiculo');
            div.innerHTML = '';
            div.style.display = 'none';

            if (term.length < 2) return;

            try {
                const res = await fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(term)}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    div.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                } else {
                    data.forEach(v => {
                        const el = document.createElement('div');
                        el.style.padding = '8px';
                        el.style.cursor = 'pointer';
                        el.style.borderBottom = '1px solid #eee';
                        el.textContent = `${v.nombre_vehiculo} - ${v.patente}`;
                        el.addEventListener('click', () => {
                            vehiculoSeleccionado = v;
                            document.getElementById('id_vehiculo_pago').value = v.id_vehiculo;
                            div.style.display = 'none';
                        });
                        div.appendChild(el);
                    });
                }
                div.style.display = 'block';
            } catch (err) {
                error('Error en búsqueda de vehículo');
            }
        });

        // Calcular monto en submodal
        document.getElementById('qty_pago_tipo_monto').addEventListener('input', function() {
            const qty = parseFloat(this.value) || 0;
            const montoP = parseFloat(document.getElementById('monto_p_hidden').value) || 0;
            const total = qty * montoP;
            document.getElementById('monto_pago').value = total.toFixed(2);
        });

        // Abrir submodal
        document.getElementById('btnAgregarPago').addEventListener('click', function() {
            if (!personalSeleccionado) {
                error('Debe seleccionar un personal primero');
                return;
            }
            document.getElementById('submodalPagos').style.display = 'flex';
        });

        // Cerrar submodal
        document.getElementById('cerrarSubmodal').addEventListener('click', function() {
            document.getElementById('submodalPagos').style.display = 'none';
        });
        document.getElementById('btnCancelarSubmodal').addEventListener('click', function() {
            document.getElementById('submodalPagos').style.display = 'none';
        });

        // Guardar pago
        document.getElementById('btnGuardarPago').addEventListener('click', async function() {
            const requiredFields = [
                'nro_factura_hidden', 'id_vehiculo_pago', 'tipo_monto_pago', 
                'qty_pago_tipo_monto', 'monto_pago'
            ];
            
            for (let field of requiredFields) {
                if (!document.getElementById(field).value) {
                    error('Complete todos los campos requeridos');
                    return;
                }
            }

            const data = {
                id_personal: document.getElementById('id_personal').value,
                id_vehiculo: document.getElementById('id_vehiculo_pago').value,
                nombre: document.getElementById('nombre_display').value,
                tipo_personal: document.getElementById('tipo_personal_display').value,
                nro_factura: document.getElementById('nro_factura_hidden').value,
                fecha: document.getElementById('fecha').value,
                tipo_monto: document.getElementById('tipo_monto_pago').value,
                qty_pago_tipo_monto: document.getElementById('qty_pago_tipo_monto').value,
                monto: document.getElementById('monto_pago').value,
                fecha_factura: document.getElementById('fecha_factura_hidden').value
            };

            try {
                const res = await fetch('../api/pagos_logic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    exito(result.message || 'Pago registrado exitosamente');
                    document.getElementById('submodalPagos').style.display = 'none';
                    // Aquí iría la lógica para recargar la tabla de pagos
                } else {
                    error(result.message || 'Error al guardar el pago');
                }
            } catch (err) {
                error('Error de conexión al guardar el pago');
            }
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            const closeResults = (inputId, resultsId) => {
                const input = document.getElementById(inputId);
                const div = document.getElementById(resultsId);
                if (!input.contains(e.target) && !div.contains(e.target)) {
                    div.style.display = 'none';
                }
            };
            closeResults('busquedaPersonal', 'resultadosBusquedaPersonal');
            closeResults('busquedaFacturacion', 'resultadosFacturacion');
            closeResults('busquedaVehiculo', 'resultadosVehiculo');
        });
    </script>
</body>
</html>