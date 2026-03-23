<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Monitoreo;

use App\Core\BitacoraModel as CoreBitacora;
use App\Modules\Admin\Models\BitacoraModel;

class BitacoraController
{
    /**
     * Muestra la vista de bitácora y auditoría del sistema.
     * Mapeado a la ruta: GET /admin/monitoreo/bitacora
     */
    public function index()
    {
        try {
            $eventos = BitacoraModel::getAll();
            $emails  = BitacoraModel::getUniqueEmails();
            $tipos   = BitacoraModel::getTiposEventos();
            $modulos = CoreBitacora::MODULOS;
        } catch (\Throwable $e) {
            error_log('[BitacoraController::index] ' . $e->getMessage());
            $eventos = [];
            $emails  = [];
            $tipos   = [];
            $modulos = CoreBitacora::MODULOS;
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/monitoreo/bitacora.php';
    }
}
