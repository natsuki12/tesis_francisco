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

        // Validar
        $validator = new CasoValidator();
        $errors = $validator->validate($data, $modo);

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Insertar en la BD
        try {
            $storeModel = new StoreCasoModel();
            $casoId = $storeModel->store($data, $profesorId);

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
}

