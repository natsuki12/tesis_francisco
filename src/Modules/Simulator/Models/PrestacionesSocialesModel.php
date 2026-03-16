<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Model for Prestaciones Sociales (Bienes Muebles) CRUD operations.
 * Wraps the StudentAttemptModel for loading/saving borrador_json.
 */
class PrestacionesSocialesModel
{
    private StudentAttemptModel $attemptModel;
    private StudentAssignmentModel $assignModel;

    public function __construct()
    {
        $this->attemptModel = new StudentAttemptModel();
        $this->assignModel  = new StudentAssignmentModel();
    }

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
            error_log('[PrestacionesSocialesModel::getIntento] ' . $e->getMessage());
            throw $e;
        }
    }

    public function getIntentoActivo(): ?array
    {
        try {
            $estudianteId = $this->assignModel->getEstudianteId((int) $_SESSION['user_id']);
            if (!$estudianteId || empty($_SESSION['sim_asignacion_id'])) {
                return null;
            }

            return $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
        } catch (\Throwable $e) {
            error_log('[PrestacionesSocialesModel::getIntentoActivo] ' . $e->getMessage());
            return null;
        }
    }

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
            error_log('[PrestacionesSocialesModel::guardarBorrador] ' . $e->getMessage());
            throw $e;
        }
    }
}
