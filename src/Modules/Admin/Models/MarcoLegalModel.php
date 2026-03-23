<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la lectura del marco legal del sistema.
 */
class MarcoLegalModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los registros del marco legal ordenados por orden.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, titulo, tipo, descripcion, url, estado,
                       orden, fecha_publicacion, numero_gaceta,
                       created_at, updated_at
                FROM sim_marco_legals
                ORDER BY orden ASC, id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MarcoLegalModel::getAll] ' . $e->getMessage());
            return [];
        }
    }
}
