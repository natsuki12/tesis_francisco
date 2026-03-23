<?php
declare(strict_types=1);

/** Importamos los controladores para que el Router los encuentre */
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordRecoveryController;
use App\Modules\Auth\Controllers\CompletarPerfilController;
use App\Modules\Professor\Controllers\Crear_Caso\Direcciones\LocationController;
use App\Modules\Professor\Controllers\Crear_Caso\CatalogController;
use App\Modules\Professor\Controllers\Casos\CasosController;
use App\Modules\Admin\Controllers\DashboardController;
use App\Modules\Admin\Controllers\Usuarios\ProfesoresController;
use App\Modules\Admin\Controllers\Usuarios\EstudiantesController;
use App\Modules\Admin\Controllers\Academico\PeriodosController;
use App\Modules\Admin\Controllers\Academico\SeccionesController;
use App\Modules\Admin\Controllers\Configuracion\CatalogosController;
use App\Modules\Admin\Controllers\Configuracion\MarcoLegalController;
use App\Modules\Admin\Controllers\Configuracion\ParametrosController;
use App\Modules\Admin\Controllers\Monitoreo\BitacoraController;
use App\Modules\Admin\Controllers\Monitoreo\ReportesController;

/** @var \App\Core\App $app */
/** @var \App\Core\Router $router */

// =============================================================================
// 🛠️ RUTAS DE SISTEMA Y LANDING
// =============================================================================
$router->get('/health', fn() => 'OK');
$router->get('/', fn() => $app->view('landing/landing'));

// DEV: vista de ejemplo para previsualizar HTML durante desarrollo
$router->get('/ejemplo', fn() => require __DIR__ . '/../ejemplo_sucesion_html/ejemplo.php');


// =============================================================================
// 🔐 RUTAS DE AUTENTICACIÓN (LOGIN)
// =============================================================================
// GET muestra el formulario, POST procesa los datos
$router->get('/login', [LoginController::class, 'show']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/password-recovery', [PasswordRecoveryController::class, 'index']);
$router->post('/password-recovery', [PasswordRecoveryController::class, 'process']);

// Completar Perfil (primer login de profesores creados por admin)
$router->get('/completar-perfil', [CompletarPerfilController::class, 'index']);
$router->post('/completar-perfil', [CompletarPerfilController::class, 'guardar']);


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
    // Forzar completar perfil si tiene flag activo (excepto en la propia ruta)
    if (!empty($_SESSION['force_password_change'])) {
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        if (!str_contains($uri, '/completar-perfil')) {
            header('Location: ' . base_url('/completar-perfil'));
            exit;
        }
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

// /home muestra el dashboard correcto según el rol
$router->get('/home', function () use ($app, $requireAuth) {
    $requireAuth();
    $role = (int) ($_SESSION['role_id'] ?? 3);
    if ($role === 2) {
        return (new \App\Modules\Professor\Controllers\HomeController())->index($app);
    }
    return match ($role) {
        1 => (new DashboardController())->index(),
        default => (new \App\Modules\Student\Controllers\HomeController())->index($app),
    };
});

$router->get('/admin', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new DashboardController())->index();
});

// Admin -> Gestión de Usuarios -> Profesores
$router->get('/admin/profesores', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ProfesoresController())->index();
});
$router->post('/admin/profesores/guardar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ProfesoresController())->guardar();
});
$router->post('/admin/profesores/eliminar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ProfesoresController())->eliminar();
});

// Admin -> Gestión de Usuarios -> Estudiantes
$router->get('/admin/estudiantes', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new EstudiantesController())->index();
});

// Admin -> Gestión Académica -> Períodos
$router->get('/admin/periodos', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new PeriodosController())->index();
});

// Admin -> Gestión Académica -> Secciones
$router->get('/admin/secciones', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new SeccionesController())->index();
});

// Admin -> Configuración -> Catálogos
$router->get('/admin/configuracion/catalogos', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new CatalogosController())->index();
});

// Admin -> Configuración -> Marco Legal
$router->get('/admin/configuracion/marco-legal', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new MarcoLegalController())->index();
});

// Admin -> Configuración -> Parámetros
$router->get('/admin/configuracion/parametros', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ParametrosController())->index();
});

// Admin -> Configuración -> Backup Manual
$router->post('/admin/configuracion/backup', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ParametrosController())->backup();
});

// Admin -> Configuración -> Descargar Backup
$router->get('/admin/configuracion/backup/descargar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ParametrosController())->descargar();
});

