<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers\Casos;

use App\Core\App;
use App\Modules\Professor\Models\Casos\CasosModel;

class CasosController
{
    private App $app;
    private CasosModel $casosModel;

    public function __construct()
    {
        global $app;
        $this->app = $app;
        $this->casosModel = new CasosModel();
    }

    /**
     * Muestra la lista de casos sucesorales del profesor.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $profesorId = (int) ($_SESSION['user_id'] ?? 0); // Asumiendo que user_id equivale al profesor, ajustar si es distinto

        // Si el profesor no está logueado, redirigir
        if (!$profesorId) {
            header('Location: ' . base_url('/login'));
            exit;
        }

        // Obtener la información real de la BD
        $casos = $this->casosModel->getCasosByProfesor($profesorId);
        $stats = $this->casosModel->getStatsByProfesor($profesorId);

        return $this->app->view('professor/casos_sucesorales', [
            'casos' => $casos,
            'stats' => $stats
        ]);
    }
}
