<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers\Asignaciones;

use App\Core\BitacoraModel;
use App\Modules\Professor\Models\Asignaciones\AsignacionesModel;
use App\Modules\Professor\Models\HomeProfessorModel;

/**
 * Controller CRUD para configuraciones de asignaciones.
 * Todas las respuestas son JSON.
 */
class AsignacionesController
{
    private AsignacionesModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new AsignacionesModel();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->profesorId = (new HomeProfessorModel())->getProfesorId($userId) ?? 0;
    }

    /** GET /api/casos/{id}/configs */
    public function index(int $casoId): void
    {
        $configs = $this->model->getConfigsConReglas($casoId);
        $this->json(['ok' => true, 'configs' => $configs]);
    }

    /** POST /api/casos/{id}/configs */
    public function store(int $casoId): void
    {
        $data = $this->input();
        $errors = $this->validateConfigData($data, false);

        if (!empty($errors)) {
            $this->json(['ok' => false, 'error' => implode(' ', $errors)], 422);
            return;
        }

        // Verify case ownership
        if (!$this->model->casoPertenece($casoId, $this->profesorId)) {
            $this->json(['ok' => false, 'error' => 'No tiene permisos sobre este caso.'], 403);
            return;
        }

        $result = $this->model->createConfig($casoId, $this->profesorId, $data);

        if ($result['ok']) {
            BitacoraModel::registrar(
                BitacoraModel::CONFIG_CREATED,
                'asignaciones',
                $this->profesorId,
                null,
                'sim_config_caso',
                (int) ($result['config_id'] ?? 0),
                detalle: 'Modalidad: ' . ($data['modalidad'] ?? '')
            );
        }

        $this->json($result, $result['ok'] ? 201 : 500);
    }

    /** PUT /api/configs/{id} */
    public function update(int $configId): void
    {
        $data = $this->input();
        $errors = $this->validateConfigData($data, true);

        if (!empty($errors)) {
            $this->json(['ok' => false, 'error' => implode(' ', $errors)], 422);
            return;
        }

        if (!$this->model->configPertenece($configId, $this->profesorId)) {
            $this->json(['ok' => false, 'error' => 'No tiene permisos sobre esta configuración.'], 403);
            return;
        }

        $result = $this->model->updateConfig($configId, $data);

        if ($result['ok']) {
            BitacoraModel::registrar(
                BitacoraModel::CONFIG_UPDATED,
                'asignaciones',
                $this->profesorId,
                null,
                'sim_config_caso',
                $configId
            );
        }

        $this->json($result, $result['ok'] ? 200 : 422);
    }

    /** DELETE /api/configs/{id} */
    public function destroy(int $configId): void
    {
        if (!$this->model->configPertenece($configId, $this->profesorId)) {
            $this->json(['ok' => false, 'error' => 'No tiene permisos sobre esta configuración.'], 403);
            return;
        }

        $result = $this->model->deleteOrDeactivateConfig($configId);

        if ($result['ok']) {
            BitacoraModel::registrar(
                BitacoraModel::CONFIG_DELETED,
                'asignaciones',
                $this->profesorId,
                null,
                'sim_config_caso',
                $configId
            );
        }

        $this->json($result, $result['ok'] ? 200 : 500);
    }

    /** POST /api/configs/{id}/estudiantes */
    public function addEstudiantes(int $configId): void
    {
        $data = $this->input();
        $ids = $data['estudiante_ids'] ?? [];
        if (empty($ids)) {
            $this->json(['ok' => false, 'error' => 'Debe seleccionar al menos un estudiante.'], 422);
            return;
        }

        if (!$this->model->configPertenece($configId, $this->profesorId)) {
            $this->json(['ok' => false, 'error' => 'No tiene permisos sobre esta configuración.'], 403);
            return;
        }

        $result = $this->model->addEstudiantes($configId, $ids);

        if ($result['ok'] ?? false) {
            BitacoraModel::registrar(
                BitacoraModel::ASSIGNMENT_CREATED,
                'asignaciones',
                $this->profesorId,
                null,
                'sim_config_caso',
                $configId,
                detalle: count($ids) . ' estudiante(s) asignado(s)'
            );
        }

        $this->json($result);
    }

    /** DELETE /api/configs/{id}/estudiantes/{aid} */
    public function removeEstudiante(int $configId, int $asignacionId): void
    {
        if (!$this->model->configPertenece($configId, $this->profesorId)) {
            $this->json(['ok' => false, 'error' => 'No tiene permisos sobre esta configuración.'], 403);
            return;
        }

        $result = $this->model->removeOrDeactivateEstudiante($asignacionId);

        if ($result['ok'] ?? false) {
            BitacoraModel::registrar(
                BitacoraModel::ASSIGNMENT_REMOVED,
                'asignaciones',
                $this->profesorId,
                null,
                'sim_asignaciones',
                $asignacionId
            );
        }

        $this->json($result);
    }

    /** GET /api/casos/{id}/estudiantes-disponibles */
    public function estudiantesDisponibles(int $casoId): void
    {
        $estudiantes = $this->model->getEstudiantesDisponibles($this->profesorId);
        $this->json(['ok' => true, 'estudiantes' => $estudiantes]);
    }

    /* ---- helpers ---- */

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validates config data for create/update.
     * @param bool $isEdit true for update, false for create
     */
    private function validateConfigData(array $data, bool $isEdit): array
    {
        $errors = [];
        $validModalidades = ['Practica_Libre', 'Practica_guiada', 'Evaluacion'];

        if (!$isEdit) {
            if (empty($data['modalidad']) || !in_array($data['modalidad'], $validModalidades)) {
                $errors[] = 'La modalidad es requerida y debe ser válida.';
            }
            if (empty($data['estudiante_ids'] ?? [])) {
                $errors[] = 'Debe seleccionar al menos un estudiante.';
            }
        }

        // max_intentos bounds
        $max = (int) ($data['max_intentos'] ?? 0);
        if ($max < 0 || $max > 100) {
            $errors[] = 'El máximo de intentos debe estar entre 0 y 100.';
        }

        // Date validation
        $apertura = !empty($data['fecha_apertura']) ? strtotime($data['fecha_apertura']) : null;
        $cierre = !empty($data['fecha_limite']) ? strtotime($data['fecha_limite']) : null;

        if ($apertura && $cierre && $cierre <= $apertura) {
            $errors[] = 'La fecha de cierre debe ser posterior a la fecha de apertura.';
        }

        // For new configs, dates must not be in the past
        if (!$isEdit) {
            $now = time();
            if ($apertura && $apertura < $now) {
                $errors[] = 'La fecha de apertura no puede ser anterior al momento actual.';
            }
            if ($cierre && $cierre < $now) {
                $errors[] = 'La fecha de cierre no puede ser anterior al momento actual.';
            }
        }

        // Nombre trim (sanitize whitespace-only)
        if (isset($data['nombre']) && trim($data['nombre']) === '') {
            // Will be handled as null in model
        }

        return $errors;
    }
}
