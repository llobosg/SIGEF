<?php
header('Content-Type: application/json');
require '../config.php';
$pdo = getDBConnection();

$vehiculos = $pdo->query("SELECT * FROM VEHICULO ORDER BY patente")->fetchAll();
echo json_encode($vehiculos);