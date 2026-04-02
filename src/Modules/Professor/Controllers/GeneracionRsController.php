<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Core\BitacoraModel;
use App\Modules\Professor\Models\GeneracionRsModel;
use App\Modules\Simulator\Services\RifGeneratorService;
use App\Modules\Simulator\Services\RSMailerService;
use App\Modules\Simulator\Validators\RSValidator;

/**
 * GeneracionRsController — Bandeja de aprobación/rechazo de RIF Sucesoral.
 *
 * Permite al profesor revisar las inscripciones de RIF enviadas por
 * estudiantes en modo Evaluación, y aprobar (genera RIF) o rechazar
 * (asigna nota + observación).
 *
 * Anti-crash: todos los métodos públicos tienen try/catch total.
 */
class GeneracionRsController
{
    private GeneracionRsModel $model;

    public function __construct()
    {
        $this->model = new GeneracionRsModel();
    }

    /**
     * GET /generacion-rs
     * Muestra la vista con solicitudes reales y estadísticas.
     */
    public function index(): void
    {
        try {
            $profesorId = $this->model->getProfesorId((int) ($_SESSION['user_id'] ?? 0));

            if (!$profesorId) {
                header('Location: ' . base_url('home'));
                exit;
            }

            $solicitudes = $this->model->getSolicitudes($profesorId);
            $stats       = $this->model->getStats($profesorId);
        } catch (\Throwable $e) {
            error_log('[GeneracionRsController::index] ' . $e->getMessage());
            $solicitudes = [];
            $stats = ['pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'total' => 0];
        }

        require_once __DIR__ . '/../../../../resources/views/professor/generacion_rs.php';
    }

    /**
     * GET /generacion-rs/{id}
     * Vista de comparación campo a campo para revisión del profesor.
     */
    public function detalle(int $id): void
    {
        try {
            $profesorId = $this->model->getProfesorId((int) ($_SESSION['user_id'] ?? 0));

            if (!$profesorId) {
                header('Location: ' . base_url('generacion-rs'));
                exit;
            }

            $validator = new RSValidator();
            $comparacion = $validator->getComparacionParaRevision($id, $profesorId);

            if (!$comparacion) {
                header('Location: ' . base_url('generacion-rs'));
                exit;
            }

            $intento = $comparacion['intento'];
            $causante = $comparacion['causante'];
            $relaciones = $comparacion['relaciones'];
            $direcciones = $comparacion['direcciones'];
        } catch (\Throwable $e) {
            error_log('[GeneracionRsController::detalle] ' . $e->getMessage());
            header('Location: ' . base_url('generacion-rs'));
            exit;
        }

        require_once __DIR__ . '/../../../../resources/views/professor/generacion_rs_detalle.php';
    }

    /**
     * POST /api/generacion-rs/{id}/aprobar
     * Aprueba un intento: genera RIF + envía correo + registra bitácora.
     */
    public function aprobar(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $profesorId = $this->model->getProfesorId((int) ($_SESSION['user_id'] ?? 0));

            if (!$profesorId) {
                echo json_encode(['ok' => false, 'message' => 'No se pudo identificar al profesor.']);
                return;
            }

            // ── 1. Verificar intento pertenece al profesor + estado correcto ──
            $intento = $this->model->getIntentoPorId($id, $profesorId);

            if (!$intento) {
                echo json_encode(['ok' => false, 'message' => 'Solicitud no encontrada o no pertenece a usted.']);
                return;
            }

            if ($intento['estado'] !== 'Pendiente_RIF') {
                echo json_encode(['ok' => false, 'message' => 'Esta solicitud ya fue revisada.']);
                return;
            }

            // ── 2. Generar RIF vía servicio ──
            $rifService = new RifGeneratorService();
            $rif = $rifService->generar($id);

            if (!$rif) {
                echo json_encode(['ok' => false, 'message' => 'Error al generar el RIF Sucesoral. Intente nuevamente.']);
                return;
            }

            // ── 3. Actualizar estado a Aprobado ──
            $updated = $this->model->aprobar($id, $rif);

            if (!$updated) {
                echo json_encode(['ok' => false, 'message' => 'No se pudo actualizar el estado. Posiblemente ya fue revisada.']);
                return;
            }

            // ── 4. Enviar correo al estudiante ──
            $emailEnviado = false;
            try {
                $email  = $intento['est_email'] ?? '';
                $nombre = trim(($intento['est_nombres'] ?? '') . ' ' . ($intento['est_apellidos'] ?? ''));
                $caso   = $intento['caso_titulo'] ?? 'Caso Sucesoral';

                if ($email) {
                    $mailer = new RSMailerService();
                    $emailEnviado = $mailer->enviarAprobacionRif($email, $nombre, (int)($intento['numero_intento'] ?? 1), $rif, $caso);
                }
            } catch (\Throwable $e) {
                error_log('[GeneracionRsController::aprobar] Error enviando correo: ' . $e->getMessage());
                // No falla la operación principal
            }

            // ── 5. Registrar en bitácora ──
            $detalle = "RIF aprobado: {$rif} — Estudiante: " . ($intento['est_cedula'] ?? '?');
            BitacoraModel::registrar(
                BitacoraModel::ATTEMPT_RIF_REVIEWED,
                'simulador',
                null,
                null,
                'sim_intentos',
                $id,
                $detalle
            );

            // ── 6. Respuesta exitosa ──
            echo json_encode([
                'ok'             => true,
                'rif'            => $rif,
                'email_enviado'  => $emailEnviado,
                'message'        => 'RIF Sucesoral aprobado exitosamente.',
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            error_log('[GeneracionRsController::aprobar] CRITICAL: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error interno del servidor.']);
        }
    }

    /**
     * POST /api/generacion-rs/{id}/rechazar
     * Rechaza un intento: asigna nota + observación + correo.
     *
     * Body JSON esperado:
     *   { "observacion": "...", "nota": 5 }  (nota solo si tipo_calificacion=numerica)
     */
    public function rechazar(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $profesorId = $this->model->getProfesorId((int) ($_SESSION['user_id'] ?? 0));

            if (!$profesorId) {
                echo json_encode(['ok' => false, 'message' => 'No se pudo identificar al profesor.']);
                return;
            }

            // ── 1. Verificar intento ──
            $intento = $this->model->getIntentoPorId($id, $profesorId);

            if (!$intento) {
                echo json_encode(['ok' => false, 'message' => 'Solicitud no encontrada o no pertenece a usted.']);
                return;
            }

            if ($intento['estado'] !== 'Pendiente_RIF') {
                echo json_encode(['ok' => false, 'message' => 'Esta solicitud ya fue revisada.']);
                return;
            }

            // ── 2. Parsear input ──
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $observacion = trim($input['observacion'] ?? '');
            if ($observacion === '') {
                echo json_encode(['ok' => false, 'message' => 'La observación es obligatoria al rechazar.']);
                return;
            }

            // ── 3. Determinar nota según tipo_calificacion ──
            $tipoCalif     = $intento['tipo_calificacion'] ?? 'aprobado_reprobado';
            $notaNumerica  = null;
            $notaCualitativa = null;
            $notaParaCorreo = null;

            if ($tipoCalif === 'numerica') {
                $notaRaw = $input['nota'] ?? null;
                if ($notaRaw === null || $notaRaw === '') {
                    echo json_encode(['ok' => false, 'message' => 'Debe asignar una nota numérica (0-9).']);
                    return;
                }
                $notaNumerica = (float) $notaRaw;
                if ($notaNumerica < 0 || $notaNumerica > 9) {
                    echo json_encode(['ok' => false, 'message' => 'La nota debe estar entre 0 y 9.']);
                    return;
                }
                $notaParaCorreo = (string) $notaNumerica;
            } else {
                // aprobado_reprobado → Reprobado implícito
                $notaCualitativa = 'Reprobado';
                $notaParaCorreo  = 'Reprobado';
            }

            // ── 4. Actualizar en DB ──
            $updated = $this->model->rechazar($id, $tipoCalif, $notaNumerica, $notaCualitativa, $observacion);

            if (!$updated) {
                echo json_encode(['ok' => false, 'message' => 'No se pudo actualizar el estado. Posiblemente ya fue revisada.']);
                return;
            }

            // ── 5. Enviar correo al estudiante ──
            $emailEnviado = false;
            try {
                $email  = $intento['est_email'] ?? '';
                $nombre = trim(($intento['est_nombres'] ?? '') . ' ' . ($intento['est_apellidos'] ?? ''));
                $caso   = $intento['caso_titulo'] ?? 'Caso Sucesoral';

                if ($email) {
                    $mailer = new RSMailerService();
                    $emailEnviado = $mailer->enviarRechazoRif(
                        $email,
                        $nombre,
                        (int)($intento['numero_intento'] ?? 1),
                        $caso,
                        $observacion,
                        $notaParaCorreo
                    );
                }
            } catch (\Throwable $e) {
                error_log('[GeneracionRsController::rechazar] Error enviando correo: ' . $e->getMessage());
            }

            // ── 6. Registrar en bitácora ──
            $detalle = "RIF rechazado — Nota: {$notaParaCorreo} — Estudiante: " . ($intento['est_cedula'] ?? '?');
            BitacoraModel::registrar(
                BitacoraModel::ATTEMPT_RIF_REVIEWED,
                'simulador',
                null,
                null,
                'sim_intentos',
                $id,
                $detalle
            );

            // ── 7. Respuesta exitosa ──
            echo json_encode([
                'ok'             => true,
                'email_enviado'  => $emailEnviado,
                'message'        => 'Solicitud rechazada exitosamente.',
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            error_log('[GeneracionRsController::rechazar] CRITICAL: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}
