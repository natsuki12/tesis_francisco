<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\BancoModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Banco (Bienes Muebles) view and CRUD API.
 * Handles loading the view with borrador data and API operations.
 */
class BancoController
{
    private BancoModel $model;

    public function __construct()
    {
        $this->model = new BancoModel();
    }

    /**
     * Load the Banco view with data from the active intento.
     */
    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposBien = [];
        $bancos = [];
        try {
            $catalog = new CatalogModel();
            $tiposBien = $catalog->getTiposBienMueble(1); // categoria 1 = Banco
            $bancos = $catalog->getBancos();
        } catch (\Throwable $e) {
            error_log('[BancoController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_muebles/banco', [
            'intento'   => $intento,
            'tiposBien' => $tiposBien,
            'bancos'    => $bancos,
        ]);
    }

    /**
     * POST /api/banco/{intentoId}/agregar
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
            $bancos = $borrador['bienes_muebles_banco'] ?? [];

            $bancos[] = $this->buildBanco($input);

            $borrador['bienes_muebles_banco'] = $bancos;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Banco::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/banco/{intentoId}/editar
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
            $bancos = $borrador['bienes_muebles_banco'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($bancos[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $bancos[$idx] = $this->buildBanco($input, $bancos[$idx]);

            $borrador['bienes_muebles_banco'] = $bancos;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Banco::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/banco/{intentoId}/eliminar
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
            $bancos = $borrador['bienes_muebles_banco'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($bancos[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($bancos, $idx, 1);
            $borrador['bienes_muebles_banco'] = array_values($bancos);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Banco::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build a banco entry from input, merging with existing data for edits.
     */
    private function buildBanco(array $input, array $existing = []): array
    {
        return [
            'tipo_bien'        => $input['tipo_bien'] ?? $existing['tipo_bien'] ?? '',
            'tipo_bien_nombre' => $input['tipo_bien_nombre'] ?? $existing['tipo_bien_nombre'] ?? '',
            'banco'            => $input['banco'] ?? $existing['banco'] ?? '',
            'banco_nombre'     => $input['banco_nombre'] ?? $existing['banco_nombre'] ?? '',
            'numero_cuenta'    => $input['numero_cuenta'] ?? $existing['numero_cuenta'] ?? '',
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
