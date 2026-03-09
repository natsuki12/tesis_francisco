<?php

namespace App\Modules\Admin\Controllers\Configuracion;

class ParametrosController
{
    /**
     * Muestra la vista de parámetros globales del sistema.
     * Mapeado a la ruta: GET /admin/configuracion/parametros
     */
    public function index()
    {
        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/parametros.php';
    }
}