// Admin -> Configuración -> Eliminar Backup
$router->post('/admin/configuracion/backup/eliminar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ParametrosController())->eliminarBackup();
});

// Admin -> Configuración -> Guardar Config Respaldo
$router->post('/admin/configuracion/parametros/guardar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ParametrosController())->guardarConfig();
});

// Admin -> Monitoreo -> Bitácora
$router->get('/admin/monitoreo/bitacora', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new BitacoraController())->index();
});

// Admin -> Monitoreo -> Reportes
$router->get('/admin/monitoreo/reportes', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return (new ReportesController())->index();
});

// ─── Simulador SENIAT (session-based) ─────────────────────
// El asignacion_id se guarda en $_SESSION['sim_asignacion_id']
// al iniciar/continuar un intento desde detalle_asignacion.

$requireSimSession = function () {
    if (empty($_SESSION['sim_asignacion_id'])) {
        $_SESSION['flash_error'] = 'No has seleccionado una asignación. Acceso no autorizado.';
        header('Location: ' . base_url('/mis-asignaciones'));
        exit;
    }
};

$requireSeniatLogin = function () {
    if (empty($_SESSION['sim_seniat_logged_in'])) {
        header('Location: ' . base_url('/simulador/servicios_declaracion'));
        exit;
    }
};

$router->get('/simulador', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return $app->view('simulator/legacy/seniat_index_old');
});

$router->get('/simulador/consulta-rif', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return $app->view('simulator/legacy/consulta_rif');
});

$router->get('/simulador/inscripcion-rif', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return (new \App\Modules\Simulator\Controllers\InscripcionRifController())->index($app);
});

$enforceInscripcionReferer = function () {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $path = parse_url($referer, PHP_URL_PATH) ?? '';
    if (empty($referer) || strpos($path, '/simulador/inscripcion-rif') === false) {
        header('Location: ' . base_url('/simulador/inscripcion-rif'));
        exit;
    }
};

$datosBasicosHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();
    return (new \App\Modules\Simulator\Controllers\InscripcionRifController())->datosBasicos($app);
};

$router->get('/simulador/inscripcion-rif/datos-basicos', $datosBasicosHandler);
$router->post('/simulador/inscripcion-rif/datos-basicos', $datosBasicosHandler);

$direccionesHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();
    return (new \App\Modules\Simulator\Controllers\InscripcionRifController())->direcciones($app);
};

$router->get('/simulador/inscripcion-rif/direcciones', $direccionesHandler);
$router->post('/simulador/inscripcion-rif/direcciones', $direccionesHandler);

$relacionesHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();
    return (new \App\Modules\Simulator\Controllers\InscripcionRifController())->relaciones($app);
};

$router->get('/simulador/inscripcion-rif/relaciones', $relacionesHandler);
$router->post('/simulador/inscripcion-rif/relaciones', $relacionesHandler);

$validarInscripcionHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();
    return (new \App\Modules\Simulator\Controllers\InscripcionRifController())->validarInscripcion($app);
};

$router->get('/simulador/inscripcion-rif/validar-inscripcion', $validarInscripcionHandler);
$router->post('/simulador/inscripcion-rif/validar-inscripcion', $validarInscripcionHandler);

$router->get('/simulador/servicios_declaracion', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return (new \App\Modules\Simulator\Controllers\SeniatAuthController())->serviciosDeclaracion($app);
});

// POST: Login SENIAT simulado
$router->post('/simulador/servicios_declaracion/login', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    (new \App\Modules\Simulator\Controllers\SeniatAuthController())->login();
});

$router->get('/simulador/registro/contribuyente', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return (new \App\Modules\Simulator\Controllers\RegistroContribuyenteController())->index($app);
});

// POST: Validar RIF del paso 1 y marcar sesión para acceder al paso 2
$router->post('/simulador/registro/contribuyente/validar', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    (new \App\Modules\Simulator\Controllers\RegistroContribuyenteController())->validar();
});

$router->get('/simulador/registro/contribuyente/paso-2', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return (new \App\Modules\Simulator\Controllers\RegistroContribuyenteController())->paso2($app);
});

// POST: Guardar usuario y clave en sim_intentos
$router->post('/simulador/registro/contribuyente/paso-2/guardar', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    (new \App\Modules\Simulator\Controllers\RegistroContribuyenteController())->guardarPaso2();
});
$router->get('/simulador/portal', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return $app->view('simulator/seniat_actual/seniat_index_new');
});

