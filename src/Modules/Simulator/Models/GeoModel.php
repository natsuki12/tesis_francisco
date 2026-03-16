<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Core\DB;
use PDO;

/**
 * GeoModel — Resolves geographic IDs to names.
 * Tables: estados, municipios, parroquias, ciudades.
 */
class GeoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    public function getEstadoNombre(int $id): string
    {
        return $this->lookup('estados', $id);
    }

    public function getMunicipioNombre(int $id): string
    {
        return $this->lookup('municipios', $id);
    }

    public function getParroquiaNombre(int $id): string
    {
        return $this->lookup('parroquias', $id);
    }

    public function getCiudadNombre(int $id): string
    {
        return $this->lookup('ciudades', $id);
    }

    private function lookup(string $table, int $id): string
    {
        if ($id <= 0) return '';
        $allowed = ['estados', 'municipios', 'parroquias', 'ciudades'];
        if (!in_array($table, $allowed)) return '';

        $stmt = $this->db->prepare("SELECT nombre FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['nombre'] : '';
    }
}
