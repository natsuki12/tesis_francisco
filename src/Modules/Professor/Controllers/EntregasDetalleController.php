<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers;

use App\Modules\Professor\Models\EntregasModel;
use App\Modules\Simulator\Services\DeclaracionComparador;

/**
 * Controlador de Entregas / Detalle de Intento del Profesor.
 * Mapeado a: GET /entregas/{id}, POST /entregas/{id}/calificar
 */
class EntregasDetalleController
{
    private EntregasModel $model;

    public function __construct()
    {
        $this->model = new EntregasModel();
    }

    /**
     * Muestra el detalle de un intento con comparación automática.
     */
    public function detalle(int $id): void
    {
        $intento = $this->model->getIntentoDetalle($id);
        if (!$intento) {
            http_response_code(404);
            require __DIR__ . '/../../../../resources/views/errors/404.php';
            return;
        }

        // Comparación: reutiliza el mismo motor que genera el PDF de retroalimentación
        $comparacion = [];
        try {
            $comparador = new DeclaracionComparador();
            $comparacion = $comparador->comparar($id, (int) $intento['estudiante_id']);
        } catch (\Throwable $e) {
            // Si falla la comparación (ej: borrador vacío), seguimos sin ella
            $comparacion = [
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
        }

        require __DIR__ . '/../../../../resources/views/professor/detalle_intento.php';
    }

    /**
     * Califica un intento: valida, guarda nota + observación,
     * y redirige al detalle con flash message.
     */
    public function calificar(int $id): void
    {
        $intento = $this->model->getIntentoDetalle($id);
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
