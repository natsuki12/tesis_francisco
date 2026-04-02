<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\MisEstudiantesModel;
use App\Modules\Professor\Models\HomeProfessorModel;

/**
 * Controlador de Mis Estudiantes del Profesor.
 * Mapeado a: GET /mis-estudiantes
 */
class MisEstudiantesController
{
    private MisEstudiantesModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new MisEstudiantesModel();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->profesorId = (new HomeProfessorModel())->getProfesorId($userId) ?? 0;
    }

    /**
     * Lista de estudiantes del profesor con métricas.
     */
    public function index(): void
    {
        $estudiantes = $this->model->getEstudiantes($this->profesorId);
        $stats = $this->model->getStats($estudiantes);

        require __DIR__ . '/../../../../resources/views/professor/mis_estudiantes.php';
    }

    /**
     * Detalle de un estudiante específico.
     */
    public function show(int $id): void
    {
        $estudiante = $this->model->getEstudianteDetalle($id, $this->profesorId);

        if (!$estudiante) {
            header('Location: ' . base_url('/mis-estudiantes'));
            exit;
        }

        $entregas = $this->model->getEntregasEstudiante($id, $this->profesorId);

        require __DIR__ . '/../../../../resources/views/professor/detalle_estudiante.php';
    }
}
