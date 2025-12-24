<?php
header('Content-Type: application/json');
require '../config.php';

$term = $_GET['q'] ?? '';
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT id_personal, nombre, tipo_personal
    FROM PERSONAL 
    WHERE nombre LIKE ?
    ORDER BY nombre
    LIMIT 10
");
$search = "%$term%";
$stmt->execute([$search]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>