<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Controller para el flujo de inscripción de RIF (legacy).
 * Los 4 sub-pasos comparten la misma lógica de carga de intento + guard de RIF.
 * Extraído de web.php para respetar MVC.
 */
class InscripcionRifController
{
    private StudentAssignmentModel $assignModel;
    private StudentAttemptModel $attemptModel;

    public function __construct()
    {
        $this->assignModel  = new StudentAssignmentModel();
        $this->attemptModel = new StudentAttemptModel();
    }

    /**
     * Helper: carga el intento activo del estudiante.
     */
    private function cargarIntento(): ?array
    {
        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
                return $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            }
        } catch (\Throwable $e) {
            error_log('[InscripcionRifController::cargarIntento] ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Helper: renderiza un sub-paso del formulario con guard de RIF.
     * Si el RIF ya se generó, redirige al simulador.
     */
    private function renderPasoConGuard(\App\Core\App $app, string $vista): string
    {
        $intentoActivo = $this->cargarIntento();

        if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
            header('Location: ' . base_url('/simulador'));
            exit;
        }

        return $app->view($vista, ['intento' => $intentoActivo]);
    }

    /**
     * GET /simulador/inscripcion-rif
     * Página principal de inscripción (sin guard de RIF).
     */
    public function index(\App\Core\App $app): string
    {
        $intentoActivo = $this->cargarIntento();
        return $app->view('simulator/legacy/inscripcion_rif', ['intento' => $intentoActivo]);
    }

    /**
     * GET+POST /simulador/inscripcion-rif/datos-basicos
     */
    public function datosBasicos(\App\Core\App $app): string
    {
        return $this->renderPasoConGuard($app, 'simulator/legacy/formulario_inscripcion_rif/datos_causante');
    }

    /**
     * GET+POST /simulador/inscripcion-rif/direcciones
     */
    public function direcciones(\App\Core\App $app): string
    {
        return $this->renderPasoConGuard($app, 'simulator/legacy/formulario_inscripcion_rif/direcciones');
    }

    /**
     * GET+POST /simulador/inscripcion-rif/relaciones
     */
    public function relaciones(\App\Core\App $app): string
    {
        return $this->renderPasoConGuard($app, 'simulator/legacy/formulario_inscripcion_rif/relaciones');
    }

    /**
     * GET+POST /simulador/inscripcion-rif/validar-inscripcion
     */
    public function validarInscripcion(\App\Core\App $app): string
    {
        return $this->renderPasoConGuard($app, 'simulator/legacy/formulario_inscripcion_rif/validar_inscripcion');
    }
}
