<?php
// api/get_facturacion.php
header('Content-Type: application/json');
require '../config.php';

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT id_factura, nro_factura, id_vehiculo, nombre_vehiculo, 
           fecha, tipo_monto, qty_tipo_monto, monto, llave_mes
    FROM FACTURACION 
    ORDER BY fecha DESC, id_factura DESC
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>