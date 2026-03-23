<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Usuarios;

use App\Modules\Admin\Models\EstudiantesModel;

class EstudiantesController
{
    /**
     * Muestra la vista de gestión administrativa de estudiantes.
     * Mapeado a la ruta: GET /admin/estudiantes
     */
    public function index()
    {
        try {
            $model = new EstudiantesModel();
            $estudiantes = $model->getAll();
            $conteo = $model->getConteo();
        } catch (\Throwable $e) {
            error_log('[EstudiantesController::index] ' . $e->getMessage());
            $estudiantes = [];
            $conteo = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/usuarios/gestionar_estudiantes.php';
    }
}
