<?php
// config.php
function getDBConnection() {
    require_once __DIR__ . '/../utils.php';

    if (getenv('RAILWAY_ENVIRONMENT')) {
        $host = getenv('MYSQLHOST');
        $port = getenv('MYSQLPORT');
        $dbname = 'railway';
        $user = getenv('MYSQLUSER');
        $pass = getenv('MYSQLPASSWORD');
        log_debug("config.php: usando entorno Railway");
    } else {
        $host = 'localhost';
        $port = '3306';
        $dbname = 'railway';
        $user = 'root';
        $pass = '';
        log_debug("config.php: usando entorno local");
    }

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        log_debug("config.php: conexión PDO creada exitosamente");
        return $pdo;
    } catch (PDOException $e) {
        log_debug("config.php: ERROR al conectar: " . $e->getMessage());
        throw $e;
    }
}
?>