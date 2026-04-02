<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Core\BitacoraModel;
use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;
use App\Modules\Simulator\Services\RifGeneratorService;

/**
 * Controller para el ciclo de vida de intentos del simulador.
 * Extraído de web.php para respetar MVC.
 */
class IntentosController
{
    private StudentAssignmentModel $assignModel;
    private StudentAttemptModel $attemptModel;

    public function __construct()
    {
        $this->assignModel  = new StudentAssignmentModel();
        $this->attemptModel = new StudentAttemptModel();
    }

    /**
     * POST /api/intentos/iniciar
     * Inicia un nuevo intento o retoma el activo.
     * Setea $_SESSION['sim_asignacion_id'] y $_SESSION['sim_modalidad'].
     */
    public function iniciar(): void
    {
        $asignacionId = (int) ($_POST['asignacion_id'] ?? 0);
        if (!$asignacionId) {
            http_response_code(400);
            echo json_encode(['error' => 'asignacion_id requerido']);
            return;
        }

        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            if (!$estudianteId) {
                http_response_code(403);
                echo json_encode(['error' => 'Estudiante no encontrado']);
                return;
            }

            // Si ya tiene uno activo, retomarlo
            $activo = $this->attemptModel->getIntentoActivo($asignacionId);
            if ($activo) {
                $_SESSION['sim_asignacion_id'] = $asignacionId;
                $_SESSION['sim_modalidad']     = $this->attemptModel->getModalidadByAsignacion($asignacionId);
                header('Location: ' . base_url('/simulador'));
                return;
            }

            // Verificar si puede iniciar
            $check = $this->attemptModel->verificarPuedeIniciar($asignacionId, $estudianteId);
            if (!$check['ok']) {
                $_SESSION['flash_error'] = $check['razon'];
                header('Location: ' . base_url('/mis-asignaciones/' . $asignacionId));
                return;
            }

            // Crear intento
            $intento = $this->attemptModel->crearIntento($asignacionId);

            // Setear sesión del simulador
            $_SESSION['sim_asignacion_id'] = $asignacionId;
            $_SESSION['sim_modalidad']     = $check['modalidad'] ?? null;

            // Registrar en bitácora
            BitacoraModel::registrar(
                BitacoraModel::ATTEMPT_STARTED,
                'simulador',
                (int) $_SESSION['user_id'],
                null,
                'sim_intentos',
                (int) $intento['id'],
                detalle: 'Intento #' . $intento['numero_intento']
            );

            header('Location: ' . base_url('/simulador'));

        } catch (\Throwable $e) {
            error_log('[IntentosController::iniciar] Error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error interno al iniciar el intento. Inténtelo de nuevo.';
            header('Location: ' . base_url('/mis-asignaciones/' . $asignacionId));
        }
    }

    /**
     * POST /api/intentos/{id}/guardar
     * Auto-save del borrador JSON.
     */
    public function guardar(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                return;
            }

            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            $intento = $this->attemptModel->getIntento($id, $estudianteId);

            if (!$intento || $intento['estado'] !== 'En_Progreso') {
                http_response_code(403);
                echo json_encode(['error' => 'Intento no válido']);
                return;
            }

            $ok = $this->attemptModel->guardarBorrador(
                $id,
                json_encode($input['borrador'] ?? [], JSON_UNESCAPED_UNICODE),
                (int) ($input['paso_actual'] ?? $intento['paso_actual']),
                (string) ($input['pasos_completados'] ?? $intento['pasos_completados'])
            );

