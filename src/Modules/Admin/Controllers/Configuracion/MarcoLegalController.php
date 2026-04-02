<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Configuracion;

use App\Modules\Admin\Models\MarcoLegalModel;

class MarcoLegalController
{
    /**
     * GET /admin/configuracion/marco-legal
     */
    public function index()
    {
        try {
            $model = new MarcoLegalModel();
            $articulosMarcoLegal = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[MarcoLegalController::index] ' . $e->getMessage());
            $articulosMarcoLegal = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/marco_legal.php';
    }

    /**
     * POST /admin/configuracion/marco-legal/guardar
     * Crea o actualiza un artículo. Retorna JSON.
     */
    public function guardar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id = (int) ($_POST['id'] ?? 0);
            $titulo      = trim($_POST['titulo'] ?? '');
            $tipo        = trim($_POST['tipo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado      = trim($_POST['estado'] ?? 'Vigente');

            // Validación
            $errors = [];
            if ($titulo === '')      $errors[] = 'El título es obligatorio.';
            if ($tipo === '')        $errors[] = 'El tipo es obligatorio.';
            if ($descripcion === '') $errors[] = 'La descripción es obligatoria.';

            $tiposValidos = ['Ley', 'Codigo', 'Providencia', 'Gaceta_Oficial', 'Reglamento'];
            if ($tipo !== '' && !in_array($tipo, $tiposValidos, true)) {
                $errors[] = 'Tipo no válido.';
            }

            $estadosValidos = ['Vigente', 'Derogado'];
            if (!in_array($estado, $estadosValidos, true)) {
                $errors[] = 'Estado no válido.';
            }

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                return;
            }

            $data = [
                'titulo'            => $titulo,
                'tipo'              => $tipo,
                'descripcion'       => $descripcion,
                'url'               => trim($_POST['url'] ?? ''),
                'estado'            => $estado,
                'orden'             => (int) ($_POST['orden'] ?? 0),
                'fecha_publicacion' => trim($_POST['fecha_publicacion'] ?? ''),
                'numero_gaceta'     => trim($_POST['numero_gaceta'] ?? ''),
            ];

            $model = new MarcoLegalModel();

            if ($id > 0) {
                $ok = $model->update($id, $data);
                echo json_encode([
                    'success' => $ok,
                    'message' => $ok ? 'Artículo actualizado correctamente.' : 'Error al actualizar el artículo.',
                ]);
            } else {
                $newId = $model->create($data);
                echo json_encode([
                    'success' => $newId > 0,
                    'message' => $newId > 0 ? 'Artículo registrado correctamente.' : 'Error al registrar el artículo.',
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[MarcoLegalController::guardar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }

    /**
     * POST /admin/configuracion/marco-legal/eliminar
     * Elimina un artículo por ID. Retorna JSON.
     */
    public function eliminar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido.']);
                return;
            }

            $model = new MarcoLegalModel();
            $ok = $model->delete($id);
            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Artículo eliminado correctamente.' : 'Error al eliminar el artículo.',
            ]);
        } catch (\Throwable $e) {
            error_log('[MarcoLegalController::eliminar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}
