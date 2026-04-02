<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Configuracion;

use App\Modules\Admin\Models\CatalogosModel;
use App\Core\Csrf;

class CatalogosController
{
    /**
     * Muestra la vista de gestión de catálogos SENIAT.
     * Mapeado a la ruta: GET /admin/configuracion/catalogos
     */
    public function index()
    {
        try {
            $model = new CatalogosModel();

            // Tab 1: Unidad Tributaria
            $unidadesTributarias = $model->getUnidadesTributarias();

            // Tab 2: Fiscal
            $gruposTarifa = $model->getGruposTarifa();
            $tramosTarifa = $model->getTramosTarifa();
            $reducciones  = $model->getReducciones();

            // Tab 3: Bienes
            $tiposBienInmueble    = $model->getTiposBienInmueble();
            $categoriasBienMueble = $model->getCategoriasBienMueble();
            $tiposBienMueble      = $model->getTiposBienMueble();
            $tiposSemoviente      = $model->getTiposSemoviente();

            // Tab 4: Parentescos
            $parentescos = $model->getParentescos();

            // Tab 5: Pasivos y Herencias
            $tiposPasivoDeuda = $model->getTiposPasivoDeuda();
            $tiposPasivoGasto = $model->getTiposPasivoGasto();
            $tipoHerencias    = $model->getTipoHerencias();
        } catch (\Throwable $e) {
            error_log('[CatalogosController::index] ' . $e->getMessage());
            $unidadesTributarias = $gruposTarifa = $tramosTarifa = $reducciones = [];
            $tiposBienInmueble = $categoriasBienMueble = $tiposBienMueble = $tiposSemoviente = [];
            $parentescos = $tiposPasivoDeuda = $tiposPasivoGasto = $tipoHerencias = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/catalogos.php';
    }

    // ══════════════════════════════════════════════════════════
    //  CRUD Endpoints (AJAX POST → JSON)
    // ══════════════════════════════════════════════════════════

    /**
     * POST /admin/configuracion/catalogos/guardar-simple
     * Crea/edita registros en tablas simples (nombre + activo).
     */
    public function guardarSimple(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $tabla  = trim($_POST['tabla'] ?? '');
            $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre = trim($_POST['nombre'] ?? '');

            $model = new CatalogosModel();
            echo json_encode($model->upsertSimple($tabla, $id, $nombre));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::guardarSimple] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/configuracion/catalogos/toggle-activo
     * Activa/desactiva un registro en cualquier tabla permitida.
     */
    public function toggleActivo(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $tabla = trim($_POST['tabla'] ?? '');
            $id    = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }

            $model = new CatalogosModel();
            echo json_encode($model->toggleActivo($tabla, $id));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::toggleActivo] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/configuracion/catalogos/guardar-parentesco
     */
    public function guardarParentesco(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id             = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $clave          = trim($_POST['clave'] ?? '');
            $etiqueta       = trim($_POST['etiqueta'] ?? '');
            $grupoTarifaId  = !empty($_POST['grupo_tarifa_id']) ? (int)$_POST['grupo_tarifa_id'] : null;

            $model = new CatalogosModel();
            echo json_encode($model->upsertParentesco($id, $clave, $etiqueta, $grupoTarifaId));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::guardarParentesco] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/configuracion/catalogos/guardar-tipo-mueble
     */
    public function guardarTipoBienMueble(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre      = trim($_POST['nombre'] ?? '');
            $categoriaId = (int)($_POST['categoria_id'] ?? 0);

            $model = new CatalogosModel();
            echo json_encode($model->upsertTipoBienMueble($id, $nombre, $categoriaId));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::guardarTipoBienMueble] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/configuracion/catalogos/guardar-herencia
     */
    public function guardarTipoHerencia(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            $model = new CatalogosModel();
            echo json_encode($model->upsertTipoHerencia($id, $nombre, $descripcion));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::guardarTipoHerencia] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }

    /**
     * POST /admin/configuracion/catalogos/guardar-ut
     */
    public function guardarUT(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                return;
            }

            $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $anio        = (int)($_POST['anio'] ?? 0);
            $valor       = (float)($_POST['valor'] ?? 0);
            $fechaGaceta = trim($_POST['fecha_gaceta'] ?? '');

            $model = new CatalogosModel();
            echo json_encode($model->upsertUnidadTributaria($id, $anio, $valor, $fechaGaceta));
        } catch (\Throwable $e) {
            error_log('[CatalogosController::guardarUT] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }
}
