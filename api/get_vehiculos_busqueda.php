<?php
header('Content-Type: application/json');
require '../config.php';

$pdo = getDBConnection();
$term = $_GET['q'] ?? '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id_vehiculo, patente, marca, modelo, nombre_vehiculo 
    FROM VEHICULO 
    WHERE patente LIKE ? OR marca LIKE ? OR modelo LIKE ? OR nombre_vehiculo LIKE ?
    LIMIT 10
");
$search = "%$term%";
$stmt->execute([$search, $search, $search, $search]);
echo json_encode($stmt->fetchAll());
?>