<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$monto = null;
$esEdicion = false;
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM MONTO WHERE id_monto = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $monto = $stmt->fetch();
    $esEdicion = true;
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
    <style>
        .formulario-montos-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr); /* ← Cambiado de 4 a 5 columnas */
            gap: 0.8rem;
            margin: 1rem 0;
        }
        .formulario-montos-grid .label-item,
        .formulario-montos-grid .field-item {
            text-align: center;
            padding: 0.3rem;
        }
        .formulario-montos-grid .label-item {
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #0066cc;
            font-size: 0.85rem;
        }
        .formulario-montos-grid .field-item {
            min-height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .formulario-montos-grid .field-item input,
        .formulario-montos-grid .field-item select {
            width: 95%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .formulario-montos-grid .field-item input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .formulario-montos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-money-bill-wave"></i> Configuración de Montos</h2>
        </div>

        <!-- Búsqueda inteligente -->
        <div style="height: 4rem;"></div>
        <div style="margin: 1rem 0; position: relative;">
            <label><i class="fas fa-search"></i> Búsqueda de Vehículos</label>
            <input type="text" id="busquedaVehiculo" placeholder="Buscar por patente, marca, modelo o nombre del vehículo..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
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

        <!-- Formulario de Montos -->
        <div class="card">
            <h3><i class="fas fa-file-invoice-dollar"></i> Ficha de Montos</h3>
            <form method="POST" action="monto_logic.php">
                <input type="hidden" name="id_monto" value="<?= $monto['id_monto'] ?? '' ?>">
                <input type="hidden" name="id_vehiculo" id="id_vehiculo" value="<?= $monto['id_vehiculo'] ?? '' ?>">

                <div class="formulario-montos-grid">
                    <!-- Fila 1: Labels -->
                    <div class="label-item">Nombre Vehículo</div>
                    <div class="label-item">Tipo Monto</div>
                    <div class="label-item">Tipo Personal</div>
                    <div class="label-item">Monto P ($)</div>
                    <div class="label-item">Monto F ($)</div>
                    
                    <!-- Fila 2: Campos -->
                    <div class="field-item">
                        <input type="text" id="nombre_vehiculo_display" name="nombre_vehiculo_display" 
                            value="<?= htmlspecialchars($monto['nombre_vehiculo'] ?? '') ?>" 
                            required>
                    </div>
                    <div class="field-item">
                        <select name="tipo_monto" required>
                            <option value="">Seleccionar</option>
                            <option value="Guía" <?= ($monto['tipo_monto'] ?? '') === 'Guía' ? 'selected' : '' ?>>Guía</option>
                            <option value="Distancia" <?= ($monto['tipo_monto'] ?? '') === 'Distancia' ? 'selected' : '' ?>>Distancia</option>
                            <option value="día" <?= ($monto['tipo_monto'] ?? '') === 'día' ? 'selected' : '' ?>>Día</option>
                        </select>
                    </div>
                    <div class="field-item">
                        <select name="tipo_personal" required>
                            <option value="">Seleccionar</option>
                            <option value="Chofer" <?= ($monto['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                            <option value="Peoneta" <?= ($monto['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                        </select>
                    </div>
                    <div class="field-item">
                        <input type="number" name="monto_p" value="<?= $monto['monto_p'] ?? '' ?>" required min="0" step="0.01">
                    </div>
                    <div class="field-item">
                        <input type="number" name="monto_f" value="<?= $monto['monto_f'] ?? '' ?>" required min="0" step="0.01">
                    </div>
                </div>

                <div class="action-buttons" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de todos los montos -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Registro de Montos</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Tipo Monto</th>
                            <th>Tipo Personal</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMontos"></tbody>
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

        // Cargar tabla de montos
        async function cargarTablaMontos() {
            try {
                const res = await fetch('../api/get_monto.php');
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();
                const tbody = document.getElementById('tablaMontos');
                tbody.innerHTML = data.map(m => `
                    <tr>
                        <td>${m.nombre_vehiculo || '-'}</td>
                        <td>${m.tipo_monto}</td>
                        <td>${m.tipo_personal}</td>
                        <td>$${parseFloat(m.monto_p || 0).toLocaleString()}</td>
                        <td>$${parseFloat(m.monto_f || 0).toLocaleString()}</td>
                        <td>
                            <a href="?edit=${m.id_monto}" class="btn-edit">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="monto_logic.php?delete=${m.id_monto}" class="btn-delete" 
                            onclick="return confirm('¿Eliminar?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            } catch (err) {
                console.error('Error al cargar montos:', err);
                error('Error al cargar la tabla de montos');
            }
        }

        // Búsqueda inteligente
        let busquedaTimeout;
        document.getElementById('busquedaVehiculo').addEventListener('input', function() {
            const term = this.value.trim();
            const div = document.getElementById('resultadosBusqueda');
            div.innerHTML = '';
            div.style.display = 'none';

            if (term.length < 2) return;

            clearTimeout(busquedaTimeout);
            busquedaTimeout = setTimeout(() => {
                fetch(`../api/get_monto_busqueda.php?q=${encodeURIComponent(term)}`)
                    .then(r => {
                        if (!r.ok) {
                            throw new Error(`HTTP error! status: ${r.status}`);
                        }
                        return r.json();
                    })
                    .then(montos => {
                        div.innerHTML = '';
                        if (montos.length === 0) {
                            div.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
                        } else {
                            const unicos = montos.filter((v, i, a) => 
                                i === a.findIndex(v2 => v2.id_monto === v.id_monto)
                            );
                            unicos.forEach(m => {
                                const el = document.createElement('div');
                                el.style.padding = '8px';
                                el.style.cursor = 'pointer';
                                el.style.borderBottom = '1px solid #eee';
                                el.textContent = `${m.nombre_vehiculo} | ${m.tipo_monto} | ${m.tipo_personal} | P: $${parseFloat(m.monto_p).toLocaleString()} | F: $${parseFloat(m.monto_f).toLocaleString()}`;
                                el.addEventListener('click', () => {
                                    const elementos = [
                                        { id: 'id_monto', value: m.id_monto || '' },
                                        { id: 'id_vehiculo', value: m.id_vehiculo || '' },
                                        { id: 'nombre_vehiculo_display', value: m.nombre_vehiculo || '' },
                                        { id: 'monto_p', value: m.monto_p || '' },
                                        { id: 'monto_f', value: m.monto_f || '' }
                                    ];
                                    
                                    elementos.forEach(item => {
                                        const elemento = document.getElementById(item.id);
                                        if (elemento) {
                                            elemento.value = item.value;
            } else {
                console.warn(`[WARNING] Elemento con ID "${item.id}" no encontrado`);
            }
        });
        
        // Actualizar selects
        const tipoMontoSelect = document.querySelector('select[name="tipo_monto"]');
        const tipoPersonalSelect = document.querySelector('select[name="tipo_personal"]');
        
        if (tipoMontoSelect) tipoMontoSelect.value = m.tipo_monto || '';
        if (tipoPersonalSelect) tipoPersonalSelect.value = m.tipo_personal || '';
        
        div.style.display = 'none';
    });
    div.appendChild(el);
});
                        }
                        div.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Error en búsqueda:', err);
                        error('Error en búsqueda');
                        div.innerHTML = '<div style="padding:8px;color:#e74c3c;">Error de conexión</div>';
                        div.style.display = 'block';
                    });
            }, 300);
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            const input = document.getElementById('busquedaVehiculo');
            const div = document.getElementById('resultadosBusqueda');
            if (!input.contains(e.target) && !div.contains(e.target)) {
                div.style.display = 'none';
            }
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            cargarTablaMontos();
            
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (msg) {
                let text = "", type = "info";
                switch(msg) {
                    case 'success': text = "✅ Monto guardado exitosamente"; type = "success"; break;
                    case 'delete_success': text = "🗑️ Monto eliminado"; type = "success"; break;
                    case 'error': text = "❌ Error al guardar"; type = "error"; break;
                }
                if (text) mostrarNotificacion(text, type);
            }
        });
    </script>
</body>
</html>