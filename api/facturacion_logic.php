<?php
// api/facturacion_logic.php - Versión con logging detallado
error_log("[FACTURACION_LOGIC] === INICIO DE EJECUCIÓN ===");
error_log("[FACTURACION_LOGIC] Método: " . $_SERVER['REQUEST_METHOD']);
error_log("[FACTURACION_LOGIC] GET: " . json_encode($_GET));
error_log("[FACTURACION_LOGIC] POST: " . json_encode($_POST));
error_log("[FACTURACION_LOGIC] SESSION: " . json_encode($_SESSION ?? []));

require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    error_log("[FACTURACION_LOGIC] Acceso denegado: rol no es admin");
    http_response_code(403);
    exit('Acceso denegado');
}

require '../config.php';
$pdo = getDBConnection();

// Eliminar registro
if (isset($_GET['delete'])) {
    error_log("[FACTURACION_LOGIC] Operación: ELIMINAR");
    error_log("[FACTURACION_LOGIC] ID a eliminar: " . $_GET['delete']);
    
    try {
        $result = $pdo->prepare("DELETE FROM FACTURACION WHERE id_factura = ?")->execute([(int)$_GET['delete']]);
        error_log("[FACTURACION_LOGIC] Eliminación exitosa: " . ($result ? 'true' : 'false'));
        header("Location: /pages/facturacion_view.php?msg=delete_success");
        error_log("[FACTURACION_LOGIC] Redirigiendo a éxito de eliminación");
        exit;
    } catch (Exception $e) {
        error_log("[FACTURACION_LOGIC] Error al eliminar: " . $e->getMessage());
        header("Location: /pages/facturacion_view.php?msg=error");
        exit;
    }
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[FACTURACION_LOGIC] Operación: GUARDAR/ACTUALIZAR");
    
    $id = $_POST['id_factura'] ?? null;
    $nro_factura = trim($_POST['nro_factura'] ?? '');
    $id_vehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $nombre_vehiculo = trim($_POST['nombre_vehiculo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $tipo_monto = $_POST['tipo_monto'] ?? '';
    $monto_m = (float)($_POST['monto_m'] ?? 0);
    $qty_tipo_monto = (int)($_POST['qty_tipo_monto'] ?? 0);
    $monto = (float)($_POST['monto'] ?? 0);

    error_log("[FACTURACION_LOGIC] Datos recibidos:");
    error_log("[FACTURACION_LOGIC]   id: $id");
    error_log("[FACTURACION_LOGIC]   nro_factura: $nro_factura");
    error_log("[FACTURACION_LOGIC]   id_vehiculo: $id_vehiculo");
    error_log("[FACTURACION_LOGIC]   nombre_vehiculo: $nombre_vehiculo");
    error_log("[FACTURACION_LOGIC]   fecha: $fecha");
    error_log("[FACTURACION_LOGIC]   tipo_monto: $tipo_monto");
    error_log("[FACTURACION_LOGIC]   monto_m: $monto_m");
    error_log("[FACTURACION_LOGIC]   qty_tipo_monto: $qty_tipo_monto");
    error_log("[FACTURACION_LOGIC]   monto: $monto");

    // Validaciones
    if (!$nro_factura || !$id_vehiculo || !$nombre_vehiculo || !$fecha || !$tipo_monto || !$monto_m || !$qty_tipo_monto || !$monto) {
        error_log("[FACTURACION_LOGIC] Validación fallida - campos incompletos");
        header("Location: /pages/facturacion_view.php?msg=error");
        exit;
    }

    // Calcular llave_mes
    $llave_mes = date('Ym', strtotime($fecha));
    error_log("[FACTURACION_LOGIC] llave_mes calculada: $llave_mes");

    try {
        if ($id) {
            error_log("[FACTURACION_LOGIC] ACTUALIZANDO registro ID: $id");
            $result = $pdo->prepare("UPDATE FACTURACION SET 
                nro_factura = ?, id_vehiculo = ?, nombre_vehiculo = ?, 
                fecha = ?, tipo_monto = ?, monto_m = ?, qty_tipo_monto = ?, monto = ?, llave_mes = ?
                WHERE id_factura = ?")
                ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $monto_m, $qty_tipo_monto, $monto, $llave_mes, $id]);
            error_log("[FACTURACION_LOGIC] Actualización resultado: " . ($result ? 'éxito' : 'fracaso'));
        } else {
            error_log("[FACTURACION_LOGIC] INSERTANDO nuevo registro");
            $result = $pdo->prepare("INSERT INTO FACTURACION (
                nro_factura, id_vehiculo, nombre_vehiculo, fecha, tipo_monto, monto_m, qty_tipo_monto, monto, llave_mes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$nro_factura, $id_vehiculo, $nombre_vehiculo, $fecha, $tipo_monto, $monto_m, $qty_tipo_monto, $monto, $llave_mes]);
            error_log("[FACTURACION_LOGIC] Inserción resultado: " . ($result ? 'éxito' : 'fracaso'));
            error_log("[FACTURACION_LOGIC] ID insertado: " . $pdo->lastInsertId());
        }
        
        error_log("[FACTURACION_LOGIC] Redirigiendo a éxito");
        header("Location: /pages/facturacion_view.php?msg=success");
        exit; // ← ¡Este exit es CRÍTICO!
        
    } catch (Exception $e) {
        error_log("[FACTURACION_LOGIC] Error en operación: " . $e->getMessage());
        header("Location: /pages/facturacion_view.php?msg=error");
        exit;
    }
}

error_log("[FACTURACION_LOGIC] === FIN DE EJECUCIÓN (sin operación) ===");
?>