<?php
error_log("[AUTH] Entrando a auth.php - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));
error_log("[AUTH] POST data: " . print_r($_POST, true));

session_start();
error_log("[AUTH] Sesión iniciada. ID de sesión: " . session_id());

//require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_debug("auth.php: acceso directo (no POST). Redirigiendo a login.php");
    header("Location: login.php");
    exit;
}

$rut = trim($_POST['rut'] ?? '');
$password = $_POST['password'] ?? '';

log_debug("auth.php: credenciales recibidas - RUT: '$rut', Password: " . ($password ? '***' : 'VACÍO'));

if (!$rut || !$password) {
    log_debug("auth.php: faltan credenciales");
    header("Location: login.php?error=1");
    exit;
}

try {
    $pdo = getDBConnection();
    log_debug("auth.php: conexión a DB establecida");

    $stmt = $pdo->prepare("SELECT id_personal, nombre, rol FROM PERSONAL WHERE rut = ? AND password = ?");
    $stmt->execute([$rut, $password]);
    $user = $stmt->fetch();

    if ($user) {
        log_debug("auth.php: usuario encontrado: " . json_encode($user));
        $_SESSION['user_id'] = $user['id_personal'];
        $_SESSION['user'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        if ($user['rol'] === 'admin') {
            log_debug("auth.php: redirigiendo a admin dashboard");
            header("Location: pages/vehiculos_view.php");
        } else {
            log_debug("auth.php: redirigiendo a dashboard básico");
            header("Location: pages/dashboard_basico.php");
        }
        exit;
    } else {
        log_debug("auth.php: usuario NO encontrado (credenciales inválidas)");
        header("Location: login.php?error=1");
        exit;
    }
} catch (Exception $e) {
    log_debug("auth.php: ERROR EN BASE DE DATOS: " . $e->getMessage());
    // En producción, no muestres el error
    header("Location: login.php?error=1");
    exit;
}
?>