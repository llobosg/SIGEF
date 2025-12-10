<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}
require '../config.php';

$pdo = getDBConnection();

// Eliminar
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM MONTO WHERE id_monto = ?")->execute([(int)$_GET['delete']]);
    header("Location: /pages/monto_view.php?msg=delete_success");
    exit;
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_monto'] ?? null;
    $tipo_monto = $_POST['tipo_monto'] ?? '';
    $tipo_personal = $_POST['tipo_personal'] ?? '';
    $monto = (int)($_POST['monto'] ?? 0);
    $id_vehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $nombre_vehiculo = trim($_POST['nombre_vehiculo'] ?? '');

    // Validar selección de vehículo
    if (!$id_vehiculo || !$nombre_vehiculo) {
        header("Location: /pages/monto_view.php?msg=error_vehiculo");
        exit;
    }

    try {
        if ($id) {
            // Actualizar
            $pdo->prepare("UPDATE MONTO SET 
                id_vehiculo = ?, 
                nombre_vehiculo = ?, 
                tipo_monto = ?, 
                tipo_personal = ?, 
                monto = ? 
                WHERE id_monto = ?")
                ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto, $id]);
        } else {
            // Insertar
            $pdo->prepare("INSERT INTO MONTO (id_vehiculo, nombre_vehiculo, tipo_monto, tipo_personal, monto) 
                VALUES (?, ?, ?, ?, ?)")
                ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto]);
        }
        header("Location: /pages/monto_view.php?msg=success");
        exit;
    } catch (Exception $e) {
        error_log("Error en monto_logic.php: " . $e->getMessage());
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }
}