<?php
    require '../session_check.php';
    if ($_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado');
    }
    require '../config.php';
    $pdo = getDBConnection();

    // Eliminar
    if (isset($_GET['delete'])) {
        $pdo->prepare("DELETE FROM VEHICULO WHERE id_vehiculo = ?")->execute([(int)$_GET['delete']]);
        header("Location: vehiculos_view.php");
        exit;
    }

    // Guardar o actualizar
    if ($_POST) {
        $id = $_POST['id_vehiculo'] ?? null;
        $data = [
            $_POST['marca'],
            $_POST['modelo'],
            (int)$_POST['year'],
            $_POST['patente'],
            $_POST['nombre_vehiculo'],
            $_POST['rev_tecnica'] ?: null,
            $_POST['permiso_circ'] ?: null,
            $_POST['nro_soap'] ?: null,
            $_POST['seguro'],
            $_POST['aseguradora'] ?: null,
            $_POST['nro_poliza'] ?: null
        ];

        if ($id) {
            $data[] = $id;
            $pdo->prepare("UPDATE VEHICULO SET marca=?, modelo=?, year=?, patente=?, nombre_vehiculo=?, rev_tecnica=?, permiso_circ=?, nro_soap=?, seguro=?, aseguradora=?, nro_poliza=? WHERE id_vehiculo=?")->execute($data);
        } else {
            $pdo->prepare("INSERT INTO VEHICULO (marca, modelo, year, patente, nombre_vehiculo, rev_tecnica, permiso_circ, nro_soap, seguro, aseguradora, nro_poliza) VALUES (?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
        }
        header("Location: vehiculos_view.php");
        exit;
    }
?>