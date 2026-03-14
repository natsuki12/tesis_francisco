<?php
declare(strict_types=1);

/** Importamos los controladores para que el Router los encuentre */
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordRecoveryController;
use App\Modules\Professor\Controllers\Crear_Caso\Direcciones\LocationController;
use App\Modules\Professor\Controllers\Crear_Caso\CatalogController;
use App\Modules\Professor\Controllers\Casos\CasosController;
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

// /home muestra el dashboard correcto según el rol
$router->get('/home', function () use ($app, $requireAuth) {
    $requireAuth();
    $role = (int) ($_SESSION['role_id'] ?? 3);
    if ($role === 2) {
        $homeModel = new \App\Modules\Professor\Models\HomeProfessorModel();
        $recentStudents = $homeModel->getRecentStudents(5);
        return $app->view('professor/home_professor', [
            'recentStudents' => $recentStudents,
        ]);
    }
    return match ($role) {
        1 => $app->view('admin/dashboard/home_admin'),
        default => (function () use ($app) {
                $model = new \App\Modules\Student\Models\StudentAssignmentModel();
                $estudianteId = $model->getEstudianteId((int) $_SESSION['user_id']);
                $draft = null;
                if ($estudianteId) {
                    $draft = $model->getUltimaAsignacionAccedida($estudianteId);
                }
                return $app->view('student/home_st', ['draft' => $draft]);
            })(),
    };
});

$router->get('/admin', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(1);
    return $app->view('admin/dashboard/home_admin');
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

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

    $intentoActivo = null;
    if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
        $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    return $app->view('simulator/legacy/inscripcion_rif', ['intento' => $intentoActivo]);
});

$enforceInscripcionReferer = function() {
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

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

    $intentoActivo = null;
    if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
        $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    // Bloquear si ya se generó el RIF Sucesoral
    if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
        header('Location: ' . base_url('/simulador'));
        exit;
    }

    return $app->view('simulator/legacy/formulario_inscripcion_rif/datos_causante', ['intento' => $intentoActivo]);
};

$router->get('/simulador/inscripcion-rif/datos-basicos', $datosBasicosHandler);
$router->post('/simulador/inscripcion-rif/datos-basicos', $datosBasicosHandler);

$direccionesHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

    $intentoActivo = null;
    if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
        $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
        header('Location: ' . base_url('/simulador'));
        exit;
    }

    return $app->view('simulator/legacy/formulario_inscripcion_rif/direcciones', ['intento' => $intentoActivo]);
};

$router->get('/simulador/inscripcion-rif/direcciones', $direccionesHandler);
$router->post('/simulador/inscripcion-rif/direcciones', $direccionesHandler);

$relacionesHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

    $intentoActivo = null;
    if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
        $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
        header('Location: ' . base_url('/simulador'));
        exit;
    }

    return $app->view('simulator/legacy/formulario_inscripcion_rif/relaciones', ['intento' => $intentoActivo]);
};

$router->get('/simulador/inscripcion-rif/relaciones', $relacionesHandler);
$router->post('/simulador/inscripcion-rif/relaciones', $relacionesHandler);

$validarInscripcionHandler = function () use ($app, $requireAuth, $requireSimSession, $enforceInscripcionReferer) {
    $requireAuth();
    $requireSimSession();
    $enforceInscripcionReferer();

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

    $intentoActivo = null;
    if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
        $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
        header('Location: ' . base_url('/simulador'));
        exit;
    }

    return $app->view('simulator/legacy/formulario_inscripcion_rif/validar_inscripcion', ['intento' => $intentoActivo]);
};

$router->get('/simulador/inscripcion-rif/validar-inscripcion', $validarInscripcionHandler);
$router->post('/simulador/inscripcion-rif/validar-inscripcion', $validarInscripcionHandler);

$router->get('/simulador/servicios_declaracion', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();

    // Obtener credenciales del intento activo para "¿Olvidó su información?"
    $usuarioSeniat = null;
    $passwordRif = null;
    try {
        if (!empty($_SESSION['sim_asignacion_id'])) {
            $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
            $intento = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($intento) {
                $usuarioSeniat = $intento['usuario_seniat'] ?? null;
                $passwordRif = $intento['password_rif'] ?? null;
            }
        }
    } catch (\Throwable $e) {
        error_log('servicios-declaracion: ' . $e->getMessage());
    }

    return $app->view('simulator/seniat_actual/servicios_declaracion', [
        'usuarioSeniat' => $usuarioSeniat,
        'passwordRif' => $passwordRif,
    ]);
});

