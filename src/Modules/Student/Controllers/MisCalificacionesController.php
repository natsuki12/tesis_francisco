<?php
declare(strict_types=1);

namespace App\Modules\Student\Controllers;

use App\Core\App;
use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Simulator\Services\DeclaracionComparador;
use App\Modules\Simulator\Validators\RSValidator;

/**
 * MisCalificacionesController — Vista de detalle de corrección para el estudiante.
 *
 * GET /mis-calificaciones/{asignacion_id}
 * Muestra la corrección del intento más reciente calificado (Aprobado o Rechazado)
 * para la asignación dada, verificando que pertenezca al estudiante autenticado.
 *
 * Anti-crash: todos los errores se registran en el log sin exponer detalles al usuario.
 */
class MisCalificacionesController
{
    /**
     * Muestra el detalle de corrección de la asignación.
     */
    public function show(int $asignacionId, App $app): string
    {
        try {
            $model        = new StudentAssignmentModel();
            $estudianteId = $model->getEstudianteId((int) ($_SESSION['user_id'] ?? 0));

            if (!$estudianteId) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            $intento = $model->getIntentoCalificado($asignacionId, $estudianteId);

            if (!$intento) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            // ── Condición de ramificación: rechazo en etapa de inscripción de RIF ──
            $esRechazadoSinRif = ($intento['estado'] === 'Rechazado' && empty($intento['rif_sucesoral']));

            // ── Defaults ──
            $causante    = ['campos' => [], 'vacio' => true];
            $relaciones  = ['representante' => [], 'herederos' => []];
            $direcciones = [];

            $emptyComparacion = [
                'secciones'         => [],
                'resumen_secciones' => [],
                'autoliquidacion'   => [],
                'herederos_calculo' => [],
                'score'             => [
                    'correctas'      => 0,
                    'con_errores'    => 0,
                    'omitidas'       => 0,
                    'de_mas'         => 0,
                    'total_esperado' => 0,
                    'porcentaje'     => 0,
                ],
            ];
            $comparacion = $emptyComparacion;

            if ($esRechazadoSinRif) {
                // Cargar comparación de campos RIF (causante / relaciones / direcciones)
                try {
                    $validator = new RSValidator();
                    $rsData    = $validator->getComparacionParaEstudiante((int) $intento['intento_id'], $estudianteId);
                    if ($rsData) {
                        $causante    = $rsData['causante']    ?? $causante;
                        $relaciones  = $rsData['relaciones']  ?? $relaciones;
                        $direcciones = $rsData['direcciones'] ?? $direcciones;
                    }
                } catch (\Throwable $e) {
                    error_log('[MisCalificacionesController::show] RS comparacion: ' . $e->getMessage());
                }
            } else {
                // Cargar comparación completa de declaración sucesoral
                try {
                    $comparador  = new DeclaracionComparador();
                    $comparacion = $comparador->comparar((int) $intento['intento_id'], $estudianteId);
                } catch (\Throwable $e) {
                    error_log('[MisCalificacionesController::show] Comparacion: ' . $e->getMessage());
                }
            }

            return $app->view('student/detalle_correccion', compact(
                'intento',
                'comparacion',
                'causante',
                'relaciones',
                'direcciones',
                'esRechazadoSinRif',
                'asignacionId'
            ));

        } catch (\Throwable $e) {
            error_log('[MisCalificacionesController::show] CRITICAL: ' . $e->getMessage());
            http_response_code(500);
            return $app->view('errors/404');
        }
    }
}
