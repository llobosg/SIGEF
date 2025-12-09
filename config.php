<?php
// config.php - Conexión a base de datos Railway usando endpoint privado

// Detectar si estamos en Railway (existe RAILWAY_ENVIRONMENT)
if (getenv('RAILWAY_ENVIRONMENT')) {
    // Entorno Railway: usar variables privadas
    $host     = getenv('MYSQLHOST');
    $port     = getenv('MYSQLPORT');
    $dbname   = 'railway'; // ¡Forzamos el uso de la base 'railway'!
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
} else {
    // Entorno local (XAMPP, etc.): valores por defecto o personalizados
    $host     = 'localhost';
    $port     = '3306';
    $dbname   = 'railway'; // o 'SIGEF' si usas otro nombre localmente
    $username = 'root';
    $password = '';
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error: No se pudo conectar a la base de datos.");
}
?>