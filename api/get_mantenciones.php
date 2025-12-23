<?php
// api/get_mantenciones.php
header('Content-Type: application/json');
require '../config.php';

$idVehiculo = $_GET['id_vehiculo'] ?? null;
if (!$idVehiculo) {
    echo json_encode([]);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT id_mantencion, fecha_mant, nombre_vehiculo, kilometraje, 
           tipo_mant, taller, costo, reparacion, notas_mant
    FROM mantencion 
    WHERE id_vehiculo = ?
    ORDER BY fecha_mant DESC
");
$stmt->execute([$idVehiculo]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
?>