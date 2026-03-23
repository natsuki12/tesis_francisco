<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la lectura de períodos académicos.
 */
class PeriodosModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los períodos con conteo de estudiantes inscritos y secciones.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.id, p.nombre, p.fecha_inicio, p.fecha_fin, p.activo,
                       p.created_at, p.updated_at,
                       (SELECT COUNT(*) FROM secciones s WHERE s.periodo_id = p.id) AS total_secciones,
                       (SELECT COUNT(*) FROM inscripciones i
                        INNER JOIN secciones s2 ON s2.id = i.seccion_id
                        WHERE s2.periodo_id = p.id) AS total_inscritos
                FROM periodos p
                ORDER BY p.activo DESC, p.fecha_inicio DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[PeriodosModel::getAll] ' . $e->getMessage());
            return [];
        }
    }
}
