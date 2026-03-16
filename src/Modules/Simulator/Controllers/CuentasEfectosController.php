<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\CuentasEfectosModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Cuentas y Efectos por Cobrar (Bienes Muebles) view and CRUD API.
 * Handles loading the view with borrador data and API operations.
 */
class CuentasEfectosController
{
    private CuentasEfectosModel $model;

    public function __construct()
    {
        $this->model = new CuentasEfectosModel();
    }

    /**
     * Load the Cuentas y Efectos view with data from the active intento.
     */
    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposBien = [];
        try {
            $catalog = new CatalogModel();
            $tiposBien = $catalog->getTiposBienMueble(5); // categoria 5 = Cuentas y Efectos
        } catch (\Throwable $e) {
            error_log('[CuentasEfectosController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_muebles/cuentas_efectos', [
            'intento'   => $intento,
            'tiposBien' => $tiposBien,
        ]);
    }

    /**
     * POST /api/cuentas-efectos/{intentoId}/agregar
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
            $cuentas = $borrador['bienes_muebles_cuentas_efectos'] ?? [];

            $cuentas[] = $this->buildCuenta($input);

            $borrador['bienes_muebles_cuentas_efectos'] = $cuentas;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CuentasEfectos::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/cuentas-efectos/{intentoId}/editar
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
            $cuentas = $borrador['bienes_muebles_cuentas_efectos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($cuentas[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            $cuentas[$idx] = $this->buildCuenta($input, $cuentas[$idx]);

            $borrador['bienes_muebles_cuentas_efectos'] = $cuentas;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CuentasEfectos::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/cuentas-efectos/{intentoId}/eliminar
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
            $cuentas = $borrador['bienes_muebles_cuentas_efectos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($cuentas[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
                return;
            }

            array_splice($cuentas, $idx, 1);
            $borrador['bienes_muebles_cuentas_efectos'] = array_values($cuentas);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[CuentasEfectos::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build a cuentas/efectos entry from input, merging with existing data for edits.
     */
    private function buildCuenta(array $input, array $existing = []): array
    {
        return [
            'tipo_bien'        => $input['tipo_bien'] ?? $existing['tipo_bien'] ?? '',
            'tipo_bien_nombre' => $input['tipo_bien_nombre'] ?? $existing['tipo_bien_nombre'] ?? '',
            'rif_cedula'       => $input['rif_cedula'] ?? $existing['rif_cedula'] ?? '',
            'nombre_apellido'  => $input['nombre_apellido'] ?? $existing['nombre_apellido'] ?? '',
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
