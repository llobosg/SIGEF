<?php
// api/get_monto.php
header('Content-Type: application/json');

require '../config.php';

try {
    $pdo = getDBConnection();
    
    // Consulta actualizada con los nuevos campos
    $stmt = $pdo->prepare("
        SELECT 
            id_monto,
            id_vehiculo, 
            nombre_vehiculo, 
            tipo_monto, 
            tipo_personal, 
            monto_p,  // ← Campo nuevo
            monto_f   // ← Campo nuevo
        FROM MONTO 
        ORDER BY nombre_vehiculo
    ");
    
    $stmt->execute();
    $montos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($montos);
    
} catch (Exception $e) {
    error_log("[GET_MONTO] Error: " . $e->getMessage());
    // Devolver array vacío en caso de error (evita errores 500)
    echo json_encode([]);
}
?>