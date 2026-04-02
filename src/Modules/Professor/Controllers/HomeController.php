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

            // Resolver el ID del profesor basándose en el user_id de la sesión.
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $profesorId = $homeModel->getProfesorId($userId);

            if (!$profesorId) {
                return $app->view('professor/home_professor', [
                    'recentStudents' => [],
                    'recentActivity' => [],
                    'stats'          => ['estudiantes' => 0, 'casos' => 0, 'rif_pendientes' => 0, 'por_calificar' => 0],
                ]);
            }

            $recentStudents = $homeModel->getRecentStudents($profesorId, 5);
            $recentActivity = $historialModel->getRecent($profesorId, 5);
            $stats = $homeModel->getDashboardStats($profesorId);

            return $app->view('professor/home_professor', [
                'recentStudents' => $recentStudents,
                'recentActivity' => $recentActivity,
                'stats'          => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log('[Professor\HomeController::index] ' . $e->getMessage());
            return $app->view('professor/home_professor', [
                'recentStudents' => [],
                'recentActivity' => [],
                'stats'          => ['estudiantes' => 0, 'casos' => 0, 'rif_pendientes' => 0, 'por_calificar' => 0],
            ]);
        }
    }
}

