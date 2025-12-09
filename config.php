<?php
// config.php
function getDBConnection() {
    if (getenv('RAILWAY_ENVIRONMENT')) {
        $host = getenv('MYSQLHOST');
        $port = getenv('MYSQLPORT');
        $dbname = 'railway';
        $user = getenv('MYSQLUSER');
        $pass = getenv('MYSQLPASSWORD');
        error_log("config.php: usando entorno Railway");
    } else {
        $host = 'localhost';
        $port = '3306';
        $dbname = 'railway';
        $user = 'root';
        $pass = '';
        error_log("config.php: usando entorno local");
    }

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        error_log("config.php: conexión PDO creada exitosamente");
        return $pdo;
    } catch (PDOException $e) {
        error_log("config.php: ERROR al conectar: " . $e->getMessage());
        throw $e;
    }
}
?>