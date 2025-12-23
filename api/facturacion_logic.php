<?php
// api/facturacion_logic.php
// Lógica para crear, actualizar y eliminar registros de facturación
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}
require '../config.php';

$pdo = getDBConnection();

// Eliminar
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM FACTURACION WHERE id_factura = ?")->execute([(int)$_GET['delete']]);
        header("Location: /pages/facturacion_view.php?msg=delete_success");
    } catch (Exception $e) {
        error_log("Error al eliminar facturación: " . $e->getMessage());
        header("Location: /pages/facturacion_view.php?msg=error");
    }
    header("Location: /pages/facturacion_view.php?msg=success");
    exit(); // ← ¡Este exit() es crucial!
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_factura'] ?? null;
    $nro_factura = trim($_POST['nro_factura'] ?? '');
    $id_vehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $nombre_vehiculo = trim($_POST['nombre_vehiculo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $tipo_monto = $_POST['tipo_monto'] ?? '';
    $monto_m = (float)($_POST['monto_m'] ?? 0); // ← Nuevo campo
    $qty_tipo_monto = (int)($_POST['qty_tipo_monto'] ?? 0);
    $monto = (float)($_POST['monto'] ?? 0);

    // Validaciones
    if (!$nro_factura || !$id_vehiculo || !$nombre_vehiculo || !$fecha || !$tipo_monto || !$monto_m || !$qty_tipo_monto || !$monto) {
        header("Location: /pages/facturacion_view.php?msg=error");
        exit;
    }

    // Calcular llave_mes
    $llave_mes = date('Ym', strtotime($fecha));

    if ($id) {
        // Actualizar
        $pdo->prepare("UPDATE FACTURACION SET 
            nro_factura = ?, id_vehiculo = ?, nombre_vehiculo = ?, 
            fecha = ?, tipo_monto = ?, monto_m = ?, qty_tipo_monto = ?, monto = ?, llave_mes = ?
            WHERE id_factura = ?")
            ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $monto_m, $qty_tipo_monto, $monto, $llave_mes, $id]);
    } else {
        // Insertar
        $pdo->prepare("INSERT INTO FACTURACION (
            nro_factura, id_vehiculo, nombre_vehiculo, fecha, tipo_monto, monto_m, qty_tipo_monto, monto, llave_mes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $monto_m, $qty_tipo_monto, $monto, $llave_mes]);
    }

    try {
        if ($id) {
            // Actualizar
            $pdo->prepare("UPDATE FACTURACION SET 
                nro_factura = ?, id_vehiculo = ?, nombre_vehiculo = ?, 
                fecha = ?, tipo_monto = ?, qty_tipo_monto = ?, monto = ?, llave_mes = ?
                WHERE id_factura = ?")
                ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $qty_tipo_monto, $monto, $llave_mes, $id]);
        } else {
            // Insertar
            $pdo->prepare("INSERT INTO FACTURACION (
                nro_factura, id_vehiculo, nombre_vehiculo, fecha, tipo_monto, qty_tipo_monto, monto, llave_mes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $qty_tipo_monto, $monto, $llave_mes]);
        }
        header("Location: /pages/facturacion_view.php?msg=success");
        exit;
    } catch (Exception $e) {
        error_log("Error en facturacion_logic.php: " . $e->getMessage());
        header("Location: /pages/facturacion_view.php?msg=error");
        exit;
    }
}
?>