            echo json_encode(['ok' => $ok]);

        } catch (\Throwable $e) {
            error_log('[IntentosController::guardar] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno al guardar.']);
        }
    }

    /**
     * POST /api/intentos/{id}/enviar
     * Envía el intento (cambia estado a Enviado).
     */
    public function enviar(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            $intento = $this->attemptModel->getIntento($id, $estudianteId);

            if (!$intento || $intento['estado'] !== 'En_Progreso') {
                http_response_code(403);
                echo json_encode(['error' => 'Intento no válido']);
                return;
            }

            $ok = $this->attemptModel->enviarIntento($id);
            if ($ok) {
                unset($_SESSION['sim_asignacion_id'], $_SESSION['sim_modalidad']);

                BitacoraModel::registrar(
                    BitacoraModel::ATTEMPT_SUBMITTED,
                    'simulador',
                    (int) $_SESSION['user_id'],
                    null,
                    'sim_intentos',
                    $id
                );
            }
            echo json_encode(['ok' => $ok]);

        } catch (\Throwable $e) {
            error_log('[IntentosController::enviar] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno al enviar.']);
        }
    }

    /**
     * POST /api/intentos/declarar
     * Persiste el borrador_json en tablas normalizadas (StoreIntentoModel)
     * y marca el intento como Enviado.
     */
    public function declarar(): void
    {
        header('Content-Type: application/json');

        try {
            $asignacionId = (int) ($_SESSION['sim_asignacion_id'] ?? 0);
            if (!$asignacionId) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Sin sesión de simulador activa.']);
                return;
            }

            $intento = $this->attemptModel->getIntentoActivo($asignacionId);
            if (!$intento) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'No hay intento activo para declarar.']);
                return;
            }

            $intentoId = (int) $intento['id'];
            $borrador = json_decode($intento['borrador_json'] ?? '{}', true);
            if (empty($borrador)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'El borrador está vacío.']);
                return;
            }

            // Persistir en tablas normalizadas + estado Enviado + bitácora
            $storeModel = new \App\Modules\Simulator\Models\StoreIntentoModel();
            $storeModel->store($intentoId, $borrador);

            // Guardar referencia al intento declarado (para acceso a PDFs)
            $_SESSION['sim_last_intento_id'] = $intentoId;

            // Limpiar sesión del simulador
            unset($_SESSION['sim_asignacion_id'], $_SESSION['sim_modalidad'], $_SESSION['sim_seniat_logged_in']);

            echo json_encode([
                'ok'            => true,
                'asignacion_id' => $asignacionId,
            ]);

        } catch (\Throwable $e) {
            error_log('[IntentosController::declarar] CRITICAL: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno al procesar la declaración.']);
        }
    }

    /**
     * POST /api/intentos/{id}/cancelar
     * Cancela un intento activo.
     */
    public function cancelar(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            $intento = $this->attemptModel->getIntento($id, $estudianteId);

            if (!$intento || $intento['estado'] !== 'En_Progreso') {
                http_response_code(403);
                echo json_encode(['error' => 'Intento no válido']);
                return;
            }

            $ok = $this->attemptModel->cancelarIntento($id);
            if ($ok) {
                unset($_SESSION['sim_asignacion_id'], $_SESSION['sim_modalidad']);

                BitacoraModel::registrar(
                    BitacoraModel::ATTEMPT_CANCELLED,
                    'simulador',
                    (int) $_SESSION['user_id'],
                    null,
                    'sim_intentos',
                    $id
                );
            }
            echo json_encode(['ok' => $ok]);

        } catch (\Throwable $e) {
            error_log('[IntentosController::cancelar] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno al cancelar.']);
        }
    }

    /**
     * POST /api/intentos/{id}/validar-rs
     * Valida borrador contra datos del caso, genera RIF sucesoral y envía correo.
     */
    public function validarRs(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);

            if (!$estudianteId) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'errores' => ['general' => ['Estudiante no encontrado.']]]);
                return;
            }

            // Bloquear si ya se generó el RIF
            $intentoCheck = $this->attemptModel->getIntentoActivo((int) ($_SESSION['sim_asignacion_id'] ?? 0));
            if ($intentoCheck && !empty($intentoCheck['rif_sucesoral'])) {
                echo json_encode(['ok' => false, 'errores' => ['general' => ['El RIF Sucesoral ya fue generado para este intento.']]]);
                return;
            }

            // Obtener título real del caso y tipo_cedula del causante
            $casoTitulo = 'Caso Sucesoral';
            $tipoCedulaCausante = 'V';
            $db = \App\Core\DB::connect();
            try {
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
                $stmtCaso->execute(['intento_id' => $id, 'est_id' => $estudianteId]);
                $casoDB = $stmtCaso->fetch(\PDO::FETCH_ASSOC);
                if ($casoDB && !empty($casoDB['titulo'])) {
                    $casoTitulo = $casoDB['titulo'];
                }
                if ($casoDB && !empty($casoDB['tipo_cedula'])) {
                    $tipoCedulaCausante = ($casoDB['tipo_cedula'] === 'E') ? 'E' : 'V';
                }
            } catch (\Throwable $e) {
                error_log("[IntentosController::validarRs] Error al obtener título del caso: " . $e->getMessage());
            }

            // En modalidad Evaluación: NO validar, siempre poner Pendiente_RIF para que el profesor revise
            $modalidad = $_SESSION['sim_modalidad'] ?? null;
            if ($modalidad === 'Evaluacion') {
                // Cambiar estado a Pendiente_RIF (solo si aún está En_Progreso, previene doble envío)
                $stmtPendiente = $db->prepare("
                    UPDATE sim_intentos
                    SET estado = 'Pendiente_RIF', submitted_at = NOW(), updated_at = NOW()
                    WHERE id = :id AND estado = 'En_Progreso'
                ");
                $stmtPendiente->execute(['id' => $id]);

                if ($stmtPendiente->rowCount() === 0) {
                    echo json_encode(['ok' => false, 'errores' => ['general' => ['Este intento ya fue enviado o no está activo.']]]);
                    return;
                }

                // Limpiar sesión del simulador (el estudiante ya no puede editar)
                unset($_SESSION['sim_asignacion_id'], $_SESSION['sim_modalidad'], $_SESSION['sim_seniat_logged_in']);

                echo json_encode([
                    'ok'             => true,
                    'modalidad'      => 'Evaluacion',
                    'pendiente_rif'  => true,
                    'email_enviado'  => false,
                    'mensaje'        => 'Su inscripción ha sido enviada al profesor para revisión y aprobación del RIF Sucesoral.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Práctica Libre / Guiada: validar borrador contra los datos reales del caso
            $validator = new \App\Modules\Simulator\Validators\RSValidator();
            $result = $validator->validar($id, $estudianteId);

            // Práctica Libre / Guiada: generar RIF + enviar correo
            $emailEnviado = false;
            $emailEstudiante = $_SESSION['email'] ?? '';
            $nombreEstudiante = $_SESSION['user_name'] ?? 'Estudiante';

            if ($emailEstudiante) {
                $mailer = new \App\Modules\Simulator\Services\RSMailerService();

                if ($result['ok']) {
                    // Generar RIF Sucesoral vía servicio reutilizable
                    $rifService = new RifGeneratorService();
                    $rifSucesoral = $rifService->generar($id);
                    $result['rif_sucesoral'] = $rifSucesoral;

                    $emailEnviado = $mailer->enviarExito(
                        $emailEstudiante,
                        $nombreEstudiante,
                        $id,
                        $rifSucesoral,
                        $casoTitulo
                    );
                } else {
                    $emailEnviado = $mailer->enviarDiscrepancias(
                        $emailEstudiante,
                        $result['errores'],
                        $nombreEstudiante,
                        $id,
                        $casoTitulo
                    );
                }
            }

            $result['email_enviado'] = $emailEnviado;
            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            error_log("[IntentosController::validarRs] CRITICAL: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'email_enviado' => false,
                'errores' => ['general' => ['Error interno del servidor al procesar la validación. Intente nuevamente.']],
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/simulador/salir
     * Limpia sesión del simulador y redirige.
     */
    public function salir(): void
    {
        try {
            unset($_SESSION['sim_asignacion_id'], $_SESSION['sim_modalidad'], $_SESSION['sim_seniat_logged_in']);
        } catch (\Throwable $e) {
            error_log('[IntentosController::salir] Error: ' . $e->getMessage());
        }
        $dest = trim($_GET['dest'] ?? '/home');
        
        // Sanitización para prevenir Open Redirect:
        // 1. Debe empezar con '/'
        // 2. NO debe empezar con '//' (relative scheme attack)
        // 3. NO debe contener '://'
        if (strpos($dest, '/') !== 0 || strpos($dest, '//') === 0 || strpos($dest, '://') !== false) {
            $dest = '/home';
        }
        
        header('Location: ' . base_url(ltrim($dest, '/')));
        exit;
    }
}
