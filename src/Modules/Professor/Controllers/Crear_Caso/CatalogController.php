<?php
namespace App\Modules\Professor\Controllers\Crear_Caso;

use App\Modules\Professor\Models\Crear_Caso\CatalogModel;

class CatalogController
{
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function getUnidadesTributarias()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getUnidadesTributarias());
    }

    public function getTiposHerencia()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposHerencia());
    }

    public function getPaises()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getPaises());
    }

    public function getParentescos()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getParentescos());
    }

    public function getTiposBienInmueble()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposBienInmueble());
    }

    public function getCategoriasBienMueble()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getCategoriasBienMueble());
    }

    public function getTiposBienMueble()
    {
        $categoria_id = isset($_GET['categoria_id']) ? (int) $_GET['categoria_id'] : null;
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposBienMueble($categoria_id));
    }

    public function getBancos()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getBancos());
    }

    public function getEmpresas()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getEmpresas());
    }

    public function getTiposSemoviente()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposSemoviente());
    }

    public function getTiposPasivoDeuda()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposPasivoDeuda());
    }

    public function getTiposPasivoGasto()
    {
        $model = new CatalogModel();
        $this->jsonResponse($model->getTiposPasivoGasto());
    }

    public function getTarifasSucesion()
    {
        try {
            $model = new CatalogModel();
            $this->jsonResponse($model->getTarifasSucesion());
        } catch (\Throwable $e) {
            error_log('[CatalogController::getTarifasSucesion] Error: ' . $e->getMessage());
            $this->jsonResponse([]);
        }
    }

    public function getSeccionesProfesor()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $model = new CatalogModel();
        $this->jsonResponse($model->getSeccionesByProfesor($userId));
    }

    public function buscarEmpresaPorRif()
    {
        $rif = trim($_GET['rif'] ?? '');
        if (empty($rif)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'RIF requerido']);
            exit;
        }
        $model = new CatalogModel();
        $result = $model->getEmpresaByRif($rif);
        if ($result) {
            $this->jsonResponse($result);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No se encontró el RIF ingresado']);
            exit;
        }
    }

    public function getEstudiantesProfesor()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $model = new CatalogModel();
        $this->jsonResponse($model->getEstudiantesByProfesor($userId));
    }

    public function buscarPersonaPorCedula()
    {
        $tipo = $_GET['tipo'] ?? '';
        $cedula = trim($_GET['cedula'] ?? '');
        $pasaporte = trim($_GET['pasaporte'] ?? '');
        $rif = trim($_GET['rif'] ?? '');

        if (empty($cedula) && empty($pasaporte) && empty($rif)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Documento requerido']);
            exit;
        }

        $model = new CatalogModel();
        $result = $model->getPersonaByDocumento($tipo, $cedula, $pasaporte, $rif);

        if ($result) {
            $this->jsonResponse($result);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Persona no encontrada']);
            exit;
        }
    }
}
