<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}
require '../config.php';

$pdo = getDBConnection();

// Eliminar registro
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM MONTO WHERE id_monto = ?")->execute([(int)$_GET['delete']]);
        header("Location: /pages/monto_view.php?msg=delete_success");
        exit;
    } catch (Exception $e) {
        error_log("Error al eliminar monto: " . $e->getMessage());
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_monto'] ?? null;
    $id_vehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $nombre_vehiculo = trim($_POST['nombre_vehiculo'] ?? '');
    $tipo_monto = $_POST['tipo_monto'] ?? '';
    $tipo_personal = $_POST['tipo_personal'] ?? '';
    $monto = (float)($_POST['monto'] ?? 0);

    // Validaciones
    if (!$nombre_vehiculo || !$tipo_monto || !$tipo_personal || !$monto) {
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }

    try {
        // En monto_logic.php
    if ($id) {
        // Actualizar
        $pdo->prepare("UPDATE MONTO SET 
            id_vehiculo = ?, 
            nombre_vehiculo = ?, 
            tipo_monto = ?, 
            tipo_personal = ?, 
            monto_p = ?,  // ← Nuevo campo
            monto_f = ?   // ← Nuevo campo
            WHERE id_monto = ?")
            ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto_p, $monto_f, $id]);
    } else {
        // Insertar
        $pdo->prepare("INSERT INTO MONTO (id_vehiculo, nombre_vehiculo, tipo_monto, tipo_personal, monto_p, monto_f) 
            VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto_p, $monto_f]);
    }
        header("Location: /pages/monto_view.php?msg=success");
        exit;
    } catch (Exception $e) {
        error_log("Error en monto_logic.php: " . $e->getMessage());
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }
}