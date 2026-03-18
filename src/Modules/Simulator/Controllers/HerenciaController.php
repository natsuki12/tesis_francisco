<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Controller for Tipo de Herencia view.
 */
class HerenciaController
{
    /**
     * Load the Tipo de Herencia view with intento data.
     */
    public function index(object $app): string
    {
        $intentoActivo = null;

        try {
            $assignModel = new StudentAssignmentModel();
            $attemptModel = new StudentAttemptModel();
            $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

            if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
                $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            }
        } catch (\Throwable $e) {
            error_log('[HerenciaController::index] Error cargando intento: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/herencia/tipo_herencia', [
            'intento' => $intentoActivo,
        ]);
    }
}
