<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Model for Herederos Premuertos CRUD operations.
 * Wraps the StudentAttemptModel for loading/saving borrador_json.
 */
class HerederosPremuertosModel
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
            error_log('[HerederosPremuertosModel::getIntento] ' . $e->getMessage());
            throw $e;
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
            error_log('[HerederosPremuertosModel::guardarBorrador] ' . $e->getMessage());
            throw $e;
        }
    }
}
