<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\PasivosGastosModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Pasivos Gastos.
 * Tipo Pasivo value=2 ("Gastos").
 * Borrador key: pasivos_gastos.
 */
class PasivosGastosController
{
    private PasivosGastosModel $model;

    public function __construct()
    {
        $this->model = new PasivosGastosModel();
    }

    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposGasto = [];
        try {
            $catalog = new CatalogModel();
            $tiposGasto = $catalog->getTiposPasivoGasto();
        } catch (\Throwable $e) {
            error_log('[PasivosGastosController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/pasivos_gastos/gastos', [
            'intento'    => $intento,
            'tiposGasto' => $tiposGasto,
        ]);
    }

    /** POST /api/pasivos_gastos/{intentoId}/agregar */
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
            $items = $borrador['pasivos_gastos'] ?? [];

            $items[] = $this->buildItem($input);

            $borrador['pasivos_gastos'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosGastos::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/pasivos_gastos/{intentoId}/editar */
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
            $items = $borrador['pasivos_gastos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $items[$idx] = $this->buildItem($input, $items[$idx]);

            $borrador['pasivos_gastos'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosGastos::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/pasivos_gastos/{intentoId}/eliminar */
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
            $items = $borrador['pasivos_gastos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($items, $idx, 1);
            $borrador['pasivos_gastos'] = array_values($items);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[PasivosGastos::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build a "Gastos" item.
     */
    private function buildItem(array $input, array $existing = []): array
    {
        return [
            'cod_tipo_pasivo'    => $input['cod_tipo_pasivo'] ?? $existing['cod_tipo_pasivo'] ?? '2',
            'nombre_tipo_pasivo' => $input['nombre_tipo_pasivo'] ?? $existing['nombre_tipo_pasivo'] ?? 'Gastos',
            'cod_tipo_gasto'     => $input['cod_tipo_gasto'] ?? $existing['cod_tipo_gasto'] ?? '7',
            'nombre_tipo_gasto'  => $input['nombre_tipo_gasto'] ?? $existing['nombre_tipo_gasto'] ?? 'Apertura de Testamento',
            'porcentaje'         => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'        => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'valor_declarado'    => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
