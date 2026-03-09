<?php

namespace App\Modules\Admin\Controllers\Configuracion;

class CatalogosController
{
    /**
     * Muestra la vista de gestión de catálogos (UT, Parentescos, Bienes, etc.)
     * Mapeado a la ruta: GET /admin/configuracion/catalogos
     */
    public function index()
    {
        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/catalogos.php';
    }
}
