<?php
// api/get_pagos.php
header('Content-Type: application/json');

require '../config.php';

try {
    $pdo = getDBConnection();
    
    // Consulta para obtener todos los pagos con la información requerida
    $stmt = $pdo->prepare("
        SELECT 
            p.id_pago,
            p.nro_factura,
            p.cliente,
            p.nombre_vehiculo,
            p.tipo_monto,
            p.qty_pago_tipo_monto,
            p.monto,
            p.fecha,
            p.nombre as nombre_personal,
            p.tipo_personal
        FROM PAGOS p
        ORDER BY p.fecha DESC, p.id_pago DESC
    ");
    
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Logging para debugging (opcional)
    error_log("[GET_PAGOS] Registros encontrados: " . count($pagos));
    
    echo json_encode($pagos);
    
} catch (Exception $e) {
    error_log("[GET_PAGOS] Error: " . $e->getMessage());
    echo json_encode([]);
}
?>