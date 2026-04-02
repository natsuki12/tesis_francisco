<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\EntregasModel;
use App\Modules\Professor\Models\HomeProfessorModel;
use App\Modules\Simulator\Services\DeclaracionComparador;

/**
 * Controlador de Entregas / Detalle de Intento del Profesor.
 * Mapeado a: GET /entregas/{id}, POST /entregas/{id}/calificar
 */
class EntregasDetalleController
{
    private EntregasModel $model;
    private int $profesorId;

    public function __construct()
    {
        $this->model = new EntregasModel();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->profesorId = (new HomeProfessorModel())->getProfesorId($userId) ?? 0;
    }

    /**
     * Muestra el detalle de un intento con comparación automática.
     */
    public function detalle(int $id): void
    {
        $intento = $this->model->getIntentoDetalle($id, $this->profesorId);
        if (!$intento) {
            http_response_code(404);
            require __DIR__ . '/../../../../resources/views/errors/404.php';
            return;
        }

        $emptyComparacion = [
            'secciones'         => [],
            'resumen_secciones' => [],
            'autoliquidacion'   => [],
            'herederos_calculo' => [],
            'score'             => [
                'correctas'      => 0,
                'con_errores'    => 0,
                'omitidas'       => 0,
                'de_mas'         => 0,
                'total_esperado' => 0,
                'porcentaje'     => 0,
            ],
        ];

        // Detectar intento rechazado en etapa de inscripción de RIF (sin declaración completa)
        $esRechazadoSinRif = ($intento['estado'] === 'Rechazado' && empty($intento['rif_sucesoral']));

        $causante    = ['campos' => [], 'vacio' => true];
        $relaciones  = ['representante' => [], 'herederos' => []];
        $direcciones = [];
        $comparacion = $emptyComparacion;

        if ($esRechazadoSinRif) {
            // Cargar los datos de revisión de RIF (causante, relaciones, direcciones)
            try {
                $rsModel    = new \App\Modules\Professor\Models\GeneracionRsModel();
                $profesorId = $rsModel->getProfesorId((int) ($_SESSION['user_id'] ?? 0));
                if ($profesorId) {
                    $validator = new \App\Modules\Simulator\Validators\RSValidator();
                    $rsData    = $validator->getComparacionParaRevision($id, $profesorId);
                    if ($rsData) {
                        $causante    = $rsData['causante']    ?? $causante;
                        $relaciones  = $rsData['relaciones']  ?? $relaciones;
                        $direcciones = $rsData['direcciones'] ?? $direcciones;
                    }
                }
            } catch (\Throwable $e) {
                // Secciones quedan vacías — la vista mostrará mensajes de "sin datos"
            }
        } else {
            // Comparación completa: reutiliza el motor que genera el PDF de retroalimentación
            try {
                $comparador  = new DeclaracionComparador();
                $comparacion = $comparador->comparar($id, (int) $intento['estudiante_id']);
            } catch (\Throwable $e) {
                // Si falla la comparación (ej: borrador vacío), seguimos sin ella
            }
        }

        require __DIR__ . '/../../../../resources/views/professor/detalle_intento.php';
    }

    /**
     * Califica un intento: valida, guarda nota + observación,
     * y redirige al detalle con flash message.
     */
    public function calificar(int $id): void
    {
        $intento = $this->model->getIntentoDetalle($id, $this->profesorId);
        if (!$intento) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Intento no encontrado']);
            return;
        }

        $tipo = $intento['tipo_calificacion'] ?? 'aprobado_reprobado';
        $observacion = trim($_POST['observacion'] ?? '');
        $notaNum = null;
        $notaCual = null;

        if ($tipo === 'numerica') {
            $nota = $_POST['nota_numerica'] ?? '';
            if ($nota === '' || !is_numeric($nota) || (float) $nota < 0 || (float) $nota > 20) {
                $_SESSION['flash_error'] = 'La nota debe ser un número entre 0 y 20.';
                header('Location: ' . base_url('/entregas/' . $id));
                exit;
            }
            $notaNum = round((float) $nota, 1);
        } else {
            $notaCual = $_POST['nota_cualitativa'] ?? '';
            if (!in_array($notaCual, ['Aprobado', 'Reprobado'])) {
                $_SESSION['flash_error'] = 'Debe seleccionar Aprobado o Reprobado.';
                header('Location: ' . base_url('/entregas/' . $id));
                exit;
            }
        }

        $this->model->calificar($id, $tipo, $notaNum, $notaCual, $observacion);

        $_SESSION['flash_success'] = 'Intento calificado exitosamente.';
        header('Location: ' . base_url('/entregas/' . $id));
        exit;
    }
}
