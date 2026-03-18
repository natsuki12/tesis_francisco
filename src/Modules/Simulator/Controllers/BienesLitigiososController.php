<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\PasivosGastosModel;

/**
 * Controller for Bienes Litigiosos view.
 * Loads intento data and renders the read-only litigiosos summary.
 */
class BienesLitigiososController
{
    /**
     * Load the Bienes Litigiosos view with intento data.
     */
    public function index(object $app): string
    {
        $intento = null;

        try {
            $model = new PasivosGastosModel();
            $intento = $model->getIntentoActivo();
        } catch (\Throwable $e) {
            error_log('[BienesLitigiososController::index] Error cargando intento: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_litigiosos/bienes_litigiosos', [
            'intento' => $intento,
        ]);
    }
}
