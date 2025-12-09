<?php
header('Content-Type: application/json');
require '../config.php';
$pdo = getDBConnection();
$data = $pdo->query("SELECT * FROM MONTO")->fetchAll();
echo json_encode($data);