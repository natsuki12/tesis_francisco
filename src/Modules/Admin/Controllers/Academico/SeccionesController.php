<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Academico;

use App\Core\Csrf;
use App\Modules\Admin\Models\SeccionesModel;
use App\Core\BitacoraModel;

class SeccionesController
{
    /**
     * Muestra la vista de gestión administrativa de secciones.
     * GET /admin/secciones
     */
    public function index()
    {
        try {
            $model = new SeccionesModel();
            $secciones     = $model->getAll();
            $periodos      = $model->getPeriodos();
            $profesores    = $model->getProfesores();
            $materias      = $model->getMaterias();
            $periodoActivo = $model->getPeriodoActivo();
        } catch (\Throwable $e) {
            error_log('[SeccionesController::index] ' . $e->getMessage());
            $secciones     = [];
            $periodos      = [];
            $profesores    = [];
            $materias      = [];
            $periodoActivo = null;
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_secciones.php';
    }

    /**
     * Crea una nueva sección.
     * POST /admin/secciones/crear → JSON
     */
    public function crear()
    {
        header('Content-Type: application/json');

        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            $model = new SeccionesModel();

            // Obtener período activo automáticamente
            $periodoActivo = $model->getPeriodoActivo();
            if (!$periodoActivo) {
                echo json_encode(['success' => false, 'message' => 'No hay un período académico activo. Configure uno antes de crear secciones.']);
                exit;
            }

            $result = $model->create([
                'nombre'      => $_POST['nombre'] ?? '',
                'cupo_maximo' => $_POST['cupo_maximo'] ?? 40,
                'profesor_id' => $_POST['profesor_id'] ?? null,
                'materia_id'  => $_POST['materia_id'] ?? 0,
                'periodo_id'  => $periodoActivo['id'],
            ]);

            // Registrar en bitácora si fue exitoso
            if ($result['success']) {
                $nombre = trim($_POST['nombre'] ?? '');
                BitacoraModel::registrar(BitacoraModel::SECTION_CREATED, 'gestión_secciones', null, null, 'secciones', null, "Sección creada: {$nombre} (Período: {$periodoActivo['nombre']})");
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::crear] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }

        exit;
    }

    /**
     * Actualiza una sección existente.
     * POST /admin/secciones/actualizar → JSON
     */
    public function actualizar()
    {
        header('Content-Type: application/json');

        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de sección inválido.']);
                exit;
            }

            $model = new SeccionesModel();
            $result = $model->update($id, [
                'nombre'      => $_POST['nombre'] ?? '',
                'cupo_maximo' => $_POST['cupo_maximo'] ?? 40,
                'profesor_id' => $_POST['profesor_id'] ?? null,
                'materia_id'  => $_POST['materia_id'] ?? 0,
            ]);

            if ($result['success']) {
                $nombre = trim($_POST['nombre'] ?? '');
                BitacoraModel::registrar(BitacoraModel::SECTION_UPDATED, 'gestión_secciones', null, null, 'secciones', $id, "Sección editada: {$nombre}");
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::actualizar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }

        exit;
    }
}
