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
}
