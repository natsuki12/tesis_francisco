<?php
namespace App\Modules\Professor\Models\Crear_Caso\Direcciones;

use App\Core\DB;

class DireccionesModel
{
    public function obtenerEstados()
    {
        $db = DB::connect();
        $stmt = $db->query("SELECT id, nombre FROM estados ORDER BY nombre ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerMunicipiosPorEstado(int $estado_id)
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id, nombre FROM municipios WHERE estado_id = ? ORDER BY nombre ASC");
        $stmt->execute([$estado_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerParroquiasPorMunicipio(int $municipio_id)
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id, nombre FROM parroquias WHERE municipio_id = ? ORDER BY nombre ASC");
        $stmt->execute([$municipio_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerCiudadesPorMunicipio(int $municipio_id)
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id, nombre, capital_estado FROM ciudades WHERE municipio_id = ? ORDER BY nombre ASC");
        $stmt->execute([$municipio_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerZonasPostalesPorEstado(int $estado_id)
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT MIN(id) AS id, codigo FROM codigos_postales WHERE estado_id = ? GROUP BY codigo ORDER BY codigo ASC");
        $stmt->execute([$estado_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
