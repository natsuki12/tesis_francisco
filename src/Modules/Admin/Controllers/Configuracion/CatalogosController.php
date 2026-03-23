<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Configuracion;

use App\Modules\Admin\Models\CatalogosModel;

class CatalogosController
{
    /**
     * Muestra la vista de gestión de catálogos SENIAT.
     * Mapeado a la ruta: GET /admin/configuracion/catalogos
     */
    public function index()
    {
        try {
            $model = new CatalogosModel();

            // Tab 1: Unidad Tributaria
            $unidadesTributarias = $model->getUnidadesTributarias();

            // Tab 2: Fiscal
            $gruposTarifa = $model->getGruposTarifa();
            $tramosTarifa = $model->getTramosTarifa();
            $reducciones  = $model->getReducciones();

            // Tab 3: Bienes
            $tiposBienInmueble    = $model->getTiposBienInmueble();
            $categoriasBienMueble = $model->getCategoriasBienMueble();
            $tiposBienMueble      = $model->getTiposBienMueble();
            $tiposSemoviente      = $model->getTiposSemoviente();

            // Tab 4: Parentescos
            $parentescos = $model->getParentescos();

            // Tab 5: Pasivos y Herencias
            $tiposPasivoDeuda = $model->getTiposPasivoDeuda();
            $tiposPasivoGasto = $model->getTiposPasivoGasto();
            $tipoHerencias    = $model->getTipoHerencias();
        } catch (\Throwable $e) {
            error_log('[CatalogosController::index] ' . $e->getMessage());
            $unidadesTributarias = $gruposTarifa = $tramosTarifa = $reducciones = [];
            $tiposBienInmueble = $categoriasBienMueble = $tiposBienMueble = $tiposSemoviente = [];
            $parentescos = $tiposPasivoDeuda = $tiposPasivoGasto = $tipoHerencias = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/catalogos.php';
    }
}