$router->get('/simulador/sucesion/bienes_muebles/banco', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\BancoController();
    return $controller->index($app);
});

$router->get('/simulador/sucesion/bienes_muebles/seguro', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\SeguroController();
    return $controller->index($app);
});

$router->get('/simulador/sucesion/bienes_muebles/transporte', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\TransporteController();
    return $controller->index($app);
});

$router->get('/simulador/sucesion/prorrogas', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\ProrrogaController();
    return $controller->index($app);
});

$router->get('/simulador/sucesion/herederos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\HerederosController())->index($app);
});

$router->get('/simulador/sucesion/herencia', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\HerenciaController())->index($app);
});

// ── Herederos Premuerto ──
$router->get('/simulador/sucesion/herederos_premuerto', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\SucesionController();
    return $ctrl->herederos_premuerto();
});

// ── Bienes Inmuebles ──
$router->get('/simulador/sucesion/bienes_inmuebles', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\BienesInmueblesController())->index($app);
});

// ── Bienes Muebles ──
// NOTA: seguro y transporte ya están definidos arriba con sus controladores (SeguroController, TransporteController)
$router->get('/simulador/sucesion/bienes_muebles/opciones_compra', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\OpcionesCompraController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/cuentas_efectos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\CuentasEfectosController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/semovientes', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\SemovientesController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/bonos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\BonosController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/acciones', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\AccionesController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/prestaciones_sociales', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\PrestacionesSocialesController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/caja_ahorro', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\CajaAhorroController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/plantaciones', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\PlantacionesController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/bienes_muebles/otros', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\OtrosController();
    return $controller->index($app);
});

// ── Pasivos Deuda ──
$router->get('/simulador/sucesion/pasivos_deuda/tarjetas_credito', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\TarjetasCreditoController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/pasivos_deuda/credito_hipotecario', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\CreditoHipotecarioController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/pasivos_deuda/prestamos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\PrestamosController();
    return $controller->index($app);
});
$router->get('/simulador/sucesion/pasivos_deuda/otros', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\PasivosOtrosController();
    return $controller->index($app);
});

// ── Pasivos Gastos ──
$router->get('/simulador/sucesion/pasivos_gastos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\PasivosGastosController();
    return $controller->index($app);
});

// ── Desgravámenes ──
$router->get('/simulador/sucesion/desgravamenes', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\DesgravamenesController())->index($app);
});

// ── Exenciones ──
$router->get('/simulador/sucesion/exenciones', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\ExencionesController();
    return $controller->index($app);
});

// ── Exoneraciones ──
$router->get('/simulador/sucesion/exoneraciones', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $controller = new \App\Modules\Simulator\Controllers\ExoneracionesController();
    return $controller->index($app);
});

// ── Bienes Litigiosos ──
$router->get('/simulador/sucesion/bienes_litigiosos', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\BienesLitigiososController())->index($app);
});

// ── Resumen Declaración ──
$router->get('/simulador/sucesion/resumen_declaracion', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\SucesionController();
    return $ctrl->resumen();
});

// ── Resumen Cálculo Manual ──
$router->get('/simulador/sucesion/resumen_calculo_manual', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\SucesionController();
    return $ctrl->calculoManual();
});

// ── Ver Declaración (Anverso) ──
$router->get('/simulador/sucesion/declaracion_anverso', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\SucesionController();
    return $ctrl->declaracionAnverso();
});

// ── Ver Declaración (Reverso) ──
$router->get('/simulador/sucesion/declaracion_reverso', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\SucesionController();
    return $ctrl->declaracionReverso();
});

// ── PDF Comparación (Declaración) ──
$router->get('/simulador/sucesion/declaracion_pdf', function () use ($requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    $ctrl = new \App\Modules\Simulator\Controllers\PdfReportController();
    return $ctrl->generar();
});

$router->get('/simulador/sucesion/principal', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return (new \App\Modules\Simulator\Controllers\SucesionController())->principal();
});

$router->get('/simulador/servicios_declaracion/sistemas', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return $app->view('simulator/seniat_actual/acceder_sistemas');
});

$router->get('/simulador/servicios_declaracion/dashboard', function () use ($app, $requireAuth, $requireSimSession, $requireSeniatLogin) {
    $requireAuth();
    $requireSimSession();
    $requireSeniatLogin();
    return $app->view('simulator/seniat_actual/dashboard/sistemas_dashboard');
});

