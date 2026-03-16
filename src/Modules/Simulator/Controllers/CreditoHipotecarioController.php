<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\CreditoHipotecarioModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Crédito Hipotecario (Pasivos Deuda) view and CRUD API.
 * Similar to TDC but without numero_tdc field. Tipo Deuda value=2.
 */
class CreditoHipotecarioController
{
    private CreditoHipotecarioModel $model;

    public function __construct()
    {
        $this->model = new CreditoHipotecarioModel();
    }

    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $bancos = [];
        try {
            $catalog = new CatalogModel();
            $bancos = $catalog->getBancos();
        } catch (\Throwable $e) {
            error_log('[CreditoHipotecarioController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/pasivos_deuda/credito_hipotecario', [
            'intento' => $intento,
            'bancos'  => $bancos,
        ]);
    }

    /** POST /api/credito_hipotecario/{intentoId}/agregar */
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
            $items = $borrador['pasivos_deuda_ch'] ?? [];

            $items[] = $this->buildCh($input);

            $borrador['pasivos_deuda_ch'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CreditoHipotecario::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/credito_hipotecario/{intentoId}/editar */
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
            $items = $borrador['pasivos_deuda_ch'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $items[$idx] = $this->buildCh($input, $items[$idx]);

            $borrador['pasivos_deuda_ch'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CreditoHipotecario::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/credito_hipotecario/{intentoId}/eliminar */
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
            $items = $borrador['pasivos_deuda_ch'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($items, $idx, 1);
            $borrador['pasivos_deuda_ch'] = array_values($items);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CreditoHipotecario::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build a CH record (no numero_tdc field).
     */
    private function buildCh(array $input, array $existing = []): array
    {
        return [
            'cod_tipo_pasivo'    => $input['cod_tipo_pasivo'] ?? $existing['cod_tipo_pasivo'] ?? '1',
            'nombre_tipo_pasivo' => $input['nombre_tipo_pasivo'] ?? $existing['nombre_tipo_pasivo'] ?? 'Deudas',
            'cod_tipo_deuda'     => $input['cod_tipo_deuda'] ?? $existing['cod_tipo_deuda'] ?? '2',
            'nombre_tipo_deuda'  => $input['nombre_tipo_deuda'] ?? $existing['nombre_tipo_deuda'] ?? 'Crédito Hipotecario',
            'cod_banco'          => $input['cod_banco'] ?? $existing['cod_banco'] ?? '',
            'nombre_banco'       => $input['nombre_banco'] ?? $existing['nombre_banco'] ?? '',
            'porcentaje'         => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'        => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'valor_declarado'    => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
