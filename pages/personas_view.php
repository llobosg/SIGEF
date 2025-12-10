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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h2><i class="fas fa-user-friends"></i> Ficha Personal</h2>
        </div>

        <div class="card">
            <form method="POST" action="personas_logic.php" id="formPersonal">
                <input type="hidden" name="id_personal" value="<?= $persona['id_personal'] ?? '' ?>">

                <div class="form-4col-grid">
                    <!-- Fila 1 -->
                    <label>RUT</label>
                    <input type="text" name="rut" id="rut" value="<?= htmlspecialchars($persona['rut'] ?? '') ?>" 
                           onchange="validarRUT(this)" placeholder="12345678-9" required>
                    <label>Tipo Personal</label>
                    <select name="tipo_personal" id="tipo_personal" required>
                        <option value="">Seleccionar</option>
                        <option value="Chofer" <?= ($persona['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                        <option value="Peoneta" <?= ($persona['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                    </select>

                    <!-- Fila 2 -->
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($persona['nombre'] ?? '') ?>" required>
                    <label id="lblTipoLic">Tipo Licencia</label>
                    <select name="tipo_licencia" id="tipo_licencia">
                        <option value="">Seleccionar</option>
                        <option value="A1" <?= ($persona['tipo_licencia'] ?? '') === 'A1' ? 'selected' : '' ?>>A1</option>
                        <option value="A2" <?= ($persona['tipo_licencia'] ?? '') === 'A2' ? 'selected' : '' ?>>A2</option>
                        <option value="B" <?= ($persona['tipo_licencia'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                    </select>

                    <!-- Fila 3 -->
                    <label>Fecha Nacimiento</label>
                    <input type="date" name="fecha_nac" value="<?= $persona['fecha_nac'] ?? '' ?>">
                    <label id="lblFechaVenc">Fecha Vencimiento</label>
                    <input type="date" name="fecha_venc_lic" id="fecha_venc_lic" value="<?= $persona['fecha_venc_lic'] ?? '' ?>">

                    <!-- Fila 4 -->
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="">Seleccionar</option>
                        <option value="Vigente" <?= ($persona['estado'] ?? '') === 'Vigente' ? 'selected' : '' ?>>Vigente</option>
                        <option value="Baja" <?= ($persona['estado'] ?? '') === 'Baja' ? 'selected' : '' ?>>Baja</option>
                    </select>
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="<?= $persona ? 'Dejar vacío para no cambiar' : 'Contraseña' ?>" 
                        <?= !$persona ? 'required' : '' ?>>

                    <!-- Fila 5 -->
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($persona['direccion'] ?? '') ?>">
                    <label>Rol</label>
                    <select name="rol" required>
                        <option value="">Seleccionar</option>
                        <option value="admin" <?= ($persona['rol'] ?? 'basico') === 'admin' ? 'selected' : '' ?>>admin</option>
                        <option value="basico" <?= ($persona['rol'] ?? 'basico') === 'basico' ? 'selected' : '' ?>>básico</option>
                    </select>

                    <!-- Fila 6 -->
                    <label>Comuna</label>
                    <input type="text" name="comuna" value="<?= htmlspecialchars($persona['comuna'] ?? '') ?>">
                    <div></div><div></div>

                    <!-- Fila 7 -->
                    <label>Celular</label>
                    <input type="text" name="celular" value="<?= htmlspecialchars($persona['celular'] ?? '') ?>">
                    <div></div><div></div>

                    <!-- Fila 8 -->
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($persona['email'] ?? '') ?>">
                    <div></div><div></div>
                </div>

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
                input.value = rut.replace(/-/g, '').slice(0,8);
                return;
            }
            let num = parts[0].slice(0,8);
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
            const lblTipo = document.getElementById('lblTipoLic');
            const lblFecha = document.getElementById('lblFechaVenc');
            const selTipo = document.getElementById('tipo_licencia');
            const inpFecha = document.getElementById('fecha_venc_lic');

            if (isChofer) {
                lblTipo.style.opacity = 1;
                lblFecha.style.opacity = 1;
                selTipo.disabled = false;
                inpFecha.disabled = false;
            } else {
                lblTipo.style.opacity = 0.4;
                lblFecha.style.opacity = 0.4;
                selTipo.disabled = true;
                inpFecha.disabled = true;
                selTipo.value = '';
                inpFecha.value = '';
            }
        }

        document.getElementById('tipo_personal').addEventListener('change', toggleLicencia);
        toggleLicencia();

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

        // Toast desde URL
        (function() {
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (!msg) return;
            let text = "", bg = "#27ae60";
            switch(msg) {
                case 'insert_success': text = "✅ Personal creado"; break;
                case 'update_success': text = "✅ Datos actualizados"; break;
                case 'delete_success': text = "🗑️ Registro eliminado"; break;
                case 'rut_duplicado': text = "❌ RUT ya existe"; bg = "#e74c3c"; break;
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

        /* Grid de 4 columnas: label - campo - label - campo */
        .form-4col-grid {
            display: grid;
            grid-template-columns: minmax(120px, auto) 1fr minmax(120px, auto) 1fr;
            gap: 0.8rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .form-4col-grid label {
            font-weight: normal;
            color: var(--dark);
            display: flex;
            align-items: center;
            height: 100%;
        }

        .form-4col-grid input,
        .form-4col-grid select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
        }

        /* Espaciadores vacíos */
        .form-4col-grid > div:empty {
            visibility: hidden;
        }

        /* Deshabilitar visualmente */
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

        @media (max-width: 992px) {
            .form-4col-grid {
                grid-template-columns: 1fr;
            }
            .form-4col-grid label {
                text-align: left;
            }
        }
    </style>
</body>
</html>