// Cerrar sesión SENIAT simulada (no cierra la sesión de la app)
$router->get('/simulador/servicios_declaracion/logout', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    (new \App\Modules\Simulator\Controllers\SeniatAuthController())->logout();
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
    return (new CasosController())->gestionar((int) $id);
});
$router->get('/crear-caso', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/crear_caso');
});

// Entregas (Profesor)
$router->get('/entregas', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/entregas', [
        'entregas' => [
            [
                'id' => 101,
                'estudiante_nombres' => 'Ana María',
                'estudiante_apellidos' => 'Martínez López',
                'estudiante_cedula' => '28456789',
                'estudiante_nacionalidad' => 'V',
                'seccion' => '4to A',
                'caso_titulo' => 'Sucesión González Méndez',
                'asignacion_nombre' => 'Evaluación parcial 1',
                'intento_actual' => 2,
                'intento_max' => 3,
                'created_at' => '2026-03-07 14:20:00',
                'estado' => 'Enviado',
            ],
            [
                'id' => 102,
                'estudiante_nombres' => 'Pedro José',
                'estudiante_apellidos' => 'López Ramírez',
                'estudiante_cedula' => '27123456',
                'estudiante_nacionalidad' => 'V',
                'seccion' => '4to A',
                'caso_titulo' => 'Sucesión González Méndez',
                'asignacion_nombre' => 'Evaluación parcial 1',
                'intento_actual' => 1,
                'intento_max' => 3,
                'created_at' => '2026-03-08 10:15:00',
                'estado' => 'Calificado',
            ],
            [
                'id' => 103,
                'estudiante_nombres' => 'María José',
                'estudiante_apellidos' => 'García Herrera',
                'estudiante_cedula' => '29876543',
                'estudiante_nacionalidad' => 'V',
                'seccion' => '4to B',
                'caso_titulo' => 'Sucesión Pérez Alvarado',
                'asignacion_nombre' => 'Evaluación parcial 2',
                'intento_actual' => 1,
                'intento_max' => 3,
                'created_at' => '2026-03-09 09:30:00',
                'estado' => 'En Progreso',
            ],
        ],
        'stats' => [
            'pendientes' => 1,
            'en_progreso' => 1,
            'calificadas' => 1,
            'total' => 3,
        ],
    ]);
});

// Marco Legal (Compartido: Profesor + Estudiante)
$router->get('/marco-legal', function () use ($app, $requireAuth) {
    $requireAuth();
    $role = (int) ($_SESSION['role_id'] ?? 3);
    if (!in_array($role, [2, 3])) {
        header('Location: ' . base_url('/home'));
        exit;
    }
    return $app->view('shared/marco_legal');
});

// Generación de R.S. (Profesor)
$router->get('/generacion-rs', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/generacion_rs');
});

// Mis Estudiantes (Profesor)
$router->get('/mis-estudiantes', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/mis_estudiantes');
});

$router->get('/mis-estudiantes/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/detalle_estudiante');
});

// Calificaciones (Profesor)
$router->get('/calificaciones', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/calificaciones');
});

// Historial (Profesor)
$router->get('/historial', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/historial');
});

// Detalle de Intento (Profesor)
$router->get('/entregas/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return $app->view('professor/detalle_intento');
});

// ═══════════════════════════════════════════════════
// RUTAS ESTUDIANTE (role 3)
// ═══════════════════════════════════════════════════

// Mis Asignaciones (Estudiante)
$router->get('/mis-asignaciones', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    return (new \App\Modules\Student\Controllers\AsignacionesEstudianteController())->index($app);
});

// Detalle de Asignación (Estudiante)
$router->get('/mis-asignaciones/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    return (new \App\Modules\Student\Controllers\AsignacionesEstudianteController())->show((int) $id, $app);
});

// Historial / Planillas (Estudiante)
$router->get('/historial-planillas', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    return $app->view('student/historial_st');
});

// Mis Calificaciones (Estudiante)
$router->get('/mis-calificaciones', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    return $app->view('student/mis_calificaciones');
});

// Detalle de Corrección (Estudiante)
$router->get('/mis-calificaciones/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    return $app->view('student/detalle_correccion');
});

// Perfil (Compartido: Profesor + Estudiante)
$router->get('/perfil', function () use ($app, $requireAuth) {
    $requireAuth();
    return $app->view('shared/perfil');
});

