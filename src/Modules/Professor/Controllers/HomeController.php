<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\HomeProfessorModel;
use App\Modules\Professor\Models\HistorialModel;

/**
 * Controller para la página de inicio del profesor.
 * Extraído de web.php para respetar MVC.
 */
class HomeController
{
    /**
     * GET /home (role=2, Profesor)
     * Muestra el dashboard del profesor con los estudiantes recientes.
     */
    public function index(\App\Core\App $app): string
    {
        try {
            $homeModel = new HomeProfessorModel();
            $historialModel = new HistorialModel();
            $profesorId = (int) ($_SESSION['user_id'] ?? 0);

            $recentStudents = $homeModel->getRecentStudents(5);
            $recentActivity = $historialModel->getRecent($profesorId, 5);

            return $app->view('professor/home_professor', [
                'recentStudents' => $recentStudents,
                'recentActivity' => $recentActivity,
            ]);
        } catch (\Throwable $e) {
            error_log('[Professor\HomeController::index] ' . $e->getMessage());
            return $app->view('professor/home_professor', [
                'recentStudents' => [],
                'recentActivity' => [],
            ]);
        }
    }
}

