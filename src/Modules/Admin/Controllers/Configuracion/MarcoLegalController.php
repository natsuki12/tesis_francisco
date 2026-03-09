<?php

namespace App\Modules\Admin\Controllers\Configuracion;

class MarcoLegalController
{
    /**
     * Muestra la vista de gestión del marco legal (LISSD).
     * Mapeado a la ruta: GET /admin/configuracion/marco-legal
     */
    public function index()
    {
        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/marco_legal.php';
    }
}