// ─── API Intentos (Estudiante) ────────────────────────────

// Iniciar nuevo intento o retomar activo
$router->post('/api/intentos/iniciar', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\IntentosController();
    $ctrl->iniciar();
});

// ── API: Herederos Premuertos CRUD ──
$router->post('/api/herederos-premuertos/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\HerederosPremuertosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/herederos-premuertos/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\HerederosPremuertosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/herederos-premuertos/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\HerederosPremuertosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Bienes Inmuebles CRUD ──
$router->post('/api/bienes-inmuebles/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BienesInmueblesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/bienes-inmuebles/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BienesInmueblesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/bienes-inmuebles/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BienesInmueblesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Banco (Bienes Muebles) CRUD ──
$router->post('/api/banco/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BancoController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/banco/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BancoController();
    $ctrl->editar((int) $id);
});
$router->post('/api/banco/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BancoController();
    $ctrl->eliminar((int) $id);
});

// ── API: Opciones de Compra (Bienes Muebles) CRUD ──
$router->post('/api/opciones-compra/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OpcionesCompraController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/opciones-compra/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OpcionesCompraController();
    $ctrl->editar((int) $id);
});
$router->post('/api/opciones-compra/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OpcionesCompraController();
    $ctrl->eliminar((int) $id);
});

// ── API: Cuentas y Efectos por Cobrar (Bienes Muebles) CRUD ──
$router->post('/api/cuentas-efectos/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CuentasEfectosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/cuentas-efectos/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CuentasEfectosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/cuentas-efectos/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CuentasEfectosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Semovientes (Bienes Muebles) CRUD ──
$router->post('/api/semovientes/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SemovientesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/semovientes/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SemovientesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/semovientes/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SemovientesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Bonos (Bienes Muebles) CRUD ──
$router->post('/api/bonos/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BonosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/bonos/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BonosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/bonos/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\BonosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Seguro (Bienes Muebles) CRUD ──
$router->post('/api/seguro/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SeguroController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/seguro/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SeguroController();
    $ctrl->editar((int) $id);
});
$router->post('/api/seguro/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\SeguroController();
    $ctrl->eliminar((int) $id);
});

// ── API: Búsqueda de RIF (compartida por todos los módulos) ──
$router->post('/api/buscar-rif', function () use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\RifController();
    $ctrl->buscarRif();
});

// ── API: Acciones (Bienes Muebles) CRUD ──
$router->post('/api/acciones/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\AccionesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/acciones/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\AccionesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/acciones/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\AccionesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Prestaciones Sociales (Bienes Muebles) CRUD ──
$router->post('/api/prestaciones-sociales/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestacionesSocialesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/prestaciones-sociales/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestacionesSocialesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/prestaciones-sociales/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestacionesSocialesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Caja de Ahorro (Bienes Muebles) CRUD ──
$router->post('/api/caja-ahorro/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CajaAhorroController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/caja-ahorro/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CajaAhorroController();
    $ctrl->editar((int) $id);
});
$router->post('/api/caja-ahorro/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CajaAhorroController();
    $ctrl->eliminar((int) $id);
});

// ── API: Plantaciones (Bienes Muebles) CRUD ──
$router->post('/api/plantaciones/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PlantacionesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/plantaciones/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PlantacionesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/plantaciones/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PlantacionesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Otros (Bienes Muebles) CRUD ──
$router->post('/api/otros/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OtrosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/otros/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OtrosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/otros/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\OtrosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Transporte (Bienes Muebles) CRUD ──
$router->post('/api/transporte/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TransporteController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/transporte/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TransporteController();
    $ctrl->editar((int) $id);
});
$router->post('/api/transporte/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TransporteController();
    $ctrl->eliminar((int) $id);
});

// ── API: Prórrogas CRUD ──
$router->post('/api/prorrogas/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ProrrogaController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/prorrogas/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ProrrogaController();
    $ctrl->editar((int) $id);
});
$router->post('/api/prorrogas/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ProrrogaController();
    $ctrl->eliminar((int) $id);
});

// ── API: Tarjetas de Crédito (Pasivos Deuda) CRUD ──
$router->post('/api/tarjetas_credito/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TarjetasCreditoController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/tarjetas_credito/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TarjetasCreditoController();
    $ctrl->editar((int) $id);
});
$router->post('/api/tarjetas_credito/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\TarjetasCreditoController();
    $ctrl->eliminar((int) $id);
});