$router->get('/simulador/registro/contribuyente', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();

    // Obtener el RIF asignado al estudiante desde su intento activo
    $rifAsignado = null;
    try {
        if (!empty($_SESSION['sim_asignacion_id'])) {
            $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
            $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
                $rifAsignado = $intentoActivo['rif_sucesoral'];
            }
        }
    } catch (\Throwable $e) {
        error_log('registro-contribuyente-get: ' . $e->getMessage());
    }

    return $app->view('simulator/seniat_actual/registro/registro_contribuyente', [
        'rifAsignado' => $rifAsignado
    ]);
});

// POST: Validar RIF del paso 1 y marcar sesión para acceder al paso 2
$router->post('/simulador/registro/contribuyente/validar', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    header('Content-Type: application/json');

    try {
        $rifIngresado = trim($_POST['rif'] ?? '');

        if (empty($rifIngresado)) {
            echo json_encode(['ok' => false, 'msg' => 'Debe ingresar un RIF.']);
            return;
        }

        // Obtener RIF asignado
        $rifAsignado = null;
        $intentoActivo = null;
        if (!empty($_SESSION['sim_asignacion_id'])) {
            $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
            $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
                $rifAsignado = $intentoActivo['rif_sucesoral'];
            }
        }

        if (!$rifAsignado) {
            echo json_encode(['ok' => false, 'msg' => 'No tiene un RIF asignado aún.']);
            return;
        }

        // Normalizar (quitar guiones) para comparar
        $normalizar = fn($rif) => str_replace('-', '', $rif);
        if ($normalizar($rifIngresado) !== $normalizar($rifAsignado)) {
            echo json_encode(['ok' => false, 'msg' => 'No hay ningún contribuyente registrado con esos datos.']);
            return;
        }

        // Verificar si ya posee un usuario registrado
        if (!empty($intentoActivo['usuario_seniat'])) {
            echo json_encode(['ok' => false, 'msg' => 'Ya posee un usuario registrado en el sistema.']);
            return;
        }

        // Marcar sesión como válida para acceder al paso 2
        $_SESSION['registro_paso1_ok'] = true;
        echo json_encode(['ok' => true]);
    } catch (\Throwable $e) {
        error_log('registro-validar: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor. Intente de nuevo.']);
    }
});

$router->get('/simulador/registro/contribuyente/paso-2', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();

    // Solo accesible si pasó la validación del paso 1
    if (empty($_SESSION['registro_paso1_ok'])) {
        header('Location: ' . base_url('/simulador/registro/contribuyente'));
        exit;
    }

    try {
        // Verificar si ya posee un usuario registrado (guard adicional)
        if (!empty($_SESSION['sim_asignacion_id'])) {
            $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
            $intento = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($intento && !empty($intento['usuario_seniat'])) {
                unset($_SESSION['registro_paso1_ok']);
                header('Location: ' . base_url('/simulador/registro/contribuyente'));
                exit;
            }
        }
    } catch (\Throwable $e) {
        error_log('registro-paso2-get: ' . $e->getMessage());
    }

    return $app->view('simulator/seniat_actual/registro/registro_contribuyente_2');
});

