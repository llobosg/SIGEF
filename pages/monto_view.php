<?php require '../auth.php'; if ($_SESSION['rol'] !== 'admin') die('Acceso denegado'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Configuración Montos - SIGEF</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <h2>Configurar Montos</h2>
            <form method="POST" action="monto_logic.php">
                <select name="tipo_personal" required>
                    <option value="">Tipo Personal</option>
                    <option value="Chofer">Chofer</option>
                    <option value="Peoneta">Peoneta</option>
                </select>
                <select name="tipo_monto" required>
                    <option value="">Tipo Monto</option>
                    <option value="Guía">Guía</option>
                    <option value="Distancia">Distancia</option>
                    <option value="día">Día</option>
                </select>
                <input type="number" name="monto" placeholder="Monto ($)" required>
                <button type="submit">Guardar</button>
            </form>
        </div>

        <div class="card">
            <h3>Montos Configurados</h3>
            <table class="data-table" id="tablaMontos">
                <thead><tr><th>Tipo Personal</th><th>Tipo Monto</th><th>Monto</th><th>Acción</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        fetch('../api/get_monto.php')
            .then(r => r.json())
            .then(data => {
                document.querySelector('#tablaMontos tbody').innerHTML = data.map(m => `
                    <tr>
                        <td>${m.tipo_personal}</td>
                        <td>${m.tipo_monto}</td>
                        <td>$${m.monto}</td>
                        <td><a href="monto_logic.php?delete=${m.id_monto}">Eliminar</a></td>
                    </tr>
                `).join('');
            });
    </script>
</body>
</html>