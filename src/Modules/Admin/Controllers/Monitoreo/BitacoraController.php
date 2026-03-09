<?php

namespace App\Modules\Admin\Controllers\Monitoreo;

class BitacoraController
{
    /**
     * Muestra la vista de bitácora y auditoría del sistema.
     * Mapeado a la ruta: GET /admin/monitoreo/bitacora
     */
    public function index()
    {
        require_once __DIR__ . '/../../../../../resources/views/admin/monitoreo/bitacora.php';
    }
}
