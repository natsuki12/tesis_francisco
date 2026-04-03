<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Academico;

use App\Core\Csrf;
use App\Core\BitacoraModel;
use App\Modules\Admin\Models\PeriodosModel;

class PeriodosController
{
    /**
     * GET /admin/periodos — Vista de gestión.
     */
    public function index()
    {
        try {
            $model = new PeriodosModel();
            $periodos = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[PeriodosController::index] ' . $e->getMessage());
            $periodos = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/academico/gestionar_periodos.php';
    }

    /**
     * POST /admin/periodos/crear — Crear nuevo período.
     */
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // CSRF
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::verify($token)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                return;
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
            $fechaFin = trim($_POST['fecha_fin'] ?? '');

            // Validaciones
            if ($nombre === '' || $fechaInicio === '' || $fechaFin === '') {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            if (mb_strlen($nombre) > 20) {
                echo json_encode(['success' => false, 'message' => 'El código del período no puede exceder 20 caracteres.']);
                return;
            }

            if ($fechaInicio >= $fechaFin) {
                echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin.']);
                return;
            }

            $model = new PeriodosModel();
            $result = $model->create($nombre, $fechaInicio, $fechaFin);

            if ($result['success']) {
                BitacoraModel::registrar(
                    BitacoraModel::CONFIG_CREATED,
                    'gestión_secciones',
                    null, null,
                    'periodos', $result['id'] ?? null,
                    'Período creado: ' . $nombre
                );
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[PeriodosController::crear] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }

    /**
     * POST /admin/periodos/actualizar — Editar período existente.
     */
    public function actualizar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::verify($token)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                return;
            }

            $id = (int) ($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
            $fechaFin = trim($_POST['fecha_fin'] ?? '');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de período inválido.']);
                return;
            }

            if ($nombre === '' || $fechaInicio === '' || $fechaFin === '') {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            if (mb_strlen($nombre) > 20) {
                echo json_encode(['success' => false, 'message' => 'El código del período no puede exceder 20 caracteres.']);
                return;
            }

            if ($fechaInicio >= $fechaFin) {
                echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin.']);
                return;
            }

            $model = new PeriodosModel();
            $result = $model->update($id, $nombre, $fechaInicio, $fechaFin);

            if ($result['success']) {
                BitacoraModel::registrar(
                    BitacoraModel::CONFIG_UPDATED,
                    'gestión_secciones',
                    null, null,
                    'periodos', $id,
                    'Período editado: ' . $nombre
                );
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[PeriodosController::actualizar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }

    /**
     * POST /admin/periodos/toggle — Cerrar o reactivar un período.
     */
    public function toggleEstado()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::verify($token)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                return;
            }

            $id = (int) ($_POST['id'] ?? 0);
            $accion = $_POST['accion'] ?? '';

            if ($id <= 0 || !in_array($accion, ['cerrar', 'reactivar'], true)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
                return;
            }

            $activar = $accion === 'reactivar';

            $model = new PeriodosModel();
            $result = $model->toggleActivo($id, $activar);

            if ($result['success']) {
                $detalle = $activar ? 'Período reactivado (ID: ' . $id . ')' : 'Período cerrado (ID: ' . $id . ')';
                BitacoraModel::registrar(
                    BitacoraModel::CONFIG_UPDATED,
                    'gestión_secciones',
                    null, null,
                    'periodos', $id,
                    $detalle
                );
            }

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[PeriodosController::toggleEstado] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}
