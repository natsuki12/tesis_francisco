<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Herederos (Identificación de Herederos) view.
 */
class HerederosController
{
    /**
     * Load the Herederos view with intento data and parentesco catalog.
     */
    public function index(object $app): string
    {
        $intentoActivo = null;
        $catalogoParentescos = [];

        try {
            $assignModel = new StudentAssignmentModel();
            $attemptModel = new StudentAttemptModel();
            $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

            if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
                $intentoActivo = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            }

            $catalog = new CatalogModel();
            $rows = $catalog->getParentescos();
            foreach ($rows as $row) {
                $catalogoParentescos[(int) $row['parentesco_id']] = $row['nombre'];
            }
        } catch (\Throwable $e) {
            error_log('[HerederosController::index] Error cargando datos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/identificacion_herederos/herederos', [
            'intento' => $intentoActivo,
            'catalogoParentescos' => $catalogoParentescos,
        ]);
    }
}