// POST: Guardar usuario y clave en sim_intentos
$router->post('/simulador/registro/contribuyente/paso-2/guardar', function () use ($requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    header('Content-Type: application/json');

    // Validar que pasó por el paso 1
    if (empty($_SESSION['registro_paso1_ok'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'msg' => 'Debe completar el paso 1 primero.']);
        return;
    }

    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');

    // Validaciones básicas
    if (empty($usuario) || empty($clave)) {
        echo json_encode(['ok' => false, 'msg' => 'Los campos Usuario y Clave son obligatorios.']);
        return;
    }

    if (strlen($clave) < 8) {
        echo json_encode(['ok' => false, 'msg' => 'La clave debe tener mínimo 8 caracteres.']);
        return;
    }

    // Guardar en la DB
    try {
        // Obtener intento activo
        $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
        $intento = $attemptModel->getIntentoActivo((int) ($_SESSION['sim_asignacion_id'] ?? 0));

        if (!$intento) {
            echo json_encode(['ok' => false, 'msg' => 'No se encontró un intento activo.']);
            return;
        }

        // Verificar que no tenga ya un usuario registrado
        if (!empty($intento['usuario_seniat'])) {
            echo json_encode(['ok' => false, 'msg' => 'Ya posee un usuario registrado.']);
            return;
        }
        $db = \App\Core\DB::connect();
        $stmt = $db->prepare("
            UPDATE sim_intentos
            SET usuario_seniat = :usuario,
                password_rif   = :clave,
                updated_at     = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'usuario' => $usuario,
            'clave'   => $clave,
            'id'      => (int) $intento['id'],
        ]);

        // Limpiar flag de sesión (el registro ya se completó)
        unset($_SESSION['registro_paso1_ok']);

        // Flash toast para mostrar en la siguiente página
        $_SESSION['flash_toast'] = [
            'type' => 'success',
            'msg'  => 'Se ha registrado exitosamente su usuario y clave de acceso.'
        ];

        echo json_encode([
            'ok' => true,
            'redirect' => base_url('/simulador/portal')
        ]);
    } catch (\PDOException $e) {
        // Duplicate key en usuario_seniat
        if ($e->getCode() == '23000') {
            echo json_encode(['ok' => false, 'msg' => 'El nombre de usuario ya está en uso. Elija otro.']);
        } else {
            error_log("registro-paso2: Error DB: " . $e->getMessage());
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar. Intente de nuevo.']);
        }
    }
});
$router->get('/simulador/portal', function () use ($app, $requireAuth, $requireSimSession) {
    $requireAuth();
    $requireSimSession();
    return $app->view('simulator/seniat_actual/seniat_index_new');
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
    $profesorId = (int) ($_SESSION['user_id'] ?? 0);
    $model = new \App\Modules\Professor\Models\Casos\GestionarCasoModel();
    $data = $model->getFullCaseById((int) $id, $profesorId);
    if (!$data) {
        http_response_code(404);
        return $app->view('errors/404');
    }
    return $app->view('professor/gestionar_caso', ['casoData' => $data]);
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

    $model = new \App\Modules\Student\Models\StudentAssignmentModel();
    $estudianteId = $model->getEstudianteId((int) $_SESSION['user_id']);

    $asignaciones = [];
    if ($estudianteId) {
        $asignaciones = $model->getAsignaciones($estudianteId);
    }

    return $app->view('student/mis_asignaciones', [
        'asignaciones' => $asignaciones,
    ]);
});

// Detalle de Asignación (Estudiante)
$router->get('/mis-asignaciones/{id}', function ($id) use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);

    $model = new \App\Modules\Student\Models\StudentAssignmentModel();
    $estudianteId = $model->getEstudianteId((int) $_SESSION['user_id']);

    if (!$estudianteId) {
        http_response_code(404);
        return $app->view('errors/404');
    }

    $asignacion = $model->getDetalleAsignacion((int) $id, $estudianteId);
    if (!$asignacion) {
        http_response_code(404);
        return $app->view('errors/404');
    }

    $intentos = $model->getIntentos((int) $id);

    return $app->view('student/detalle_asignacion', [
        'asignacion' => $asignacion,
        'intentos' => $intentos,
    ]);
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
$router->post('/api/intentos/iniciar', function () use ($app, $requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);

    $asignacionId = (int) ($_POST['asignacion_id'] ?? 0);
    if (!$asignacionId) {
        http_response_code(400);
        echo json_encode(['error' => 'asignacion_id requerido']);
        return;
    }

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();

    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);
    if (!$estudianteId) {
        http_response_code(403);
        echo json_encode(['error' => 'Estudiante no encontrado']);
        return;
    }

    // Si ya tiene uno activo, redirigir
    $activo = $attemptModel->getIntentoActivo($asignacionId);
    if ($activo) {
        $_SESSION['sim_asignacion_id'] = $asignacionId;
        header('Location: ' . base_url('/simulador'));
        return;
    }

    // Verificar si puede iniciar
    $check = $attemptModel->verificarPuedeIniciar($asignacionId, $estudianteId);
    if (!$check['ok']) {
        $_SESSION['flash_error'] = $check['razon'];
        header('Location: ' . base_url('/mis-asignaciones/' . $asignacionId));
        return;
    }

    // Crear intento
    $intento = $attemptModel->crearIntento($asignacionId);
    $_SESSION['sim_asignacion_id'] = $asignacionId;
    header('Location: ' . base_url('/simulador'));
});

