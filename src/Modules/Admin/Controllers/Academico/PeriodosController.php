<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Academico;

use App\Modules\Admin\Models\PeriodosModel;

class PeriodosController
{
    /**
     * Muestra la vista de gestión administrativa de períodos académicos.
     * Mapeado a la ruta: GET /admin/periodos
     */
    public function index()
    {
        try {
            $model = new PeriodosModel();
            $periodos = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[PeriodosController::index] ' . $e->getMessage());
            $periodos = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_periodos.php';
    }
}
