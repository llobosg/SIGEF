<?php
// api/pagos_logic.php
header('Content-Type: application/json');

// Logging para debugging
error_log("[PAGOS_LOGIC] Inicio de ejecución");
error_log("[PAGOS_LOGIC] POST recibido: " . json_encode($_POST));

require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    error_log("[PAGOS_LOGIC] Acceso denegado: rol no es admin");
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require '../config.php';
$pdo = getDBConnection();

try {
    // Validar que se reciban los datos necesarios
    $required_fields = ['id_personal', 'id_vehiculo', 'nombre', 'tipo_personal', 'nro_factura', 'cliente', 'fecha', 'fecha_factura', 'tipo_monto', 'qty_pago_tipo_monto', 'monto', 'monto_p', 'monto_f'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("[PAGOS_LOGIC] Campo requerido faltante: $field");
            echo json_encode(['success' => false, 'message' => "Campo requerido faltante: $field"]);
            exit;
        }
    }

    // Extraer y sanitizar datos
    $id_personal = (int)$_POST['id_personal'];
    $id_vehiculo = (int)$_POST['id_vehiculo'];
    $nombre = trim($_POST['nombre']);
    $tipo_personal = trim($_POST['tipo_personal']);
    $nro_factura = trim($_POST['nro_factura']);
    $cliente = trim($_POST['cliente']);
    $fecha = $_POST['fecha'];
    $fecha_factura = $_POST['fecha_factura'];
    $tipo_monto = trim($_POST['tipo_monto']);
    $qty_pago_tipo_monto = (int)$_POST['qty_pago_tipo_monto'];
    $monto = (float)$_POST['monto'];
    $monto_p = (float)$_POST['monto_p'];
    $monto_f = (float)$_POST['monto_f'];

    // Validaciones adicionales
    if ($monto <= 0 || $qty_pago_tipo_monto <= 0) {
        error_log("[PAGOS_LOGIC] Valores inválidos: monto=$monto, qty=$qty_pago_tipo_monto");
        echo json_encode(['success' => false, 'message' => 'Valores de pago inválidos']);
        exit;
    }

    // Verificar que la facturación exista y tenga saldo suficiente
    $stmt_check = $pdo->prepare("
        SELECT saldo, monto_f 
        FROM FACTURACION 
        WHERE nro_factura = ?
    ");
    $stmt_check->execute([$nro_factura]);
    $facturacion = $stmt_check->fetch();

    if (!$facturacion) {
        error_log("[PAGOS_LOGIC] Facturación no encontrada: $nro_factura");
        echo json_encode(['success' => false, 'message' => 'Facturación no encontrada']);
        exit;
    }

    if ($monto > $facturacion['saldo']) {
        error_log("[PAGOS_LOGIC] Saldo insuficiente: monto=$monto, saldo={$facturacion['saldo']}");
        echo json_encode(['success' => false, 'message' => 'Monto excede el saldo disponible']);
        exit;
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // 1. Insertar el pago
        $stmt_pago = $pdo->prepare("
            INSERT INTO PAGOS (
                nro_factura, cliente, id_vehiculo, nombre_vehiculo, 
                fecha, id_personal, nombre, tipo_personal, 
                tipo_monto, qty_pago_tipo_monto, monto, monto_p, monto_f,
                total_monto, fecha_factura
            ) VALUES (
                ?, ?, ?, 
                (SELECT nombre_vehiculo FROM VEHICULO WHERE id_vehiculo = ? LIMIT 1),
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?
            )
        ");
        
        // Obtener nombre_vehiculo para el total_monto
        $stmt_veh = $pdo->prepare("SELECT nombre_vehiculo FROM VEHICULO WHERE id_vehiculo = ?");
        $stmt_veh->execute([$id_vehiculo]);
        $veh_result = $stmt_veh->fetch();
        $nombre_vehiculo = $veh_result ? $veh_result['nombre_vehiculo'] : 'Vehículo no encontrado';

        $stmt_pago->execute([
            $nro_factura, $cliente, $id_vehiculo,
            $id_vehiculo, // Para la subquery
            $fecha, $id_personal, $nombre, $tipo_personal,
            $tipo_monto, $qty_pago_tipo_monto, $monto, $monto_p, $monto_f,
            $monto, // total_monto inicial (se actualizará después)
            $fecha_factura
        ]);

        $id_pago = $pdo->lastInsertId();
        error_log("[PAGOS_LOGIC] Pago insertado con ID: $id_pago");

        // 2. Actualizar total_monto del personal (sumar todos los pagos de este personal)
        $stmt_total = $pdo->prepare("
            UPDATE PAGOS p1
            SET total_monto = (
                SELECT COALESCE(SUM(monto), 0) 
                FROM PAGOS p2 
                WHERE p2.id_personal = ?
            )
            WHERE p1.id_personal = ?
        ");
        $stmt_total->execute([$id_personal, $id_personal]);
        error_log("[PAGOS_LOGIC] Total_monto actualizado para personal ID: $id_personal");

        // 3. Actualizar saldo en FACTURACION
        $nuevo_saldo = $facturacion['saldo'] - $monto;
        $stmt_saldo = $pdo->prepare("
            UPDATE FACTURACION 
            SET saldo = ?, 
                pagado = monto_f - saldo 
            WHERE nro_factura = ?
        ");
        $stmt_saldo->execute([$nuevo_saldo, $nro_factura]);
        error_log("[PAGOS_LOGIC] Saldo actualizado para factura $nro_factura: $nuevo_saldo");

        // Confirmar transacción
        $pdo->commit();
        error_log("[PAGOS_LOGIC] Transacción completada exitosamente");

        echo json_encode([
            'success' => true, 
            'message' => 'Pago registrado exitosamente',
            'id_pago' => $id_pago
        ]);

    } catch (Exception $e) {
        $pdo->rollback();
        error_log("[PAGOS_LOGIC] Error en transacción: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("[PAGOS_LOGIC] Error general: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pago: ' . $e->getMessage()]);
}
?>