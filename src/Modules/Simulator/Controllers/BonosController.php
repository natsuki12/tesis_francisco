<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\BonosModel;

/**
 * Controller for Bonos (Bienes Muebles) view and CRUD API.
 * Handles loading the view with borrador data and API operations.
 */
class BonosController
{
    private BonosModel $model;

    public function __construct()
    {
        $this->model = new BonosModel();
    }

    /**
     * Load the Bonos view with data from the active intento.
     */
    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        return $app->view('simulator/seniat_actual/sucesion/bienes_muebles/bonos', [
            'intento' => $intento,
        ]);
    }

    /**
     * POST /api/bonos/{intentoId}/agregar
     */
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
            $items = $borrador['bienes_muebles_bonos'] ?? [];

            $items[] = $this->buildBono($input);

            $borrador['bienes_muebles_bonos'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Bonos::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/bonos/{intentoId}/editar
     */
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
            $items = $borrador['bienes_muebles_bonos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $items[$idx] = $this->buildBono($input, $items[$idx]);

            $borrador['bienes_muebles_bonos'] = $items;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Bonos::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/bonos/{intentoId}/eliminar
     */
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
            $items = $borrador['bienes_muebles_bonos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($items[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($items, $idx, 1);
            $borrador['bienes_muebles_bonos'] = array_values($items);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Bonos::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build a bono entry from input, merging with existing data for edits.
     */
    private function buildBono(array $input, array $existing = []): array
    {
        return [
            'tipo_bien'           => $input['tipo_bien'] ?? $existing['tipo_bien'] ?? '18',
            'tipo_bien_nombre'    => $input['tipo_bien_nombre'] ?? $existing['tipo_bien_nombre'] ?? 'Bonos',
            'tipo_bonos'          => $input['tipo_bonos'] ?? $existing['tipo_bonos'] ?? '',
            'numero_bonos'        => $input['numero_bonos'] ?? $existing['numero_bonos'] ?? '',
            'numero_serie'        => $input['numero_serie'] ?? $existing['numero_serie'] ?? '',
            'bien_litigioso'      => $input['bien_litigioso'] ?? $existing['bien_litigioso'] ?? 'false',
            'num_expediente'      => $input['num_expediente'] ?? $existing['num_expediente'] ?? '',
            'tribunal_causa'      => $input['tribunal_causa'] ?? $existing['tribunal_causa'] ?? '',
            'partes_juicio'       => $input['partes_juicio'] ?? $existing['partes_juicio'] ?? '',
            'estado_juicio'       => $input['estado_juicio'] ?? $existing['estado_juicio'] ?? '',
            'porcentaje'          => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'         => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'valor_declarado'     => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
