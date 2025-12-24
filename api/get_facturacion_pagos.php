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
    SELECT 
        nro_factura, 
        cliente, 
        fecha_factura, 
        tipo_monto, 
        qty_tipo_monto, 
        monto_f, 
        monto_p, 
        saldo
    FROM FACTURACION 
    WHERE nro_factura LIKE ? OR cliente LIKE ?
    ORDER BY fecha_factura DESC
    LIMIT 10
");
$search = "%$term%";
$stmt->execute([$search, $search]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: descomentar si sigue sin funcionar
// error_log("Búsqueda facturación - término: $term, resultados: " . count($results));

echo json_encode($results);
?>