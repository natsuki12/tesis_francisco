<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Model for Bienes Inmuebles CRUD operations.
 * Wraps the StudentAttemptModel for loading/saving borrador_json.
 */
class BienesInmueblesModel
{
    private StudentAttemptModel $attemptModel;
    private StudentAssignmentModel $assignModel;

    public function __construct()
    {
        $this->attemptModel = new StudentAttemptModel();
        $this->assignModel  = new StudentAssignmentModel();
    }

    /**
     * Get the intento if it belongs to the current user and is in progress.
     * @throws \Throwable on DB errors (caught by controller)
     */
    public function getIntento(int $intentoId): ?array
    {
        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            if (!$estudianteId) {
                return null;
            }

            $intento = $this->attemptModel->getIntento($intentoId, $estudianteId);
            if (!$intento || $intento['estado'] !== 'En_Progreso') {
                return null;
            }

            return $intento;
        } catch (\Throwable $e) {
            error_log('[BienesInmueblesModel::getIntento] ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the active intento for the current session.
     */
    public function getIntentoActivo(): ?array
    {
        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            if (!$estudianteId || empty($_SESSION['sim_asignacion_id'])) {
                return null;
            }

            return $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
        } catch (\Throwable $e) {
            error_log('[BienesInmueblesModel::getIntentoActivo] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save the full borrador JSON back to the intento.
     * @throws \Throwable on DB errors (caught by controller)
     */
    public function guardarBorrador(int $intentoId, array $borrador, int $pasoActual, string $pasosCompletados = ''): bool
    {
        try {
            return $this->attemptModel->guardarBorrador(
                $intentoId,
                json_encode($borrador, JSON_UNESCAPED_UNICODE),
                $pasoActual,
                $pasosCompletados
            );
        } catch (\Throwable $e) {
            error_log('[BienesInmueblesModel::guardarBorrador] ' . $e->getMessage());
            throw $e;
        }
    }
}
