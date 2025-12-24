<?php
// api/get_monto.php
header('Content-Type: application/json');
error_reporting(E_ALL);

require '../config.php';

try {
    $pdo = getDBConnection();
    
    // Verificar la estructura actual de tu tabla MONTO
    $stmt = $pdo->prepare("
        SELECT 
            id_monto,
            id_vehiculo,
            nombre_vehiculo,
            tipo_monto,
            tipo_personal,
            monto_p,
            monto_f
        FROM MONTO 
        ORDER BY nombre_vehiculo
    ");
    
    $stmt->execute();
    $montos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($montos);
    
} catch (Exception $e) {
    error_log("[GET_MONTO] Error: " . $e->getMessage());
    echo json_encode([]);
}
?>