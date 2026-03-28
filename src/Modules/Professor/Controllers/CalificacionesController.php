<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\CalificacionesModel;

/**
 * Controlador de Calificaciones — sábana de notas del profesor.
 * Mapeado a: GET /calificaciones, GET /calificaciones/api
 */
class CalificacionesController
{
    private CalificacionesModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new CalificacionesModel();
        $this->profesorId = (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * Vista principal: secciones pre-cargadas, tabla se carga via AJAX.
     */
    public function index(): void
    {
        $secciones = $this->model->getSecciones($this->profesorId);
        require __DIR__ . '/../../../../resources/views/professor/calificaciones.php';
    }

    /**
     * API JSON: devuelve la sábana de notas de una sección.
     */
    public function apiNotas(): void
    {
        header('Content-Type: application/json');
        session_write_close();

        try {
            $seccionId = (int) ($_GET['seccion_id'] ?? 0);
            if ($seccionId <= 0) {
                echo json_encode(['error' => 'Sección no especificada']);
                return;
            }

            $result = $this->model->getSabana($this->profesorId, $seccionId);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[CalificacionesController::apiNotas] ' . $e->getMessage());
            echo json_encode(['estudiantes' => [], 'casos' => [], 'stats' => ['total' => 0, 'calificados' => 0, 'pendientes' => 0]]);
        }
    }
}
