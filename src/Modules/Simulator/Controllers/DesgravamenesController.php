<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\PasivosGastosModel;

/**
 * Controller for Desgravámenes view.
 * Loads intento data and renders the read-only desgravámenes summary.
 */
class DesgravamenesController
{
    /**
     * Load the Desgravámenes view with intento data.
     */
    public function index(object $app): string
    {
        $intento = null;

        try {
            $model = new PasivosGastosModel();
            $intento = $model->getIntentoActivo();
        } catch (\Throwable $e) {
            error_log('[DesgravamenesController::index] Error cargando intento: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/desgravamanes/desgravamenes', [
            'intento' => $intento,
        ]);
    }
}
