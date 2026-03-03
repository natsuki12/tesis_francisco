<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\App;
use App\Modules\Professor\Models\Crear_Caso\Direcciones\DireccionesModel;

$app = new App(__DIR__); // Inicializar DB (Dotenv load)

try {
    $m = new DireccionesModel();
    var_dump($m->obtenerMunicipiosPorEstado(3)); // Fail case 1 in logs
    var_dump($m->obtenerMunicipiosPorEstado(6)); // Fail case 2 in logs
} catch (\Throwable $e) {
    echo "ERROR CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
