<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

// Cargar datos si es edición
$persona = null;
$alert_msg = '';
if (isset($_GET['edit'])) {
    require '../config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM PERSONAL WHERE id_personal = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $persona = $stmt->fetch();

    if ($persona && !empty($persona['fecha_venc_lic'])) {
        $hoy = new DateTime();
        $venc = new DateTime($persona['fecha_venc_lic']);
        $diff = $hoy->diff($venc);
        if ($venc < $hoy) {
            $alert_msg = "⚠️ Licencia vencida hace {$diff->days} días.";
        } elseif ($diff->days <= 30) {
            $alert_msg = "⚠️ Licencia vence en {$diff->days} días.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Toastify para notificaciones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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

                <div class="form-grid-4">
                    <!-- Fila 1: Labels -->
                    <label>RUT</label>
                    <label>Nombre</label>
                    <label>Fecha Nacimiento</label>
                    <label>Estado</label>

                    <!-- Fila 2: Campos -->
                    <input type="text" name="rut" id="rut" value="<?= htmlspecialchars($persona['rut'] ?? '') ?>" 
                           onchange="validarRUT(this)" placeholder="12345678-9" required>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($persona['nombre'] ?? '') ?>" required>
                    <input type="date" name="fecha_nac" value="<?= $persona['fecha_nac'] ?? '' ?>">
                    <select name="estado" required>
                        <option value="">Seleccionar</option>
                        <option value="Vigente" <?= ($persona['estado'] ?? '') === 'Vigente' ? 'selected' : '' ?>>Vigente</option>
                        <option value="Baja" <?= ($persona['estado'] ?? '') === 'Baja' ? 'selected' : '' ?>>Baja</option>
                    </select>

                    <!-- Fila 3: Labels -->
                    <label>Dirección</label>
                    <label>Comuna</label>
                    <label>Celular</label>
                    <label>Email</label>

                    <!-- Fila 4: Campos -->
                    <input type="text" name="direccion" value="<?= htmlspecialchars($persona['direccion'] ?? '') ?>">
                    <input type="text" name="comuna" value="<?= htmlspecialchars($persona['comuna'] ?? '') ?>">
                    <input type="text" name="celular" value="<?= htmlspecialchars($persona['celular'] ?? '') ?>">
                    <input type="email" name="email" value="<?= htmlspecialchars($persona['email'] ?? '') ?>">

                    <!-- Fila 5: Labels -->
                    <label>Tipo Personal</label>
                    <label>Rol</label>
                    <label>Contraseña</label>
                    <label></label>

                    <!-- Fila 6: Campos -->
                    <select name="tipo_personal" id="tipo_personal" required>
                        <option value="">Seleccionar</option>
                        <option value="Chofer" <?= ($persona['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                        <option value="Peoneta" <?= ($persona['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                    </select>
                    <select name="rol" disabled>
                        <option value="basico" <?= ($persona['rol'] ?? 'basico') === 'basico' ? 'selected' : '' ?>>Básico</option>
                        <!-- Solo admin puede crear admin, pero lo dejamos fijo por ahora -->
                    </select>
                    <input type="password" name="password" placeholder="<?= $persona ? 'Dejar vacío para no cambiar' : 'Contraseña' ?>" 
                           <?= !$persona ? 'required' : '' ?>>
                    <div></div>
                </div>

                <!-- Botón Guardar -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>

            <?php if (isset($alert_msg)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($alert_msg) ?></div>
            <?php endif; ?>
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

    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Validación RUT chileno
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
                Toastify({
                    text: "RUT inválido",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                }).showToast();
            }
        }
        function getDV(T) {
            let M = 0, S = 1;
            for (; T; T = Math.floor(T / 10)) S = (S + T % 10 * (9 - M++ % 6)) % 11;
            return S ? S - 1 + '' : 'K';
        }

        // Mostrar/ocultar campos de licencia (si se agrega en el futuro)
        document.getElementById('tipo_personal')?.addEventListener('change', function() {
            // Opcional: habilitar campos adicionales si es Chofer
        });

        // Cargar tabla desde API
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
            })
            .catch(err => {
                Toastify({
                    text: "Error al cargar personal",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                }).showToast();
            });
    </script>

    <style>
        .page-title h2 {
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 1.2rem;
        }

        /* Grid de 4 columnas */
        .form-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }

        .form-grid-4 label {
            text-align: center;
            font-weight: normal;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .form-grid-4 input,
        .form-grid-4 select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .form-grid-4 > div:empty {
            visibility: hidden;
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
        @media (max-width: 768px) {
            .form-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            .form-grid-4 label {
                grid-column: span 2;
                text-align: left;
            }
        }
    </style>
    <script>
        // Mostrar notificación al cargar la página según parámetro 'msg'
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            if (!msg) return;

            let text = "", bg = "#27ae60";

            switch(msg) {
                case 'insert_success':
                    text = "✅ Personal creado exitosamente";
                    break;
                case 'update_success':
                    text = "✅ Datos actualizados";
                    break;
                case 'delete_success':
                    text = "🗑️ Registro eliminado";
                    break;
                case 'rut_duplicado':
                    text = "❌ El RUT ya existe en el sistema";
                    bg = "#e74c3c";
                    break;
                case 'error':
                    text = "⚠️ Error al guardar los datos";
                    bg = "#e74c3c";
                    break;
                default:
                    return;
            }

            // Mostrar toast
            Toastify({
                text: text,
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: bg,
                stopOnFocus: true,
            }).showToast();

            // Limpiar parámetro 'msg' de la URL sin recargar
            window.history.replaceState({}, document.title, window.location.pathname);
        })();
        </script>
</body>
</html>