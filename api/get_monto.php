<?php
header('Content-Type: application/json');
require '../config.php';

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT 
        id_monto, 
        id_vehiculo, 
        nombre_vehiculo, 
        tipo_monto, 
        tipo_personal, 
        monto 
    FROM MONTO 
    ORDER BY nombre_vehiculo, tipo_personal
");
echo json_encode($stmt->fetchAll());
?>