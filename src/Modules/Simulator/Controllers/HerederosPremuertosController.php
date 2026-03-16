<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Simulator\Models\HerederosPremuertosModel;

/**
 * API controller for Herederos Premuertos CRUD.
 * All methods receive the intento ID and operate on borrador_json.herederos_premuertos[].
 */
class HerederosPremuertosController
{
    private HerederosPremuertosModel $model;

    public function __construct()
    {
        $this->model = new HerederosPremuertosModel();
    }

    /**
     * POST /api/herederos-premuertos/{intentoId}/agregar
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
            $premuertos = $borrador['herederos_premuertos'] ?? [];

            $cedula = trim($input['cedula'] ?? '');
            if ($cedula === '0') $cedula = '';

            $premuertos[] = [
                'nombre'              => $input['nombre'] ?? '',
                'apellido'            => $input['apellido'] ?? '',
                'cedula'              => $cedula,
                'fecha_nacimiento'    => $input['fecha_nacimiento'] ?? '',
                'parentesco_id'       => (int) ($input['parentesco_id'] ?? 19),
                'premuerto_padre_id'  => $input['premuerto_padre_id'] ?? '',
            ];

            $borrador['herederos_premuertos'] = $premuertos;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[HerederosPremuertos::agregar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/herederos-premuertos/{intentoId}/editar
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
            $premuertos = $borrador['herederos_premuertos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($premuertos[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Heredero no encontrado']);
                return;
            }

            $cedula = trim($input['cedula'] ?? $premuertos[$idx]['cedula']);
            if ($cedula === '0') $cedula = '';

            $premuertos[$idx]['nombre']           = $input['nombre'] ?? $premuertos[$idx]['nombre'];
            $premuertos[$idx]['apellido']         = $input['apellido'] ?? $premuertos[$idx]['apellido'];
            $premuertos[$idx]['cedula']           = $cedula;
            $premuertos[$idx]['fecha_nacimiento'] = $input['fecha_nacimiento'] ?? $premuertos[$idx]['fecha_nacimiento'];
            $premuertos[$idx]['parentesco_id']    = (int) ($input['parentesco_id'] ?? $premuertos[$idx]['parentesco_id']);

            $borrador['herederos_premuertos'] = $premuertos;
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[HerederosPremuertos::editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * POST /api/herederos-premuertos/{intentoId}/eliminar
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
            $premuertos = $borrador['herederos_premuertos'] ?? [];
            $idx = (int) $input['index'];

            if (!isset($premuertos[$idx])) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Heredero no encontrado']);
                return;
            }

            array_splice($premuertos, $idx, 1);
            $borrador['herederos_premuertos'] = array_values($premuertos);
            $ok = $this->model->guardarBorrador(
                $intentoId,
                $borrador,
                (int) ($intento['paso_actual'] ?? 1),
                (string) ($intento['pasos_completados'] ?? '')
            );

            echo json_encode(['ok' => $ok]);
        } catch (\Throwable $e) {
            error_log('[HerederosPremuertos::eliminar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }
}
