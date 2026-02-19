<?php
namespace App\Modules\prueba;

use App\Core\Controller;
use App\Core\App;

class PruebaController extends Controller {

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function seniat() {
        // Ruta al archivo HTML en public/pruebas
        // __DIR__ es .../src/Modules/prueba
        // __DIR__ es .../src/Modules/prueba
        // Subimos 3 niveles para llegar a la raíz del proyecto
        $path = dirname(__DIR__, 3) . '/public/pruebas/index.html';
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            // Ajustar rutas relativas para que apunten a public/pruebas/
            // Usamos base_url() si está disponible, o construimos la ruta relativa
            // Asumimos que base_url() devuelve la URL base del proyecto (ej: http://localhost/tesis_francisco)
            $assetBase = base_url('/public/pruebas/');
            
            // Reemplazar enlaces CSS
            $content = str_replace('href="css/', 'href="' . $assetBase . 'css/', $content);
            $content = str_replace('href="./css/', 'href="' . $assetBase . 'css/', $content);
            
            // Reemplazar enlaces JS (si los hay)
            $content = str_replace('src="js/', 'src="' . $assetBase . 'js/', $content);
            $content = str_replace('src="./js/', 'src="' . $assetBase . 'js/', $content);
            
            // Reemplazar imágenes (si las hay)
            $content = str_replace('src="img/', 'src="' . $assetBase . 'img/', $content);
            $content = str_replace('src="./img/', 'src="' . $assetBase . 'img/', $content);

            echo $content;
        } else {
            http_response_code(404);
            echo "Error: El archivo de prueba no fue encontrado en " . $path;
        }
    }
}
