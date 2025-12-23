<?php
header('Content-Type: application/json');
session_start();

// Validar sesión y rol
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require '../config.php';
$pdo = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if ($method === 'POST') {
        // Validar campos obligatorios
        $required = ['id_vehiculo', 'fecha_mant', 'tipo_mant', 'costo'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new Exception("El campo '$field' es obligatorio.");
            }
        }

        $id_mantencion = $input['id_mantencion'] ?? null;
        $id_vehiculo = (int)$input['id_vehiculo'];
        $nombre_vehiculo = $input['nombre_vehiculo'] ?? ''; // Opcional: se puede obtener del vehículo
        $fecha_mant = $input['fecha_mant'];
        $kilometraje = !empty($input['kilometraje']) ? (int)$input['kilometraje'] : null;
        $tipo_mant = $input['tipo_mant'];
        $reparacion = $input['reparacion'] ?? null;
        $taller = $input['taller'] ?? null;
        $notas_mant = $input['notas_mant'] ?? null;
        $costo = (float)$input['costo'];

        // Verificar que el vehículo exista
        $stmt = $pdo->prepare("SELECT nombre_vehiculo FROM VEHICULO WHERE id_vehiculo = ?");
        $stmt->execute([$id_vehiculo]);
        $veh = $stmt->fetch();
        if (!$veh) {
            throw new Exception('Vehículo no encontrado.');
        }
        $nombre_vehiculo = $veh['nombre_vehiculo'];

        if ($id_mantencion) {
            // Actualizar
            $sql = "UPDATE MANTENCION SET 
                        id_vehiculo = ?, nombre_vehiculo = ?, fecha_mant = ?, kilometraje = ?, 
                        tipo_mant = ?, reparacion = ?, taller = ?, notas_mant = ?, costo = ?
                    WHERE id_mantencion = ?";
            $pdo->prepare($sql)->execute([
                $id_vehiculo, $nombre_vehiculo, $fecha_mant, $kilometraje,
                $tipo_mant, $reparacion, $taller, $notas_mant, $costo, $id_mantencion
            ]);
            echo json_encode(['success' => true, 'message' => 'Mantención actualizada exitosamente.']);
        } else {
            // Insertar
            $sql = "INSERT INTO MANTENCION (
                id_vehiculo, nombre_vehiculo, fecha_mant, kilometraje, 
                tipo_mant, reparacion, taller, notas_mant, costo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([
                $id_vehiculo, $nombre_vehiculo, $fecha_mant, $kilometraje,
                $tipo_mant, $reparacion, $taller, $notas_mant, $costo
            ]);
            echo json_encode(['success' => true, 'message' => 'Mantención registrada exitosamente.']);
        }

    } elseif ($method === 'DELETE') {
        $id_mantencion = $_GET['id'] ?? null;
        if (!$id_mantencion) {
            throw new Exception('ID de mantención no proporcionado.');
        }
        $pdo->prepare("DELETE FROM MANTENCION WHERE id_mantencion = ?")->execute([(int)$id_mantencion]);
        echo json_encode(['success' => true, 'message' => 'Registro eliminado exitosamente.']);

    } else {
        throw new Exception('Método no permitido.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>