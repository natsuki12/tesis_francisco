<?php
declare(strict_types=1);

/** Importamos los controladores para que el Router los encuentre */
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\LoginController;

/** @var \App\Core\App $app */
/** @var \App\Core\Router $router */

// =============================================================================
// 🛠️ RUTAS DE SISTEMA Y LANDING
// =============================================================================
$router->get('/health', fn() => 'OK');
$router->get('/', fn() => $app->view('landing/landing'));


// =============================================================================
// 🔐 RUTAS DE AUTENTICACIÓN (LOGIN)
// =============================================================================
// GET muestra el formulario, POST procesa los datos
$router->get('/login', [LoginController::class, 'show']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);


// =============================================================================
// 📝 RUTAS DE REGISTRO (CONTROLADOR DE PASOS)
// =============================================================================
// IMPORTANTE: Ya no usamos /register ni /register_part_2 por separado.
// El RegisterController decide qué mostrar en la ruta única: /registro

// 1. Mostrar el formulario (Paso 1 o Paso 2 según estado de sesión)
$router->get('/registro', [RegisterController::class, 'index']);

// 2. Procesar los formularios (Recibe datos del Paso 1 o Código del Paso 2)
$router->post('/registro', [RegisterController::class, 'process']);

// 3. Botón "Regresar" (Para corregir correo si se equivocaron en el paso 1)
$router->get('/registro/atras', [RegisterController::class, 'back']);

// 4. Ruta temporal para pruebas - Ir directamente al Paso 3 (Datos personales)
$router->get('/registro/parte3', [RegisterController::class, 'showPart3']);


// =============================================================================
// 🎓 RUTAS DEL SIMULADOR
// =============================================================================
// =============================================================================
// 🎓 RUTAS DEL SIMULADOR
// =============================================================================
$router->get('/simulador_index_antiguo', fn() => $app->view('simulator/legacy/index_old'));
$router->get('/home', fn() => $app->view('student/home_st'));
$router->get('/simulador_inicio', fn() => $app->view('simulator/steps/step_01_seniat_index'));

// Perfil
// $router->get('/simulador_profile', fn() => $app->view('student/profile_st')); 
// $router->get('/perfil', fn() => $app->view('student/profile_st'));

// Ruta dinámica de prueba (puedes borrarla luego)
$router->get('/users/{id}', fn($id) => "Usuario: " . htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'));