<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\HistorialModel;

/**
 * Controlador de Historial de Actividad del Profesor.
 * Mapeado a: GET /historial, GET /historial/api
 */
class HistorialController
{
    private HistorialModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new HistorialModel();
        $this->profesorId = (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * Vista principal: renderiza la tabla vacía + JS que llama a apiList().
     */
    public function index(): void
    {
        $tiposEvento = $this->model->getTiposEvento();
        require __DIR__ . '/../../../../resources/views/professor/historial.php';
    }

    /**
     * API paginada para la DataTable server-side (AJAX GET).
     */
    public function apiList(): void
    {
        header('Content-Type: application/json');
        session_write_close();

        try {
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $limit   = min(50, max(10, (int) ($_GET['limit'] ?? 15)));
            $search  = trim($_GET['search'] ?? '');
            $sortCol = $_GET['sort'] ?? 'fecha';
            $sortDir = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

            $filters = [];
            if (!empty($_GET['tipo']))      $filters['tipo']      = $_GET['tipo'];
            if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (!empty($_GET['date_to']))   $filters['date_to']   = $_GET['date_to'];

            $result = $this->model->getPaginated(
                $this->profesorId, $page, $limit, $search, $sortCol, $sortDir, $filters
            );

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[HistorialController::apiList] ' . $e->getMessage());
            echo json_encode(['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1]);
        }
    }
}
