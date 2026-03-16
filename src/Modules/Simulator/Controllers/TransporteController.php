<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\TransporteModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Transporte (Bienes Muebles) view and CRUD API.
 */
class TransporteController
{
    private TransporteModel $model;

    public function __construct()
    {
        $this->model = new TransporteModel();
    }

    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposBien = [];
        try {
            $catalog = new CatalogModel();
            $tiposBien = $catalog->getTiposBienMueble(3); // categoria 3 = Transporte
        } catch (\Throwable $e) {
            error_log('[TransporteController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_muebles/transporte', [
            'intento'   => $intento,
            'tiposBien' => $tiposBien,
        ]);
    }

    /** POST /api/transporte/{intentoId}/agregar */
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
            $transportes = $borrador['bienes_muebles_transporte'] ?? [];

            $transportes[] = $this->buildTransporte($input);

            $borrador['bienes_muebles_transporte'] = $transportes;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Transporte::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/transporte/{intentoId}/editar */
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
            $transportes = $borrador['bienes_muebles_transporte'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($transportes[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $transportes[$idx] = $this->buildTransporte($input, $transportes[$idx]);

            $borrador['bienes_muebles_transporte'] = $transportes;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Transporte::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /** POST /api/transporte/{intentoId}/eliminar */
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
            $transportes = $borrador['bienes_muebles_transporte'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($transportes[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($transportes, $idx, 1);
            $borrador['bienes_muebles_transporte'] = array_values($transportes);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[Transporte::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function buildTransporte(array $input, array $existing = []): array
    {
        return [
            'tipo_bien'        => $input['tipo_bien'] ?? $existing['tipo_bien'] ?? '',
            'tipo_bien_nombre' => $input['tipo_bien_nombre'] ?? $existing['tipo_bien_nombre'] ?? '',
            'anio'             => $input['anio'] ?? $existing['anio'] ?? '',
            'marca'            => $input['marca'] ?? $existing['marca'] ?? '',
            'modelo'           => $input['modelo'] ?? $existing['modelo'] ?? '',
            'serial'           => $input['serial'] ?? $existing['serial'] ?? '',
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
