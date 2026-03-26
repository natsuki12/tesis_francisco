<?php
declare(strict_types=1);

namespace App\Modules\Shared\Controllers;

use App\Modules\Shared\Models\PerfilModel;

/**
 * Controller para la página de perfil (compartida: Profesor + Estudiante).
 * Patrón anti-crash: todo envuelto en try/catch para que un fallo de BD
 * nunca rompa la página — simplemente se muestran valores vacíos.
 */
class PerfilController
{
    /**
     * GET /perfil
     */
    public function index(\App\Core\App $app): string
    {
        $personaId = (int) ($_SESSION['persona_id'] ?? 0);
        $roleId    = (int) ($_SESSION['role_id'] ?? 3);

        // Valores por defecto (anti-crash)
        $personaRow    = [];
        $userCreatedAt = null;
        $estudianteRow = [];
        $profesorRow   = [];

        try {
            $model = new PerfilModel();

            $personaRow    = $model->getPersona($personaId);
            $userCreatedAt = $model->getUserCreatedAt($personaId);

            if ($roleId === 3) {
                $estudianteRow = $model->getEstudianteData($personaId);
            } elseif ($roleId === 2) {
                $profesorRow = $model->getProfesorData($personaId);
            }
        } catch (\Throwable $e) {
            error_log('[Shared\PerfilController::index] ' . $e->getMessage());
            // Los valores por defecto ya están definidos arriba — la vista se renderiza igual.
        }

        return $app->view('shared/perfil', [
            'personaRow'    => $personaRow,
            'userRow'       => ['created_at' => $userCreatedAt],
            'estudianteRow' => $estudianteRow,
            'profesorRow'   => $profesorRow,
        ]);
    }
}
