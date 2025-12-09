<?php
header('Content-Type: application/json');
require '../config.php';
$pdo = getDBConnection();
$personas = $pdo->query("SELECT * FROM PERSONAL ORDER BY nombre")->fetchAll();
echo json_encode($personas);