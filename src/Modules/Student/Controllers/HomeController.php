<?php
declare(strict_types=1);

namespace App\Modules\Student\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;

/**
 * Controller para la página de inicio del estudiante.
 * Extraído de web.php para respetar MVC.
 */
class HomeController
{
    /**
     * GET /home (role=3, Estudiante)
     * Muestra el dashboard del estudiante con el borrador de la última asignación accedida.
     */
    public function index(\App\Core\App $app): string
    {
        try {
            $model = new StudentAssignmentModel();
            $estudianteId = $model->getEstudianteId((int) $_SESSION['user_id']);
            $draft = null;
            $proximoVencimiento = null;
            $stats = ['pendientes' => 0, 'en_progreso' => 0, 'calificados' => 0];
            $actividad = [];

            if ($estudianteId) {
                $draft = $model->getUltimaAsignacionAccedida($estudianteId);
                $proximoVencimiento = $model->getProximoVencimiento($estudianteId);
                $stats = $model->getHomeStats($estudianteId);
                $actividad = $model->getActividadReciente($estudianteId, 5);
            }

            return $app->view('student/home_st', [
                'draft' => $draft,
                'proximoVencimiento' => $proximoVencimiento,
                'stats' => $stats,
                'actividad' => $actividad,
            ]);
        } catch (\Throwable $e) {
            error_log('[HomeController::index] ' . $e->getMessage());
            return $app->view('student/home_st', [
                'draft' => null,
                'proximoVencimiento' => null,
                'stats' => ['pendientes' => 0, 'en_progreso' => 0, 'calificados' => 0],
                'actividad' => [],
            ]);
        }
    }
}
