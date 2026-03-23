<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Configuracion;

use App\Modules\Admin\Models\MarcoLegalModel;

class MarcoLegalController
{
    /**
     * Muestra la vista de gestión del marco legal (LISSD).
     * Mapeado a la ruta: GET /admin/configuracion/marco-legal
     */
    public function index()
    {
        try {
            $model = new MarcoLegalModel();
            $articulosMarcoLegal = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[MarcoLegalController::index] ' . $e->getMessage());
            $articulosMarcoLegal = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/marco_legal.php';
    }
}
