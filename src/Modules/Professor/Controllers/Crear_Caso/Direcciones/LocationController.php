<?php
namespace App\Modules\Professor\Controllers\Crear_Caso\Direcciones;

use App\Modules\Professor\Models\Crear_Caso\Direcciones\DireccionesModel;

class LocationController
{
    public function getEstados()
    {
        header('Content-Type: application/json');
        try {
            $model = new DireccionesModel();
            $estados = $model->obtenerEstados();
            echo json_encode(['success' => true, 'data' => $estados]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los estados: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getMunicipios()
    {
        header('Content-Type: application/json');
        try {
            $estado_id = isset($_GET['estado_id']) ? (int) $_GET['estado_id'] : 0;
            if ($estado_id <= 0) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            $model = new DireccionesModel();
            $municipios = $model->obtenerMunicipiosPorEstado($estado_id);
            echo json_encode(['success' => true, 'data' => $municipios]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los municipios: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getParroquias()
    {
        header('Content-Type: application/json');
        try {
            $municipio_id = isset($_GET['municipio_id']) ? (int) $_GET['municipio_id'] : 0;
            if ($municipio_id <= 0) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            $model = new DireccionesModel();
            $parroquias = $model->obtenerParroquiasPorMunicipio($municipio_id);
            echo json_encode(['success' => true, 'data' => $parroquias]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener las parroquias: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getCiudades()
    {
        header('Content-Type: application/json');
        try {
            $municipio_id = isset($_GET['municipio_id']) ? (int) $_GET['municipio_id'] : 0;
            if ($municipio_id <= 0) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            $model = new DireccionesModel();
            $ciudades = $model->obtenerCiudadesPorMunicipio($municipio_id);
            echo json_encode(['success' => true, 'data' => $ciudades]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener las ciudades: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getZonasPostales()
    {
        header('Content-Type: application/json');
        try {
            $estado_id = isset($_GET['estado_id']) ? (int) $_GET['estado_id'] : 0;
            if ($estado_id <= 0) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            $model = new DireccionesModel();
            $zonas = $model->obtenerZonasPostalesPorEstado($estado_id);
            echo json_encode(['success' => true, 'data' => $zonas]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener las zonas postales: ' . $e->getMessage()]);
        }
        exit;
    }
}
