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
    SELECT nro_factura, cliente, fecha_factura, tipo_monto, 
           qty_tipo_monto, monto as monto_f, monto as monto_p, 
           (monto - COALESCE(pagado, 0)) as saldo
    FROM FACTURACION f
    LEFT JOIN (
        SELECT nro_factura, SUM(monto) as pagado 
        FROM PAGOS 
        GROUP BY nro_factura
    ) p ON f.nro_factura = p.nro_factura
    WHERE f.nro_factura LIKE ? OR f.cliente LIKE ?
    ORDER BY f.fecha_factura DESC
    LIMIT 10
");
$search = "%$term%";
$stmt->execute([$search, $search]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>