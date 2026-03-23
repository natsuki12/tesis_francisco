<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers\Asignaciones;

use App\Core\BitacoraModel;
use App\Modules\Professor\Models\Asignaciones\AsignacionesModel;

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
        $this->profesorId = (int) ($_SESSION['user_id'] ?? 0);
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
        if (empty($data['modalidad'])) {
            $this->json(['ok' => false, 'error' => 'La modalidad es requerida.'], 422);
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
        $estudiantes = $this->model->getEstudiantesDisponibles($casoId, $this->profesorId);
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
}