// Auto-save borrador (AJAX)
$router->post('/api/intentos/{id}/guardar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        return;
    }

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();

    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);
    $intento = $attemptModel->getIntento((int) $id, $estudianteId);

    if (!$intento || $intento['estado'] !== 'En_Progreso') {
        http_response_code(403);
        echo json_encode(['error' => 'Intento no válido']);
        return;
    }

    $ok = $attemptModel->guardarBorrador(
        (int) $id,
        json_encode($input['borrador'] ?? [], JSON_UNESCAPED_UNICODE),
        (int) ($input['paso_actual'] ?? $intento['paso_actual']),
        (string) ($input['pasos_completados'] ?? $intento['pasos_completados'])
    );

    echo json_encode(['ok' => $ok]);
});

// Validar borrador para generar R.S. y enviar resultados por correo
$router->post('/api/intentos/{id}/validar-rs', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    header('Content-Type: application/json');

    try {
        $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
        $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

        if (!$estudianteId) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'errores' => ['general' => ['Estudiante no encontrado.']]]);
            return;
        }

        // ── Bloquear si ya se generó el RIF ──
        $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
        $intentoCheck = $attemptModel->getIntentoActivo((int) ($_SESSION['sim_asignacion_id'] ?? 0));
        if ($intentoCheck && !empty($intentoCheck['rif_sucesoral'])) {
            echo json_encode(['ok' => false, 'errores' => ['general' => ['El RIF Sucesoral ya fue generado para este intento.']]]);
            return;
        }

        // ── Obtener título real del caso y tipo_cedula del causante desde la DB ──
        $casoTitulo = 'Caso Sucesoral';
        $tipoCedulaCausante = 'V'; // Default
        try {
            $db = \App\Core\DB::connect();
            $stmtCaso = $db->prepare("
                SELECT ce.titulo, p.tipo_cedula
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a  ON a.id  = i.asignacion_id
                INNER JOIN sim_caso_configs cfg     ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce    ON ce.id  = cfg.caso_id
                INNER JOIN sim_personas p           ON p.id   = ce.causante_id
                WHERE i.id = :intento_id AND a.estudiante_id = :est_id
                LIMIT 1
            ");
            $stmtCaso->execute(['intento_id' => (int) $id, 'est_id' => $estudianteId]);
            $casoDB = $stmtCaso->fetch(\PDO::FETCH_ASSOC);
            if ($casoDB && !empty($casoDB['titulo'])) {
                $casoTitulo = $casoDB['titulo'];
            }
            if ($casoDB && !empty($casoDB['tipo_cedula'])) {
                // V, E o No_Aplica → si es E usa E, si no usa V
                $tipoCedulaCausante = ($casoDB['tipo_cedula'] === 'E') ? 'E' : 'V';
            }
        } catch (\Throwable $e) {
            error_log("validar-rs: Error al obtener título del caso: " . $e->getMessage());
            // No es crítico, continuar con valores por defecto
        }

        // ── Validar borrador contra los datos reales del caso ──
        $validator = new \App\Modules\Simulator\Validators\RSValidator();
        $result = $validator->validar((int) $id, $estudianteId);

        // ── Enviar correo con resultados ──
        $emailEnviado = false;
        $emailEstudiante = $_SESSION['email'] ?? '';
        $nombreEstudiante = $_SESSION['user_name'] ?? 'Estudiante';

        if ($emailEstudiante) {
            $mailer = new \App\Modules\Simulator\Services\RSMailerService();

            if ($result['ok']) {
                // Generar RIF Sucesoral simulado (V12345678 o E12345678) y persistir en DB
                // La columna tiene UNIQUE KEY — reintentamos solo si hay colisión
                $rifSucesoral = null;
                $maxReintentos = 5;
                for ($r = 0; $r < $maxReintentos; $r++) {
                    $rifCandidate = $tipoCedulaCausante . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                    try {
                        $stmtRif = $db->prepare("
                            UPDATE sim_intentos
                            SET rif_sucesoral = :rif,
                                updated_at    = NOW()
                            WHERE id = :id
                        ");
                        $stmtRif->execute([
                            'rif' => $rifCandidate,
                            'id'  => (int) $id,
                        ]);
                        $rifSucesoral = $rifCandidate;
                        break;
                    } catch (\PDOException $e) {
                        // 23000 = Integrity constraint (duplicate key) → reintentar
                        if ($e->getCode() == '23000') {
                            continue;
                        }
                        // Otro error de DB → no reintentar
                        error_log("validar-rs: Error de DB al guardar RIF: " . $e->getMessage());
                        break;
                    } catch (\Throwable $e) {
                        error_log("validar-rs: Error inesperado al guardar RIF: " . $e->getMessage());
                        break;
                    }
                }
                $result['rif_sucesoral'] = $rifSucesoral;

                $emailEnviado = $mailer->enviarExito(
                    $emailEstudiante,
                    $nombreEstudiante,
                    (int) $id,
                    $rifSucesoral,
                    $casoTitulo
                );
            } else {
                $emailEnviado = $mailer->enviarDiscrepancias(
                    $emailEstudiante,
                    $result['errores'],
                    $nombreEstudiante,
                    (int) $id,
                    $casoTitulo
                );
            }
        }

        $result['email_enviado'] = $emailEnviado;
        echo json_encode($result, JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        error_log("validar-rs CRITICAL: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'email_enviado' => false,
            'errores' => ['general' => ['Error interno del servidor al procesar la validación. Intente nuevamente.']],
        ], JSON_UNESCAPED_UNICODE);
    }
});

