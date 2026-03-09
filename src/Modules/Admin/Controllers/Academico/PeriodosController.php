<?php

namespace App\Modules\Admin\Controllers\Academico;

class PeriodosController
{
    /**
     * Muestra la vista de gestión administrativa de períodos académicos.
     * Mapeado a la ruta: GET /admin/periodos
     */
    public function index()
    {
        // Mock data.
        $periodos = [];

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_periodos.php';
    }
}