// ── API: Crédito Hipotecario (Pasivos Deuda) CRUD ──
$router->post('/api/credito_hipotecario/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CreditoHipotecarioController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/credito_hipotecario/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CreditoHipotecarioController();
    $ctrl->editar((int) $id);
});
$router->post('/api/credito_hipotecario/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\CreditoHipotecarioController();
    $ctrl->eliminar((int) $id);
});

// ── API: Préstamos, Cuentas y Efectos por Pagar (Pasivos Deuda) CRUD ──
$router->post('/api/prestamos/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestamosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/prestamos/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestamosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/prestamos/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PrestamosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Pasivos Deuda → Otros CRUD ──
$router->post('/api/pasivos_otros/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosOtrosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/pasivos_otros/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosOtrosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/pasivos_otros/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosOtrosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Pasivos Gastos CRUD ──
$router->post('/api/pasivos_gastos/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosGastosController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/pasivos_gastos/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosGastosController();
    $ctrl->editar((int) $id);
});
$router->post('/api/pasivos_gastos/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\PasivosGastosController();
    $ctrl->eliminar((int) $id);
});

// ── API: Exenciones CRUD ──
$router->post('/api/exenciones/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExencionesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/exenciones/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExencionesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/exenciones/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExencionesController();
    $ctrl->eliminar((int) $id);
});

// ── API: Exoneraciones CRUD ──
$router->post('/api/exoneraciones/{id}/agregar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExoneracionesController();
    $ctrl->agregar((int) $id);
});
$router->post('/api/exoneraciones/{id}/editar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExoneracionesController();
    $ctrl->editar((int) $id);
});
$router->post('/api/exoneraciones/{id}/eliminar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    $ctrl = new \App\Modules\Simulator\Controllers\ExoneracionesController();
    $ctrl->eliminar((int) $id);
});

// Auto-save borrador (AJAX)
$router->post('/api/intentos/{id}/guardar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    (new \App\Modules\Simulator\Controllers\IntentosController())->guardar((int) $id);
});

// Validar borrador para generar R.S. y enviar resultados por correo
$router->post('/api/intentos/{id}/validar-rs', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    (new \App\Modules\Simulator\Controllers\IntentosController())->validarRs((int) $id);
});

// Enviar intento
$router->post('/api/intentos/{id}/enviar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    (new \App\Modules\Simulator\Controllers\IntentosController())->enviar((int) $id);
});

// Cancelar intento
$router->post('/api/intentos/{id}/cancelar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    (new \App\Modules\Simulator\Controllers\IntentosController())->cancelar((int) $id);
});

// Salir del simulador (limpia sesión y redirige)
$router->get('/api/simulador/salir', function () use ($requireAuth) {
    $requireAuth();
    (new \App\Modules\Simulator\Controllers\IntentosController())->salir();
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

// API: Eliminar un caso permanentemente
$router->delete('/api/casos/{id}', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return (new CasosController())->destroy((int) $id);
});

// API: Cambiar estado de un caso (Inactivar)
$router->patch('/api/casos/{id}/estado', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    return (new CasosController())->updateEstado((int) $id);
});

// API: CRUD Asignaciones (Configs)
use App\Modules\Professor\Controllers\Asignaciones\AsignacionesController;

$router->get('/api/casos/{id}/configs', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->index((int) $id);
});
$router->post('/api/casos/{id}/configs', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->store((int) $id);
});
$router->patch('/api/configs/{id}', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->update((int) $id);
});
$router->delete('/api/configs/{id}', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->destroy((int) $id);
});
$router->post('/api/configs/{id}/estudiantes', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->addEstudiantes((int) $id);
});
$router->delete('/api/configs/{id}/estudiantes/{aid}', function ($id, $aid) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->removeEstudiante((int) $id, (int) $aid);
});
$router->get('/api/casos/{id}/estudiantes-disponibles', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(2);
    (new AsignacionesController())->estudiantesDisponibles((int) $id);
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
$router->get('/api/tarifas-sucesion', [CatalogController::class, 'getTarifasSucesion']);
$router->get('/api/secciones-profesor', [CatalogController::class, 'getSeccionesProfesor']);
$router->get('/api/estudiantes-profesor', [CatalogController::class, 'getEstudiantesProfesor']);
$router->get('/api/buscar-empresa-rif', [CatalogController::class, 'buscarEmpresaPorRif']);
$router->get('/api/buscar-persona', [CatalogController::class, 'buscarPersonaPorCedula']);