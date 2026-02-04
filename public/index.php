<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/Core/helpers.php';

use App\Core\App;

$app = new App(dirname(__DIR__));

$router = $app->router();

// Cargar rutas
require dirname(__DIR__) . '/routes/web.php';

// Ejecutar app
$app->run();
