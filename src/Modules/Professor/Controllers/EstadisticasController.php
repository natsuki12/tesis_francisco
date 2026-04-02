<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\EstadisticasModel;
use App\Modules\Professor\Models\HomeProfessorModel;

/**
 * Controlador de Estadísticas del Profesor.
 * Mapeado a: GET /estadisticas
 */
class EstadisticasController
{
    private EstadisticasModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new EstadisticasModel();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->profesorId = (new HomeProfessorModel())->getProfesorId($userId) ?? 0;
    }

    /**
     * Vista principal de estadísticas.
     */
    public function index(): void
    {
        try {
            $resumen       = $this->model->getResumen($this->profesorId);
            $estados       = $this->model->getDistribucionEstados($this->profesorId);
            $rendimiento   = $this->model->getRendimientoPorCaso($this->profesorId);
            $notas         = $this->model->getDistribucionNotas($this->profesorId);
            $sinActividad  = $this->model->getEstudiantesSinActividad($this->profesorId);
        } catch (\Throwable $e) {
            error_log('[EstadisticasController::index] ' . $e->getMessage());
            $resumen      = ['estudiantes' => 0, 'casos' => 0, 'intentos' => 0, 'tasa_exito' => 0];
            $estados      = [];
            $rendimiento  = [];
            $notas        = ['numericas' => ['0 – 5' => 0, '6 – 10' => 0, '11 – 15' => 0, '16 – 20' => 0, 'total' => 0], 'cualitativas' => []];
            $sinActividad = [];
        }

        require __DIR__ . '/../../../../resources/views/professor/estadisticas.php';
    }
}
