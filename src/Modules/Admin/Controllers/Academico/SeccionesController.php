<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Academico;

use App\Modules\Admin\Models\SeccionesModel;

class SeccionesController
{
    /**
     * Muestra la vista de gestión administrativa de secciones.
     * Mapeado a la ruta: GET /admin/secciones
     */
    public function index()
    {
        try {
            $model = new SeccionesModel();
            $secciones  = $model->getAll();
            $periodos   = $model->getPeriodos();
            $profesores = $model->getProfesores();
        } catch (\Throwable $e) {
            error_log('[SeccionesController::index] ' . $e->getMessage());
            $secciones  = [];
            $periodos   = [];
            $profesores = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_secciones.php';
    }
}
