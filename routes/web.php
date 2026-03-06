<?php
declare(strict_types=1);

/** Importamos los controladores para que el Router los encuentre */
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordRecoveryController;
use App\Modules\Professor\Controllers\Crear_Caso\Direcciones\LocationController;
use App\Modules\Professor\Controllers\Crear_Caso\CatalogController;
use App\Modules\Professor\Controllers\Casos\CasosController;

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
$router->get('/password-recovery', [PasswordRecoveryController::class, 'index']);
$router->post('/password-recovery', [PasswordRecoveryController::class, 'process']);


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
// 🎓 RUTAS DEL SIMULADOR (PROTEGIDAS - Requieren login)
// =============================================================================
$requireAuth = function () {
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    if (empty($_SESSION['logged_in'])) {
        header('Location: ' . base_url('/login'));
        exit;
    }
};

// Verificador de rol: redirige a /home si el rol no coincide
$requireRole = function (int $allowedRole) {
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $role = (int) ($_SESSION['role_id'] ?? 3);
    if ($role !== $allowedRole) {
        header('Location: ' . base_url('/home'));
        exit;
    }
};

$router->get('/simulador_index_antiguo', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/legacy/index_old');
});

// /home muestra el dashboard correcto según el rol
$router->get('/home', function () use ($app, $requireAuth) {
    $requireAuth();
    $role = (int) ($_SESSION['role_id'] ?? 3);
    return match ($role) {
        1 => $app->view('admin/home_admin'),
        2 => $app->view('professor/home_professor'),
        default => $app->view('student/home_st'),
    };
});

$router->get('/simulador_inicio', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/steps/step_01_seniat_index');
});
$router->get('/step_01_seniat_index', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/steps/step_01_seniat_index');
});
$router->get('/inscripcion_rif', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/legacy/inscripcion_rif');
});
$router->get('/consulta_rif', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/legacy/consulta_rif');
});
$router->get('/servicios_declaracion', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('simulator/steps/servicios_declaracion');
});

// Casos Sucesorales (Profesor)
$router->get('/casos-sucesorales', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return (new CasosController())->index();
});
$router->get('/casos-sucesorales/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    // TODO: Obtener datos reales del caso $id en el futuro
    return $app->view('professor/gestionar_caso', ['id' => $id]);
});
$router->get('/crear-caso', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/crear_caso');
});

// API: Guardar/Publicar caso
$router->post('/api/casos', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return (new CasosController())->store();
});

// API: Obtener JSON de un caso para edición
$router->get('/api/casos/{id}', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return (new CasosController())->show((int) $id);
});

// Perfil
// $router->get('/simulador_profile', fn() => $app->view('student/profile_st')); 
// $router->get('/perfil', fn() => $app->view('student/profile_st'));

// Ruta dinámica de prueba (puedes borrarla luego)
$router->get('/users/{id}', fn($id) => "Usuario: " . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8'));


// Rutas de API Profesor
$router->get('/api/estados', [LocationController::class, 'getEstados']);
$router->get('/api/municipios', [LocationController::class, 'getMunicipios']);
$router->get('/api/parroquias', [LocationController::class, 'getParroquias']);
$router->get('/api/ciudades', [LocationController::class, 'getCiudades']);
$router->get('/api/zonas-postales', [LocationController::class, 'getZonasPostales']);

// Rutas de Catálogos Dinámicos (Crear Caso)
$router->get('/api/unidades-tributarias', [CatalogController::class, 'getUnidadesTributarias']);
$router->get('/api/tipos-herencia', [CatalogController::class, 'getTiposHerencia']);
$router->get('/api/paises', [CatalogController::class, 'getPaises']);
$router->get('/api/parentescos', [CatalogController::class, 'getParentescos']);
$router->get('/api/tipos-bien-inmueble', [CatalogController::class, 'getTiposBienInmueble']);
$router->get('/api/categorias-bien-mueble', [CatalogController::class, 'getCategoriasBienMueble']);
$router->get('/api/tipos-bien-mueble', [CatalogController::class, 'getTiposBienMueble']);
$router->get('/api/bancos', [CatalogController::class, 'getBancos']);
$router->get('/api/empresas', [CatalogController::class, 'getEmpresas']);
$router->get('/api/tipos-semoviente', [CatalogController::class, 'getTiposSemoviente']);
$router->get('/api/tipos-pasivo-deuda', [CatalogController::class, 'getTiposPasivoDeuda']);
$router->get('/api/tipos-pasivo-gasto', [CatalogController::class, 'getTiposPasivoGasto']);
$router->get('/api/secciones-profesor', [CatalogController::class, 'getSeccionesProfesor']);
$router->get('/api/estudiantes-profesor', [CatalogController::class, 'getEstudiantesProfesor']);
$router->get('/api/buscar-empresa-rif', [CatalogController::class, 'buscarEmpresaPorRif']);