// Enviar intento
$router->post('/api/intentos/{id}/enviar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    header('Content-Type: application/json');

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();

    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);
    $intento = $attemptModel->getIntento((int) $id, $estudianteId);

    if (!$intento || $intento['estado'] !== 'En_Progreso') {
        http_response_code(403);
        echo json_encode(['error' => 'Intento no válido']);
        return;
    }

    $ok = $attemptModel->enviarIntento((int) $id);
    if ($ok) {
        unset($_SESSION['sim_asignacion_id']);
    }
    echo json_encode(['ok' => $ok]);
});

// Cancelar intento
$router->post('/api/intentos/{id}/cancelar', function ($id) use ($requireAuth, $requireRole) {
    $requireAuth();
    $requireRole(3);
    header('Content-Type: application/json');

    $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
    $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();

    $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);
    $intento = $attemptModel->getIntento((int) $id, $estudianteId);

    if (!$intento || $intento['estado'] !== 'En_Progreso') {
        http_response_code(403);
        echo json_encode(['error' => 'Intento no válido']);
        return;
    }

    $ok = $attemptModel->cancelarIntento((int) $id);
    if ($ok) {
        unset($_SESSION['sim_asignacion_id']);
    }
    echo json_encode(['ok' => $ok]);
});

// Salir del simulador (limpia sesión y redirige)
$router->get('/api/simulador/salir', function () use ($requireAuth) {
    $requireAuth();
    unset($_SESSION['sim_asignacion_id']);
    $dest = $_GET['dest'] ?? '/home';
    // Sanitizar: solo permitir rutas internas
    if (strpos($dest, '/') !== 0) {
        $dest = '/home';
    }
    header('Location: ' . base_url(ltrim($dest, '/')));
    exit;
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
$router->get('/api/secciones-profesor', [CatalogController::class, 'getSeccionesProfesor']);
$router->get('/api/estudiantes-profesor', [CatalogController::class, 'getEstudiantesProfesor']);
$router->get('/api/buscar-empresa-rif', [CatalogController::class, 'buscarEmpresaPorRif']);
$router->get('/api/buscar-persona', [CatalogController::class, 'buscarPersonaPorCedula']);