<?php
// pages/monto_logic.php

require '../session_check.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    error_log("[MONTO_LOGIC] Acceso denegado: rol no es admin");
    header("Location: /pages/monto_view.php?msg=error");
    exit;
}

require '../config.php';

$pdo = getDBConnection();

// Eliminar registro
if (isset($_GET['delete'])) {
    error_log("[MONTO_LOGIC] Operación: ELIMINAR ID: " . $_GET['delete']);
    
    try {
        $result = $pdo->prepare("DELETE FROM MONTO WHERE id_monto = ?")->execute([(int)$_GET['delete']]);
        error_log("[MONTO_LOGIC] Eliminación resultado: " . ($result ? 'éxito' : 'fracaso'));
        header("Location: /pages/monto_view.php?msg=delete_success");
        exit;
    } catch (Exception $e) {
        error_log("[MONTO_LOGIC] Error al eliminar: " . $e->getMessage());
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[MONTO_LOGIC] Operación: GUARDAR/ACTUALIZAR");
    
    // Validar que lleguen todos los datos necesarios
    $required_fields = ['id_vehiculo', 'nombre_vehiculo', 'tipo_monto', 'tipo_personal', 'monto_p', 'monto_f'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("[MONTO_LOGIC] Campo requerido faltante: $field");
            error_log("[MONTO_LOGIC] POST completo: " . json_encode($_POST));
            header("Location: /pages/monto_view.php?msg=error");
            exit;
        }
    }

    $id = $_POST['id_monto'] ?? null;
    $id_vehiculo = (int)$_POST['id_vehiculo'];
    $nombre_vehiculo = trim($_POST['nombre_vehiculo']);
    $tipo_monto = trim($_POST['tipo_monto']);
    $tipo_personal = trim($_POST['tipo_personal']);
    $monto_p = !empty($_POST['monto_p']) ? (float)$_POST['monto_p'] : 0.00;
    $monto_f = !empty($_POST['monto_f']) ? (float)$_POST['monto_f'] : 0.00;

    // Validar que sean números válidos
    if ($monto_p < 0 || $monto_f < 0) {
        error_log("[MONTO_LOGIC] Valores negativos no permitidos");
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }

    error_log("[MONTO_LOGIC] Datos validados correctamente");
    error_log("[MONTO_LOGIC] ID: $id, Vehículo: $nombre_vehiculo, Monto_P: $monto_p, Monto_F: $monto_f");

    try {
        if ($id) {
            error_log("[MONTO_LOGIC] ACTUALIZANDO registro ID: $id");
            $result = $pdo->prepare("UPDATE MONTO SET 
                id_vehiculo = ?, 
                nombre_vehiculo = ?, 
                tipo_monto = ?, 
                tipo_personal = ?, 
                monto_p = ?, 
                monto_f = ?
                WHERE id_monto = ?")
                ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto_p, $monto_f, $id]);
            error_log("[MONTO_LOGIC] Actualización resultado: " . ($result ? 'éxito' : 'fracaso'));
        } else {
            error_log("[MONTO_LOGIC] INSERTANDO nuevo registro");
            $result = $pdo->prepare("INSERT INTO MONTO (id_vehiculo, nombre_vehiculo, tipo_monto, tipo_personal, monto_p, monto_f) 
                VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$id_vehiculo, $nombre_vehiculo, $tipo_monto, $tipo_personal, $monto_p, $monto_f]);
            error_log("[MONTO_LOGIC] Inserción resultado: " . ($result ? 'éxito' : 'fracaso'));
            error_log("[MONTO_LOGIC] ID insertado: " . $pdo->lastInsertId());
        }
        
        error_log("[MONTO_LOGIC] Redirigiendo a éxito");
        header("Location: /pages/monto_view.php?msg=success");
        exit;
        
    } catch (Exception $e) {
        error_log("[MONTO_LOGIC] Error en operación SQL: " . $e->getMessage());
        error_log("[MONTO_LOGIC] Error completo: " . $e->getTraceAsString());
        header("Location: /pages/monto_view.php?msg=error");
        exit;
    }
}

error_log("[MONTO_LOGIC] === FIN DE EJECUCIÓN (sin operación) ===");
header("Location: /pages/monto_view.php?msg=error");
exit;
?>