<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$persona = null;
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM PERSONAL WHERE id_personal = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $persona = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <!-- Título fuera del formulario -->
        <div class="page-title">
            <h2><i class="fas fa-user-friends"></i> Ficha Personal</h2>
        </div>

        <div class="card">
            <form method="POST" action="personas_logic.php" id="formPersonal">
                <input type="hidden" name="id_personal" value="<?= $persona['id_personal'] ?? '' ?>">

                <div class="form-4col">
                    <!-- Columna 1: Labels izquierda -->
                    <div class="form-col-labels">
                        <label>RUT</label>
                        <label>Nombre</label>
                        <label>Fecha Nacimiento</label>
                        <label>Estado</label>
                        <label>Dirección</label>
                        <label>Comuna</label>
                        <label>Celular</label>
                        <label>Email</label>
                    </div>

                    <!-- Columna 2: Campos izquierda -->
                    <div class="form-col-fields">
                        <input type="text" name="rut" id="rut" value="<?= htmlspecialchars($persona['rut'] ?? '') ?>" 
                               onchange="validarRUT(this)" placeholder="12345678-9" required>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($persona['nombre'] ?? '') ?>" required>
                        <input type="date" name="fecha_nac" value="<?= $persona['fecha_nac'] ?? '' ?>">
                        <select name="estado" required>
                            <option value="">Seleccionar</option>
                            <option value="Vigente" <?= ($persona['estado'] ?? '') === 'Vigente' ? 'selected' : '' ?>>Vigente</option>
                            <option value="Baja" <?= ($persona['estado'] ?? '') === 'Baja' ? 'selected' : '' ?>>Baja</option>
                        </select>
                        <input type="text" name="direccion" value="<?= htmlspecialchars($persona['direccion'] ?? '') ?>">
                        <input type="text" name="comuna" value="<?= htmlspecialchars($persona['comuna'] ?? '') ?>">
                        <input type="text" name="celular" value="<?= htmlspecialchars($persona['celular'] ?? '') ?>">
                        <input type="email" name="email" value="<?= htmlspecialchars($persona['email'] ?? '') ?>">
                    </div>

                    <!-- Columna 3: Labels derecha -->
                    <div class="form-col-labels">
                        <label>Tipo Personal</label>
                        <label id="lblTipoLic">Tipo Licencia</label>
                        <label id="lblFechaVenc">Fecha Vencimiento</label>
                        <label>Contraseña</label>
                        <!-- Espaciadores para alinear -->
                        <div></div><div></div><div></div><div></div>
                    </div>

                    <!-- Columna 4: Campos derecha -->
                    <div class="form-col-fields">
                        <select name="tipo_personal" id="tipo_personal" required>
                            <option value="">Seleccionar</option>
                            <option value="Chofer" <?= ($persona['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                            <option value="Peoneta" <?= ($persona['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                        </select>
                        <select name="tipo_licencia" id="tipo_licencia">
                            <option value="">Seleccionar</option>
                            <option value="A1" <?= ($persona['tipo_licencia'] ?? '') === 'A1' ? 'selected' : '' ?>>A1</option>
                            <option value="A2" <?= ($persona['tipo_licencia'] ?? '') === 'A2' ? 'selected' : '' ?>>A2</option>
                            <option value="B" <?= ($persona['tipo_licencia'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                        </select>
                        <input type="date" name="fecha_venc_lic" id="fecha_venc_lic" value="<?= $persona['fecha_venc_lic'] ?? '' ?>">
                        <input type="password" name="password" placeholder="<?= $persona ? 'Dejar vacío para no cambiar' : 'Contraseña' ?>" 
                               <?= !$persona ? 'required' : '' ?>>
                        <!-- Espaciadores -->
                        <div></div><div></div><div></div><div></div>
                    </div>
                </div>

                <!-- Botón Guardar -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de personal -->
        <div class="card">
            <h3>Registro de Personal</h3>
            <table class="data-table" id="tablaPersonal">
                <thead>
                    <tr>
                        <th>RUT</th><th>Nombre</th><th>Tipo</th><th>Estado</th><th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Validación RUT
        function validarRUT(input) {
            let rut = input.value.replace(/[^0-9Kk-]/g, '').toUpperCase();
            if (!rut) return;
            const parts = rut.split('-');
            if (parts.length !== 2) {
                const num = rut.replace(/-/g, '').slice(0, 8);
                input.value = num;
                return;
            }
            let num = parts[0].slice(0, 8);
            let dv = parts[1];
            input.value = num + '-' + dv;
            if (dv !== getDV(num)) {
                Toastify({text:"RUT inválido",duration:3000,gravity:"top",position:"right",backgroundColor:"#e74c3c"}).showToast();
            }
        }
        function getDV(T) {
            let M = 0, S = 1;
            for (; T; T = Math.floor(T / 10)) S = (S + T % 10 * (9 - M++ % 6)) % 11;
            return S ? S - 1 + '' : 'K';
        }

        // Mostrar/ocultar campos de licencia
        function toggleLicencia() {
            const isChofer = document.getElementById('tipo_personal').value === 'Chofer';
            document.getElementById('lblTipoLic').style.opacity = isChofer ? 1 : 0.4;
            document.getElementById('lblFechaVenc').style.opacity = isChofer ? 1 : 0.4;
            document.getElementById('tipo_licencia').disabled = !isChofer;
            document.getElementById('fecha_venc_lic').disabled = !isChofer;
            
            // Opcional: limpiar si se desactiva
            if (!isChofer) {
                document.getElementById('tipo_licencia').value = '';
                document.getElementById('fecha_venc_lic').value = '';
            }
        }

        // Inicializar al cargar
        document.getElementById('tipo_personal').addEventListener('change', toggleLicencia);
        toggleLicencia(); // Aplicar estado inicial

        // Cargar tabla
        fetch('../api/get_personas.php')
            .then(r => r.json())
            .then(data => {
                const tbody = document.querySelector('#tablaPersonal tbody');
                tbody.innerHTML = data.map(p => `
                    <tr>
                        <td>${p.rut}</td>
                        <td>${p.nombre}</td>
                        <td>${p.tipo_personal || '-'}</td>
                        <td>${p.estado}</td>
                        <td>
                            <a href="?edit=${p.id_personal}">Editar</a>
                            <a href="personas_logic.php?delete=${p.id_personal}" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                        </td>
                    </tr>
                `).join('');
            });

        // Notificaciones toast desde URL
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            if (!msg) return;
            let text = "", bg = "#27ae60";
            switch(msg) {
                case 'insert_success': text = "✅ Personal creado exitosamente"; break;
                case 'update_success': text = "✅ Datos actualizados"; break;
                case 'delete_success': text = "🗑️ Registro eliminado"; break;
                case 'rut_duplicado': text = "❌ El RUT ya existe"; bg = "#e74c3c"; break;
                case 'error': text = "⚠️ Error al guardar"; bg = "#e74c3c"; break;
                default: return;
            }
            Toastify({text,duration:4000,gravity:"top",position:"right",backgroundColor:bg}).showToast();
            history.replaceState({}, '', location.pathname);
        })();
    </script>

    <style>
        .page-title h2 {
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 1.2rem;
        }

        /* Layout de 4 columnas */
        .form-4col {
            display: grid;
            grid-template-columns: auto 1fr auto 1fr;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .form-col-labels,
        .form-col-fields {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .form-col-labels label {
            font-weight: normal;
            color: var(--dark);
            text-align: right;
            padding-right: 0.5rem;
        }

        .form-col-fields input,
        .form-col-fields select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .form-col-fields > div:empty {
            visibility: hidden;
        }

        /* Deshabilitar visualmente campos no aplicables */
        #tipo_licencia:disabled,
        #fecha_venc_lic:disabled {
            background-color: #f5f5f5;
            color: var(--gray);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .form-actions .btn-save {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-actions .btn-save:hover {
            background: var(--secondary-hover);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-4col {
                grid-template-columns: 1fr;
            }
            .form-col-labels label {
                text-align: left;
            }
        }
    </style>
</body>
</html>