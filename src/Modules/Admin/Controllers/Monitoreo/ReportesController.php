<?php

namespace App\Modules\Admin\Controllers\Monitoreo;

class ReportesController
{
    /**
     * Muestra la vista de reportes estadísticos.
     * Mapeado a la ruta: GET /admin/monitoreo/reportes
     */
    public function index()
    {
        require_once __DIR__ . '/../../../../../resources/views/admin/monitoreo/reportes.php';
    }
}
