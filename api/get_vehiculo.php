<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Acceso denegado');
}
header('Content-Type: application/json');
require '../config.php';
$pdo = getDBConnection();

$vehiculos = $pdo->query("SELECT * FROM VEHICULO ORDER BY patente")->fetchAll();
echo json_encode($vehiculos);