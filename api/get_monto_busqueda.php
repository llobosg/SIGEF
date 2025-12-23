<?php
// api/get_monto_busqueda.php
header('Content-Type: application/json');
require '../config.php';

$term = $_GET['q'] ?? '';
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT id_monto, id_vehiculo, nombre_vehiculo, tipo_monto, tipo_personal, monto
    FROM MONTO 
    WHERE nombre_vehiculo LIKE ? OR tipo_monto LIKE ? OR tipo_personal LIKE ?
    ORDER BY nombre_vehiculo
    LIMIT 10
");
$search = "%$term%";
$stmt->execute([$search, $search, $search]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>