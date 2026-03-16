<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\SeguroModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Seguro (Bienes Muebles) view and CRUD API.
 */
class SeguroController
{
    private SeguroModel $model;

    public function __construct()
    {
        $this->model = new SeguroModel();
    }

    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposBien = [];
        try {
            $catalog = new CatalogModel();
            $tiposBien = $catalog->getTiposBienMueble(2); // categoria 2 = Seguro
        } catch (\Throwable $e) {
            error_log('[SeguroController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_muebles/seguro', [
            'intento'   => $intento,
            'tiposBien' => $tiposBien,
        ]);
    }

    /** POST /api/seguro/{intentoId}/agregar */
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
            $seguros = $borrador['bienes_muebles_seguro'] ?? [];

            $seguros[] = $this->buildSeguro($input);

            $borrador['bienes_muebles_seguro'] = $seguros;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Seguro::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/seguro/{intentoId}/editar */
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
            $seguros = $borrador['bienes_muebles_seguro'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($seguros[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $seguros[$idx] = $this->buildSeguro($input, $seguros[$idx]);

            $borrador['bienes_muebles_seguro'] = $seguros;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Seguro::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/seguro/{intentoId}/eliminar */
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
            $seguros = $borrador['bienes_muebles_seguro'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($seguros[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($seguros, $idx, 1);
            $borrador['bienes_muebles_seguro'] = array_values($seguros);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Seguro::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function buildSeguro(array $input, array $existing = []): array
    {
        return [
            'tipo_bien'        => $input['tipo_bien'] ?? $existing['tipo_bien'] ?? '',
            'tipo_bien_nombre' => $input['tipo_bien_nombre'] ?? $existing['tipo_bien_nombre'] ?? '',
            'rif_empresa'      => $input['rif_empresa'] ?? $existing['rif_empresa'] ?? '',
            'razon_social'     => $input['razon_social'] ?? $existing['razon_social'] ?? '',
            'numero_prima'     => $input['numero_prima'] ?? $existing['numero_prima'] ?? '',
            'bien_litigioso'   => $input['bien_litigioso'] ?? $existing['bien_litigioso'] ?? 'false',
            'num_expediente'   => $input['num_expediente'] ?? $existing['num_expediente'] ?? '',
            'tribunal_causa'   => $input['tribunal_causa'] ?? $existing['tribunal_causa'] ?? '',
            'partes_juicio'    => $input['partes_juicio'] ?? $existing['partes_juicio'] ?? '',
            'estado_juicio'    => $input['estado_juicio'] ?? $existing['estado_juicio'] ?? '',
            'porcentaje'       => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'      => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'valor_declarado'  => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
