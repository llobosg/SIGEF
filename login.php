<?php
session_start();
require_once 'utils.php';

log_debug("login.php: cargado. Parámetros GET: " . json_encode($_GET));

// Mostrar error si viene de auth.php fallido
$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - SIGEF</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="login-container">
        <img src="assets/logo.png" alt="Logo SIGEF" class="login-logo" onerror="this.style.display='none'">
        <h2><i class="fas fa-lock"></i> Acceso a SIGEF</h2>

        <?php if ($error): ?>
            <div class="error">❌ Usuario o contraseña incorrectos</div>
        <?php endif; ?>

        <form method="POST" action="auth.php">
            <input type="text" name="rut" placeholder="RUT" required />
            <input type="password" name="password" placeholder="Contraseña" required />
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>