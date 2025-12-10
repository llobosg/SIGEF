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
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <h2><?= $persona ? 'Editar Personal' : 'Nuevo Personal' ?></h2>

            <?php if ($alert_msg): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($alert_msg) ?></div>
            <?php endif; ?>

            <form id="formPersonal" method="POST" action="personas_logic.php">
                <input type="hidden" name="id_personal" value="<?= $persona['id_personal'] ?? '' ?>">

                <input type="text" name="rut" id="rut" placeholder="RUT (ej: 12345678-9)" 
                       value="<?= htmlspecialchars($persona['rut'] ?? '') ?>" 
                       onchange="validarRUT(this)" required>
                <span id="rut-error" style="color:red; font-size:0.9em;"></span>

                <input type="text" name="nombre" placeholder="Nombre completo" 
                       value="<?= htmlspecialchars($persona['nombre'] ?? '') ?>" required>

                <input type="date" name="fecha_nac" value="<?= $persona['fecha_nac'] ?? '' ?>">

                <input type="text" name="direccion" placeholder="Dirección" 
                       value="<?= htmlspecialchars($persona['direccion'] ?? '') ?>">

                <input type="text" name="comuna" placeholder="Comuna" 
                       value="<?= htmlspecialchars($persona['comuna'] ?? '') ?>">

                <input type="text" name="celular" placeholder="Celular" 
                       value="<?= htmlspecialchars($persona['celular'] ?? '') ?>">

                <input type="email" name="email" placeholder="Email" 
                       value="<?= htmlspecialchars($persona['email'] ?? '') ?>">

                <!-- Rol se mantiene desde login, no se edita aquí -->
                <input type="hidden" name="rol" value="<?= $persona['rol'] ?? 'basico' ?>">

                <select name="tipo_personal" id="tipo_personal" required>
                    <option value="">Tipo de Personal</option>
                    <option value="Chofer" <?= ($persona['tipo_personal'] ?? '') === 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                    <option value="Peoneta" <?= ($persona['tipo_personal'] ?? '') === 'Peoneta' ? 'selected' : '' ?>>Peoneta</option>
                </select>

                <!-- Campos de licencia: solo visibles si es Chofer -->
                <div id="licencia-fields" style="display:<?= ($persona && $persona['tipo_personal'] === 'Chofer') ? 'block' : 'none' ?>;">
                    <select name="tipo_licencia">
                        <option value="">Tipo Licencia</option>
                        <option value="A1" <?= ($persona['tipo_licencia'] ?? '') === 'A1' ? 'selected' : '' ?>>A1</option>
                        <option value="A2" <?= ($persona['tipo_licencia'] ?? '') === 'A2' ? 'selected' : '' ?>>A2</option>
                        <option value="B" <?= ($persona['tipo_licencia'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                    </select>
                    <input type="date" name="fecha_venc_lic" value="<?= $persona['fecha_venc_lic'] ?? '' ?>" placeholder="Vencimiento Licencia">
                </div>

                <select name="estado" required>
                    <option value="">Estado</option>
                    <option value="Vigente" <?= ($persona['estado'] ?? '') === 'Vigente' ? 'selected' : '' ?>>Vigente</option>
                    <option value="Baja" <?= ($persona['estado'] ?? '') === 'Baja' ? 'selected' : '' ?>>Baja</option>
                </select>

                <input type="password" name="password" placeholder="<?= $persona ? 'Dejar vacío para no cambiar' : 'Contraseña' ?>" 
                       <?= !$persona ? 'required' : '' ?>>

                <button type="submit"><?= $persona ? 'Actualizar' : 'Guardar' ?></button>
                <?php if ($persona): ?>
                    <a href="personas_view.php">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>Lista de Personal</h3>
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

    <script>
        // Validación RUT en tiempo real
        function validarRUT(input) {
            const rut = input.value.replace(/[^0-9Kk-]/g, '').toUpperCase();
            if (!rut) return;

            const parts = rut.split('-');
            if (parts.length !== 2) {
                input.value = rut.replace(/(\d{1,8})/, '$1');
                return;
            }

            let num = parts[0];
            let dv = parts[1];
            if (num.length > 8) num = num.slice(0,8);
            input.value = num + '-' + dv;

            const cleanRut = num;
            const expectedDv = getDV(cleanRut);
            if (dv !== expectedDv) {
                document.getElementById('rut-error').textContent = 'RUT inválido';
            } else {
                document.getElementById('rut-error').textContent = '';
            }
        }

        function getDV(T) {
            let M = 0, S = 1;
            for (; T; T = Math.floor(T / 10)) S = (S + T % 10 * (9 - M++ % 6)) % 11;
            return S ? String(S - 1) : 'K';
        }

        // Mostrar/ocultar campos de licencia
        document.getElementById('tipo_personal').addEventListener('change', function() {
            const licDiv = document.getElementById('licencia-fields');
            licDiv.style.display = this.value === 'Chofer' ? 'block' : 'none';
            if (this.value !== 'Chofer') {
                // Limpiar si no es chofer
                document.querySelector('select[name="tipo_licencia"]').value = '';
                document.querySelector('input[name="fecha_venc_lic"]').value = '';
            }
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
            });
    </script>
</body>
</html>