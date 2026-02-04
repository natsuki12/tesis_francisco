<?php
// sistema/test.php

// Incluimos el archivo de configuración que creamos antes
require_once '../src/Core/DB.php';

try {
    // Si el script llega aquí, significa que db.php conectó bien
    // (porque si fallara, db.php mata el proceso con die())
    
    echo "<div style='color:green; font-weight:bold; font-family:sans-serif; padding:20px;'>";
    echo "✅ ¡ÉXITO TOTAL! <br>";
    echo "Conectado a la base de datos: " . getenv('DB_NAME');
    echo "</div>";

} catch (Exception $e) {
    echo "Algo raro pasó: " . $e->getMessage();
}
?>