<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\BienesInmueblesModel;
use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

/**
 * Controller for Bienes Inmuebles view and CRUD API.
 * Handles loading the view with borrador data and API operations.
 */
class BienesInmueblesController
{
    private BienesInmueblesModel $model;

    public function __construct()
    {
        $this->model = new BienesInmueblesModel();
    }

    /**
     * Load the Bienes Inmuebles view with data from the active intento.
     */
    public function index(object $app): string
    {
        $intento = $this->model->getIntentoActivo();

        $tiposBienInmueble = [];
        try {
            $catalog = new CatalogModel();
            $tiposBienInmueble = $catalog->getTiposBienInmueble();
        } catch (\Throwable $e) {
            error_log('[BienesInmueblesController::index] Error cargando catálogos: ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/sucesion/bienes_inmuebles/bienes_inmuebles', [
            'tiposBienInmueble' => $tiposBienInmueble,
            'intento' => $intento,
        ]);
    }

    /**
     * POST /api/bienes-inmuebles/{intentoId}/agregar
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
            $inmuebles = $borrador['bienes_inmuebles'] ?? [];

            $inmuebles[] = $this->buildInmueble($input);

            $borrador['bienes_inmuebles'] = $inmuebles;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[BienesInmuebles::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/bienes-inmuebles/{intentoId}/editar
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
            $inmuebles = $borrador['bienes_inmuebles'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($inmuebles[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Inmueble no encontrado']);
                return;
            }

            $inmuebles[$idx] = $this->buildInmueble($input, $inmuebles[$idx]);

            $borrador['bienes_inmuebles'] = $inmuebles;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[BienesInmuebles::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/bienes-inmuebles/{intentoId}/eliminar
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
            $inmuebles = $borrador['bienes_inmuebles'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($inmuebles[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Inmueble no encontrado']);
                return;
            }

            array_splice($inmuebles, $idx, 1);
            $borrador['bienes_inmuebles'] = array_values($inmuebles);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[BienesInmuebles::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Build an inmueble array from input, merging with existing data for edits.
     */
    private function buildInmueble(array $input, array $existing = []): array
    {
        return [
            'tipo_bien_inmueble_id' => $input['tipo_bien_inmueble_id'] ?? $existing['tipo_bien_inmueble_id'] ?? [],
            'tipo_bien_nombres'     => $input['tipo_bien_nombres'] ?? $existing['tipo_bien_nombres'] ?? '',
            'vivienda_principal'    => $input['vivienda_principal'] ?? $existing['vivienda_principal'] ?? 'false',
            'bien_litigioso'        => $input['bien_litigioso'] ?? $existing['bien_litigioso'] ?? 'false',
            'porcentaje'            => $input['porcentaje'] ?? $existing['porcentaje'] ?? '0,01',
            'descripcion'           => $input['descripcion'] ?? $existing['descripcion'] ?? '',
            'linderos'              => $input['linderos'] ?? $existing['linderos'] ?? '',
            'superficie_construida' => $input['superficie_construida'] ?? $existing['superficie_construida'] ?? '',
            'superficie_no_construida' => $input['superficie_no_construida'] ?? $existing['superficie_no_construida'] ?? '',
            'area_superficie'       => $input['area_superficie'] ?? $existing['area_superficie'] ?? '',
            'direccion'             => $input['direccion'] ?? $existing['direccion'] ?? '',
            'oficina_registro'      => $input['oficina_registro'] ?? $existing['oficina_registro'] ?? '',
            'nro_registro'          => $input['nro_registro'] ?? $existing['nro_registro'] ?? '',
            'libro'                 => $input['libro'] ?? $existing['libro'] ?? '',
            'protocolo'             => $input['protocolo'] ?? $existing['protocolo'] ?? '',
            'fecha_registro'        => $input['fecha_registro'] ?? $existing['fecha_registro'] ?? '',
            'trimestre'             => $input['trimestre'] ?? $existing['trimestre'] ?? '',
            'asiento_registral'     => $input['asiento_registral'] ?? $existing['asiento_registral'] ?? '',
            'matricula'             => $input['matricula'] ?? $existing['matricula'] ?? '',
            'folio_real_anio'       => $input['folio_real_anio'] ?? $existing['folio_real_anio'] ?? '',
            'valor_original'        => $input['valor_original'] ?? $existing['valor_original'] ?? '0,00',
            'valor_declarado'       => $input['valor_declarado'] ?? $existing['valor_declarado'] ?? '0,00',
        ];
    }
}
