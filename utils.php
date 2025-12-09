<?php
// utils.php - Función de logging para diagnóstico
function log_debug($message) {
    $logFile = __DIR__ . '/logs/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] " . print_r($message, true) . "\n";
    
    // Escribir en archivo
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    
    // En desarrollo local, también mostrar en pantalla
    if (!getenv('RAILWAY_ENVIRONMENT')) {
        echo "<pre style='color:#d35400;'>[DEBUG] $logLine</pre>";
    }
}
?>