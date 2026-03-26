<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Monitoreo;

use App\Core\BitacoraModel as CoreBitacora;
use App\Modules\Admin\Models\BitacoraModel;

class BitacoraController
{
    /**
     * Muestra la vista de bitácora (sin eventos — se cargan via API).
     * GET /admin/monitoreo/bitacora
     */
    public function index()
    {
        try {
            $emails  = BitacoraModel::getUniqueEmails();
            $tipos   = BitacoraModel::getTiposEventos();
            $modulos = CoreBitacora::MODULOS;
        } catch (\Throwable $e) {
            error_log('[BitacoraController::index] ' . $e->getMessage());
            $emails  = [];
            $tipos   = [];
            $modulos = CoreBitacora::MODULOS;
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/monitoreo/bitacora.php';
    }

    /**
     * API paginada para la DataTable de bitácora (AJAX GET).
     * Soporta search, sort, paginación y filtros (módulo, evento, usuario, fechas).
     * GET /admin/monitoreo/bitacora/api
     */
    public function apiList(): void
    {
        header('Content-Type: application/json');
        session_write_close(); // Liberar sesión para no bloquear navegación

        try {
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $limit   = min(50, max(10, (int) ($_GET['limit'] ?? 15)));
            $search  = trim($_GET['search'] ?? '');
            $sortCol = $_GET['sort'] ?? 'created_at';
            $sortDir = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

            // Filtros custom enviados por DataTableManager.setFilter()
            $filters = [];
            foreach (['modulo', 'evento', 'usuario', 'date_from', 'date_to'] as $key) {
                if (!empty($_GET[$key])) {
                    $filters[$key] = $_GET[$key];
                }
            }

            $result = BitacoraModel::getPaginated($page, $limit, $search, $sortCol, $sortDir, $filters);
            $result['modulos'] = CoreBitacora::MODULOS;

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[BitacoraController::apiList] ' . $e->getMessage());
            echo json_encode(['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1]);
        }
    }
}
