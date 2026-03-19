<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers\Casos;

use App\Core\App;
use App\Modules\Professor\Models\Casos\CasosModel;
use App\Modules\Professor\Models\Casos\StoreCasoModel;
use App\Modules\Professor\Validators\CasoValidator;

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
        // La autenticación y verificación de rol se hacen en web.php ($requireAuth + $requireRole)
        $profesorId = (int) ($_SESSION['user_id'] ?? 0);

        // Obtener la información real de la BD
        $casos = $this->casosModel->getCasosByProfesor($profesorId);
        $stats = $this->casosModel->getStatsByProfesor($profesorId);

        return $this->app->view('professor/casos_sucesorales', [
            'casos' => $casos,
            'stats' => $stats
        ]);
    }

    /**
     * Retorna el JSON de un caso específico para edición.
     * GET /api/casos/{id}
     */
    public function show(int $id)
    {
        header('Content-Type: application/json');

        $profesorId = (int) ($_SESSION['user_id'] ?? 0);
        $result = $this->casosModel->getCasoJsonById($id, $profesorId);

        if (!$result) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
            exit;
        }

        // Bloquear edición de casos publicados
        if ($result['estado'] === 'Publicado') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No se puede editar un caso publicado.']);
            exit;
        }

        if (!$result['data']) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Este caso no tiene datos editables.']);
            exit;
        }

        // Inyectar caso_id en el data para que el frontend lo use en re-saves
        $result['data']['caso_id'] = $result['caso_id'];

        echo json_encode([
            'success' => true,
            'caso_id' => $result['caso_id'],
            'estado' => $result['estado'],
            'data' => $result['data']
        ]);
        exit;
    }

    /**
     * Guarda un caso completo (Borrador o Publicar).
     * POST /api/casos
     */
    public function store()
    {
        header('Content-Type: application/json');

        $profesorId = (int) ($_SESSION['user_id'] ?? 0);

        // Leer el JSON del body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['El body de la petición no es JSON válido.']]);
            exit;
        }

        // Determinar modo
        $modo = ($data['caso']['estado'] ?? 'Borrador') === 'Publicado' ? 'Publicar' : 'Borrador';

        // Sanitizar: XSS escape + coerción de tipos numéricos
        $data = CasoValidator::sanitize($data);

        // Validar
        $profesorId = (int) $_SESSION['user_id'];
        $casoId = isset($data['caso_id']) ? (int) $data['caso_id'] : null;
        $validator = new CasoValidator();
        $errors = $validator->validate($data, $modo, $profesorId, $casoId);

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Insertar en la BD
        try {
            $storeModel = new StoreCasoModel();
            $inputCasoId = isset($data['caso_id']) ? (int) $data['caso_id'] : null;

            // Guard: bloquear re-publicación de un caso ya publicado
            if ($inputCasoId && $modo === 'Publicar') {
                $db = \App\Core\DB::connect();
                $chk = $db->prepare("SELECT estado FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof");
                $chk->execute(['id' => $inputCasoId, 'prof' => $profesorId]);
                $estadoActual = $chk->fetchColumn();
                if ($estadoActual === 'Publicado') {
                    echo json_encode(['success' => true, 'caso_id' => $inputCasoId, 'message' => 'Este caso ya fue publicado.']);
                    exit;
                }
            }

            if ($modo === 'Borrador') {
                $casoId = $storeModel->storeDraft($data, $profesorId, $inputCasoId);
            } else {
                $casoId = $storeModel->store($data, $profesorId, $inputCasoId);
            }

            echo json_encode([
                'success' => true,
                'caso_id' => $casoId,
                'message' => $modo === 'Publicar'
                    ? 'Caso publicado exitosamente.'
                    : 'Borrador guardado exitosamente.'
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'errors' => ['Error al guardar el caso: ' . $e->getMessage()]
            ]);
        }

        exit;
    }

    /**
     * Elimina un caso (soft delete: estado = 'Eliminado').
     * DELETE /api/casos/{id}
     */
    public function destroy(int $id)
    {
        header('Content-Type: application/json');
        $profesorId = (int) ($_SESSION['user_id'] ?? 0);

        try {
            $db = \App\Core\DB::connect();

            // Verificar que el caso existe y pertenece al profesor
            $stmt = $db->prepare("SELECT id FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof AND estado != 'Eliminado'");
            $stmt->execute(['id' => $id, 'prof' => $profesorId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
                exit;
            }

            // Soft delete: cambiar estado a 'Eliminado'
            $stmt = $db->prepare("UPDATE sim_casos_estudios SET estado = 'Eliminado' WHERE id = :id");
            $stmt->execute(['id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Caso eliminado exitosamente.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Cambia el estado de un caso (ej: Inactivar).
     * PATCH /api/casos/{id}/estado
     */
    public function updateEstado(int $id)
    {
        header('Content-Type: application/json');
        $profesorId = (int) ($_SESSION['user_id'] ?? 0);

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $nuevoEstado = $data['estado'] ?? '';

        // Solo permitir estados válidos
        if (!in_array($nuevoEstado, ['Inactivo', 'Publicado', 'Borrador'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        try {
            $db = \App\Core\DB::connect();

            $stmt = $db->prepare("SELECT id FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof AND estado != 'Eliminado'");
            $stmt->execute(['id' => $id, 'prof' => $profesorId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
                exit;
            }

            $stmt = $db->prepare("UPDATE sim_casos_estudios SET estado = :estado WHERE id = :id");
            $stmt->execute(['estado' => $nuevoEstado, 'id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Estado actualizado a ' . $nuevoEstado . '.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

