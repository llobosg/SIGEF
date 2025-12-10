<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') exit;

require '../config.php';
$pdo = getDBConnection();

// Eliminar
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM PERSONAL WHERE id_personal = ?")->execute([(int)$_GET['delete']]);
    header("Location: personas_view.php");
    exit;
}

// Guardar o actualizar
if ($_POST) {
    $id = $_POST['id_personal'] ?? null;
    $rut = trim($_POST['rut']);
    $password = $_POST['password'] ?? null;

    // Validar RUT duplicado (excluyendo el actual si es edición)
    $stmt = $pdo->prepare("SELECT id_personal FROM PERSONAL WHERE rut = ?" . ($id ? " AND id_personal != ?" : ""));
    if ($id) {
        $stmt->execute([$rut, $id]);
    } else {
        $stmt->execute([$rut]);
    }
    if ($stmt->fetch()) {
        die('Error: RUT ya existe en el sistema.');
    }

    // Preparar datos
    $tipo_personal = $_POST['tipo_personal'] ?? null;
    $tipo_licencia = ($tipo_personal === 'Chofer') ? ($_POST['tipo_licencia'] ?? null) : null;
    $fecha_venc_lic = ($tipo_personal === 'Chofer') ? ($_POST['fecha_venc_lic'] ?: null) : null;

    // Verificar si el estado cambió para actualizar fecha_estado
    $estado = $_POST['estado'];
    $fecha_estado = null;
    if ($id) {
        $actual = $pdo->prepare("SELECT estado FROM PERSONAL WHERE id_personal = ?");
        $actual->execute([$id]);
        $prev = $actual->fetch();
        if ($prev && $prev['estado'] !== $estado) {
            $fecha_estado = date('Y-m-d');
        }
    } else {
        $fecha_estado = date('Y-m-d'); // Primera vez
    }

    // Contraseña: solo si se proporciona
    $pwdHash = $password ? $password : ($id ? null : '1234'); // En producción, usar hash

    if ($id) {
        // Actualización
        $sql = "UPDATE PERSONAL SET 
                    rut = ?, nombre = ?, fecha_nac = ?, direccion = ?, comuna = ?, 
                    celular = ?, email = ?, rol = ?, tipo_personal = ?, tipo_licencia = ?, 
                    fecha_venc_lic = ?, estado = ?, " . ($fecha_estado ? "fecha_estado = ?, " : "") .
                "password = ? WHERE id_personal = ?";
        $params = [
            $rut, $_POST['nombre'], $_POST['fecha_nac'] ?: null, $_POST['direccion'], $_POST['comuna'],
            $_POST['celular'], $_POST['email'], $_POST['rol'], $tipo_personal, $tipo_licencia,
            $fecha_venc_lic, $estado
        ];
        if ($fecha_estado) $params[] = $fecha_estado;
        $params[] = $pwdHash;
        $params[] = $id;

        $pdo->prepare($sql)->execute($params);
    } else {
        // Inserción
        $pdo->prepare("INSERT INTO PERSONAL (
            rut, nombre, fecha_nac, direccion, comuna, celular, email, rol, tipo_personal,
            tipo_licencia, fecha_venc_lic, estado, fecha_estado, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([
            $rut, $_POST['nombre'], $_POST['fecha_nac'] ?: null, $_POST['direccion'], $_POST['comuna'],
            $_POST['celular'], $_POST['email'], $_POST['rol'], $tipo_personal, $tipo_licencia,
            $fecha_venc_lic, $estado, $fecha_estado, $pwdHash
        ]);
    }

    header("Location: personas_view.php");
    exit;
}