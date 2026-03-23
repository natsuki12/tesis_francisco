<?php
declare(strict_types=1);

namespace App\Modules\Student\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;

/**
 * Controller para las asignaciones del estudiante.
 * Extraído de web.php para respetar MVC.
 */
class AsignacionesEstudianteController
{
    private StudentAssignmentModel $model;

    public function __construct()
    {
        $this->model = new StudentAssignmentModel();
    }

    /**
     * GET /mis-asignaciones
     * Lista las asignaciones del estudiante.
     */
    public function index(\App\Core\App $app): string
    {
        try {
            $estudianteId = $this->model->getEstudianteId((int) $_SESSION['user_id']);
            $asignaciones = [];
            if ($estudianteId) {
                $asignaciones = $this->model->getAsignaciones($estudianteId);
            }

            return $app->view('student/mis_asignaciones', [
                'asignaciones' => $asignaciones,
            ]);
        } catch (\Throwable $e) {
            error_log('[AsignacionesEstudianteController::index] ' . $e->getMessage());
            return $app->view('student/mis_asignaciones', [
                'asignaciones' => [],
            ]);
        }
    }

    /**
     * GET /mis-asignaciones/{id}
     * Muestra el detalle de una asignación con sus intentos.
     */
    public function show(int $id, \App\Core\App $app): string
    {
        try {
            $estudianteId = $this->model->getEstudianteId((int) $_SESSION['user_id']);

            if (!$estudianteId) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            $asignacion = $this->model->getDetalleAsignacion($id, $estudianteId);
            if (!$asignacion) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            $intentos = $this->model->getIntentos($id);

            return $app->view('student/detalle_asignacion', [
                'asignacion' => $asignacion,
                'intentos' => $intentos,
            ]);
        } catch (\Throwable $e) {
            error_log('[AsignacionesEstudianteController::show] ' . $e->getMessage());
            http_response_code(500);
            return $app->view('errors/404');
        }
    }
}
