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

    // ════════════════════════════════════════════════════════════
    //  GESTIÓN DE ESTUDIANTES POR SECCIÓN
    // ════════════════════════════════════════════════════════════

    /**
     * GET /admin/secciones/estudiantes?seccion_id=X
     * Lista estudiantes inscritos en una sección.
     */
    public function getEstudiantes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $seccionId = (int) ($_GET['seccion_id'] ?? 0);
            if ($seccionId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de sección inválido.']);
                return;
            }
            $model = new SeccionesModel();
            $estudiantes = $model->getEstudiantesSeccion($seccionId);
            echo json_encode(['success' => true, 'data' => $estudiantes]);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::getEstudiantes] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * GET /admin/secciones/buscar-estudiantes?seccion_id=X&q=texto
     * Busca estudiantes NO inscritos en la sección.
     */
    public function buscarEstudiantes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $seccionId = (int) ($_GET['seccion_id'] ?? 0);
            $q = trim($_GET['q'] ?? '');
            if ($seccionId <= 0) {
                echo json_encode([]);
                return;
            }
            $model = new SeccionesModel();
            $results = $model->buscarEstudiantesDisponibles($seccionId, $q);
            echo json_encode($results);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::buscarEstudiantes] ' . $e->getMessage());
            echo json_encode([]);
        }
    }

    /**
     * POST /admin/secciones/inscribir
     * Inscribe un estudiante en una sección.
     */
    public function inscribir()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }
            $seccionId    = (int) ($_POST['seccion_id'] ?? 0);
            $estudianteId = (int) ($_POST['estudiante_id'] ?? 0);

            if ($seccionId <= 0 || $estudianteId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                return;
            }

            $model = new SeccionesModel();
            $result = $model->inscribirEstudiante($seccionId, $estudianteId);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::inscribir] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/secciones/desinscribir
     * Remueve un estudiante de una sección.
     */
    public function desinscribir()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }
            $seccionId    = (int) ($_POST['seccion_id'] ?? 0);
            $estudianteId = (int) ($_POST['estudiante_id'] ?? 0);

            if ($seccionId <= 0 || $estudianteId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                return;
            }

            $model = new SeccionesModel();
            $result = $model->desinscribirEstudiante($seccionId, $estudianteId);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::desinscribir] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/secciones/sync-estudiantes
     * Sincroniza la lista final de estudiantes de una sección (batch).
     * Body: JSON { csrf_token, seccion_id, estudiante_ids: [int] }
     */
    public function syncEstudiantes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (!Csrf::verify($input['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $seccionId     = (int) ($input['seccion_id'] ?? 0);
            $estudianteIds = $input['estudiante_ids'] ?? [];

            if ($seccionId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de sección inválido.']);
                return;
            }

            if (!is_array($estudianteIds)) {
                echo json_encode(['success' => false, 'message' => 'Lista de estudiantes inválida.']);
                return;
            }

            $model = new SeccionesModel();
            $result = $model->syncEstudiantes($seccionId, $estudianteIds);

            if ($result['success'] && $result['message'] !== 'Sin cambios.') {
                BitacoraModel::registrar(BitacoraModel::SECTION_UPDATED, 'gestión_secciones', null, null, 'secciones', $seccionId, $result['message']);
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[SeccionesController::syncEstudiantes] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }
}
