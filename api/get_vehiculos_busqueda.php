<?php
// api/get_vehiculos_busqueda.php
header('Content-Type: application/json');
error_reporting(E_ALL); // Para debugging

require '../config.php';

$term = $_GET['q'] ?? '';
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verificar que los campos existan en tu tabla VEHICULO
    $stmt = $pdo->prepare("
        SELECT 
            id_vehiculo, 
            patente, 
            marca, 
            modelo, 
            nombre_vehiculo,
            year
        FROM VEHICULO 
        WHERE patente LIKE ? 
           OR marca LIKE ? 
           OR modelo LIKE ? 
           OR nombre_vehiculo LIKE ?
        ORDER BY patente
        LIMIT 10
    ");
    
    $search = "%$term%";
    $stmt->execute([$search, $search, $search, $search]);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($vehiculos);
    
} catch (Exception $e) {
    error_log("[VEHICULOS_BUSQUEDA] Error: " . $e->getMessage());
    echo json_encode([]);
}
?>