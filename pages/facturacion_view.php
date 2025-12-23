<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$factura = null;
$esEdicion = false;
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM FACTURACION WHERE id_factura = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $factura = $stmt->fetch();
    $esEdicion = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturación - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .formulario-facturacion-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0.8rem;
            margin: 1rem 0;
        }
        .formulario-facturacion-grid .label-item,
        .formulario-facturacion-grid .field-item {
            text-align: center;
            padding: 0.3rem;
        }
        .formulario-facturacion-grid .label-item {
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #0066cc;
            font-size: 0.85rem;
        }
        .formulario-facturacion-grid .field-item {
            min-height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .formulario-facturacion-grid .field-item input,
        .formulario-facturacion-grid .field-item select {
            width: 95%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .formulario-facturacion-grid .field-item input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        /* Responsive: en móviles, 2 columnas */
        @media (max-width: 768px) {
            .formulario-facturacion-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .formulario-facturacion-grid > div:nth-child(2n+1) {
                grid-column: 1;
            }
            .formulario-facturacion-grid > div:nth-child(2n+2) {
                grid-column: 2;
            }
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-file-invoice"></i> Facturación</h2>
        </div>

        <!-- Búsqueda inteligente -->
        <div style="height: 4rem;"></div>
        <div style="margin: 1rem 0; position: relative;">
            <label><i class="fas fa-search"></i> Búsqueda de Montos</label>
            <input type="text" id="busquedaMontos" placeholder="Buscar por nombre vehículo, tipo monto o tipo personal..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
            <div id="resultadosBusqueda" style="
                position: absolute;
                top: 100%;
                left: 0;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                max-height: 300px;
                overflow-y: auto;
                width: 100%;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                display: none;
            "></div>
        </div>

        <!-- Formulario de Facturación -->
        <div class="card">
            <h3><i class="fas fa-receipt"></i> Ficha de Facturación</h3>
            <form method="POST" action="../api/facturacion_logic.php">
                <input type="hidden" name="id_factura" value="<?= $factura['id_factura'] ?? '' ?>">
                <input type="hidden" name="id_vehiculo" id="id_vehiculo" value="<?= $factura['id_vehiculo'] ?? '' ?>">

                <div class="formulario-facturacion-grid">
                    <!-- Fila 1: Labels -->
                    <div class="label-item">N° Factura</div>
                    <div class="label-item">Nombre Vehículo</div>
                    <div class="label-item">Tipo Monto</div>
                    <div class="label-item">Monto x Vehíc</div>
                    <div class="label-item">Cantidad</div>
                    <div class="label-item">Monto x Factura</div>
                    
                    <!-- Fila 2: Campos -->
                    <div class="field-item">
                        <input type="text" name="nro_factura" 
                               value="<?= htmlspecialchars($factura['nro_factura'] ?? '') ?>" 
                               required <?= $esEdicion ? '' : 'readonly' ?>>
                    </div>
                    <div class="field-item">
                        <input type="text" name="nombre_vehiculo" id="nombre_vehiculo_display"
                               value="<?= htmlspecialchars($factura['nombre_vehiculo'] ?? '') ?>" 
                               readonly required>
                    </div>
                    <div class="field-item">
                        <input type="text" name="tipo_monto" id="tipo_monto_display"
                               value="<?= htmlspecialchars($factura['tipo_monto'] ?? '') ?>" 
                               readonly required>
                    </div>
                    <<!-- Campo oculto para el envío (con name) -->
                    <input type="hidden" name="monto_m" id="monto_m_hidden" value="<?= $factura['monto_m'] ?? '' ?>">

                    <!-- Campo de display solo para visualización (sin name) -->
                    <div class="field-item">
                        <input type="number" id="monto_m_display"
                            value="<?= $factura['monto_m'] ?? '' ?>" 
                            readonly required step="0.01" style="background-color: #f8f9fa;">
                    </div>
                    <div class="field-item">
                        <input type="number" name="qty_tipo_monto" id="qty_tipo_monto" 
                               value="<?= $factura['qty_tipo_monto'] ?? '' ?>" 
                               min="1" required <?= $esEdicion ? '' : 'readonly' ?>
                               onchange="calcularMontoTotal()">
                    </div>
                    <div class="field-item">
                        <input type="number" name="monto" id="monto_total" 
                               value="<?= $factura['monto'] ?? '' ?>" 
                               readonly required step="0.01">
                    </div>
                </div>

                <!-- Fecha -->
                <div style="margin-top: 1rem;">
                    <label>Fecha *</label>
                    <input type="date" name="fecha" value="<?= $factura['fecha'] ?? date('Y-m-d') ?>" 
                           required <?= $esEdicion ? '' : 'readonly' ?>>
                </div>

                <div class="action-buttons" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Facturación
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de facturaciones históricas -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Facturaciones Históricas</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th>Tipo Monto</th>
                            <th>Monto x Vehíc</th>
                            <th>Cantidad</th>
                            <th>Monto x Factura</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaFacturacion"></tbody>
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

        // Calcular monto total
        function calcularMontoTotal() {
            const qty = parseFloat(document.getElementById('qty_tipo_monto').value) || 0;
            const montoM = parseFloat(document.getElementById('monto_m_display').value) || 0;
            const total = qty * montoM;
            document.getElementById('monto_total').value = total.toFixed(2);
        }

        // Cargar tabla de facturación
        async function cargarTablaFacturacion() {
            try {
                const res = await fetch('../api/get_facturacion.php');
                const data = await res.json();
                const tbody = document.getElementById('tablaFacturacion');
                tbody.innerHTML = data.map(f => `
                    <tr>
                        <td>${f.nro_factura || '-'}</td>
                        <td>${f.fecha || '-'}</td>
                        <td>${f.nombre_vehiculo || '-'}</td>
                        <td>${f.tipo_monto || '-'}</td>
                        <td>$${parseFloat(f.monto_m).toLocaleString()}</td>
                        <td>${f.qty_tipo_monto || '-'}</td>
                        <td>$${parseFloat(f.monto).toLocaleString()}</td>
                        <td>
                            <a href="?edit=${f.id_factura}" class="btn-edit" title="Editar">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="../api/facturacion_logic.php?delete=${f.id_factura}" class="btn-delete" 
                            onclick="return confirm('¿Eliminar facturación?')" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            } catch (err) {
                console.error('Error al cargar facturación:', err);
            }
        }

        // Búsqueda inteligente
        let busquedaTimeout;
        document.getElementById('busquedaMontos').addEventListener('input', function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosBusqueda');
            div.innerHTML = '';
            div.style.display = 'none';

            if (term.length < 2) return;

            clearTimeout(busquedaTimeout);
            busquedaTimeout = setTimeout(() => {
                fetch(`../api/get_monto_busqueda.php?q=${encodeURIComponent(term)}`)
                    .then(r => r.json())
                    .then(montos => {
                        div.innerHTML = '';
                        if (montos.length === 0) {
                            div.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                        } else {
                            montos.forEach(m => {
                                const el = document.createElement('div');
                                el.style.padding = '8px';
                                el.style.cursor = 'pointer';
                                el.style.borderBottom = '1px solid #eee';
                                el.textContent = `${m.nombre_vehiculo} | ${m.tipo_monto} | ${m.tipo_personal} | $${parseFloat(m.monto).toLocaleString()}`;
                                el.addEventListener('click', () => {
                                    if (<?= $esEdicion ? 'false' : 'true' ?>) {
                                        // En la función de búsqueda, reemplaza:
                                        const campos = [
                                            { id: 'id_vehiculo', value: m.id_vehiculo || '' },
                                            { id: 'nombre_vehiculo_display', value: m.nombre_vehiculo || '' },
                                            { id: 'tipo_monto_display', value: m.tipo_monto || '' },
                                            { id: 'monto_m_display', value: m.monto || 0 },
                                            { id: 'monto_m_hidden', value: m.monto || 0 }, // ← Campo oculto
                                            { id: 'monto_base', value: m.monto || 0 }
                                        ];
                                        
                                        campos.forEach(item => {
                                            const el = document.getElementById(item.id);
                                            if (el) el.value = item.value;
                                        });
                                        
                                        const qtyField = document.getElementById('qty_tipo_monto');
                                        const nroField = document.querySelector('input[name="nro_factura"]');
                                        const fechaField = document.querySelector('input[name="fecha"]');
                                        const montoTotal = document.getElementById('monto_total');
                                        
                                        if (qtyField) qtyField.readOnly = false;
                                        if (nroField) nroField.readOnly = false;
                                        if (fechaField) fechaField.readOnly = false;
                                        if (montoTotal) montoTotal.value = '';
                                        
                                        if (qtyField) qtyField.value = '';
                                    }
                                    div.style.display = 'none';
                                });
                                div.appendChild(el);
                            });
                        }
                        div.style.display = 'block';
                    })
                    .catch(err => {
                        error('Error en búsqueda');
                    });
            }, 300);
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            const input = document.getElementById('busquedaMontos');
            const div = document.getElementById('resultadosBusqueda');
            if (!input.contains(e.target) && !div.contains(e.target)) {
                div.style.display = 'none';
            }
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            cargarTablaFacturacion();
            
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (msg) {
                let text = "", type = "info";
                switch(msg) {
                    case 'success': text = "✅ Facturación guardada exitosamente"; type = "success"; break;
                    case 'delete_success': text = "🗑️ Facturación eliminada"; type = "success"; break;
                    case 'error': text = "❌ Error al guardar"; type = "error"; break;
                }
                if (text) mostrarNotificacion(text, type);
            }
            
            if (<?= $esEdicion ? 'true' : 'false' ?>) {
                calcularMontoTotal();
            }
        });
    </script>
</body>
</html>