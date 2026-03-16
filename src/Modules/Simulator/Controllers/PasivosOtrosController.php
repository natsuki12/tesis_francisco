<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\PasivosOtrosModel;

/**
 * Controller for Pasivos Deuda → Otros.
 * Tipo Deuda value=4 ("Otro Especifique"). No Banco field.
 * Borrador key: pasivos_deuda_otros.
 */
class PasivosOtrosController
{
    private PasivosOtrosModel $model;

    public function __construct()
    {
        $this->model = new PasivosOtrosModel();
    }

    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        return $app->view('simulator/seniat_actual/sucesion/pasivos_deuda/otros', [
            'intento' => $intento,
        ]);
    }

    /** POST /api/pasivos_otros/{intentoId}/agregar */
    public function agregar(int $intentoId): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
                return;
            }

            $intento = $this->model->getIntento($intentoId);
            if (!$intento) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'Intento no válido']);
                return;
            }

            $borrador = json_decode($intento['borrador_json'] ?: '{}', true) ?: [];
            $items = $borrador['pasivos_deuda_otros'] ?? [];

            $items[] = $this->buildItem($input);

            $borrador['pasivos_deuda_otros'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosOtros::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/pasivos_otros/{intentoId}/editar */
    public function editar(int $intentoId): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['index'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'JSON inválido o índice faltante']);
                return;
            }

            $intento = $this->model->getIntento($intentoId);
            if (!$intento) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'Intento no válido']);
                return;
            }

            $borrador = json_decode($intento['borrador_json'] ?: '{}', true) ?: [];
            $items = $borrador['pasivos_deuda_otros'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $items[$idx] = $this->buildItem($input, $items[$idx]);

            $borrador['pasivos_deuda_otros'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosOtros::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/pasivos_otros/{intentoId}/eliminar */
    public function eliminar(int $intentoId): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['index'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'JSON inválido o índice faltante']);
                return;
            }

            $intento = $this->model->getIntento($intentoId);
            if (!$intento) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'Intento no válido']);
                return;
            }

            $borrador = json_decode($intento['borrador_json'] ?: '{}', true) ?: [];
            $items = $borrador['pasivos_deuda_otros'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($items, $idx, 1);
            $borrador['pasivos_deuda_otros'] = array_values($items);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosOtros::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build an "Otros" item. No banco field — simpler than PCE.
     */
    private function buildItem(array $input, array $existing = []): array
    {
        return [
            'cod_tipo_pasivo'    => $input['cod_tipo_pasivo'] ?? $existing['cod_tipo_pasivo'] ?? '1',
            'nombre_tipo_pasivo' => $input['nombre_tipo_pasivo'] ?? $existing['nombre_tipo_pasivo'] ?? 'Deudas',
            'cod_tipo_deuda'     => $input['cod_tipo_deuda'] ?? $existing['cod_tipo_deuda'] ?? '4',
            'nombre_tipo_deuda'  => $input['nombre_tipo_deuda'] ?? $existing['nombre_tipo_deuda'] ?? 'Otro Especifique',
            'porcentaje'         => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'        => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'valor_declarado'    => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
