<!-- login.php -->
<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: " . ($_SESSION['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_basico.php'));
    exit;
}

$error = '';
if ($_POST) {
    $rut = trim($_POST['rut'] ?? '');
    $password = $_POST['password'] ?? '';

    require 'config.php';
    $stmt = $pdo->prepare("SELECT * FROM PERSONAL WHERE rut = ? AND password = ?");
    $stmt->execute([$rut, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id_personal'] = $user['id_personal'];
        header("Location: " . ($user['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_basico.php'));
        exit;
    } else {
        $error = "RUT o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - SIGEF</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-bg">
    <div class="login-container">
        <h2>SIGEF - Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="rut" placeholder="RUT" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>