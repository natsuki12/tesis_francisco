<?php

namespace App\Modules\Admin\Controllers\Academico;

class SeccionesController
{
    /**
     * Muestra la vista de gestión administrativa de secciones.
     * Mapeado a la ruta: GET /admin/secciones
     */
    public function index()
    {
        // Mock data.
        $secciones = [];

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_secciones.php';
    }
}
