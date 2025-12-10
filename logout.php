<?php
// logout.php - Cierra sesión y redirige al login

session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se usa una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir a login
header("Location: /login.php");
exit;
?>