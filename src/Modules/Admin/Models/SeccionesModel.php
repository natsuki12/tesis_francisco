<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la lectura de secciones académicas.
 */
class SeccionesModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todas las secciones con datos del período, profesor y conteo de inscritos.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT s.id,
                       s.nombre,
                       s.cupo_maximo,
                       s.created_at,
                       p.nombre   AS periodo,
                       p.activo   AS periodo_activo,
                       p.id       AS periodo_id,
                       pr.id      AS profesor_id,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS profesor_nombre,
                       pe.genero  AS profesor_genero,
                       (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = s.id) AS inscritos
                FROM secciones s
                INNER JOIN periodos  p  ON p.id  = s.periodo_id
                INNER JOIN profesores pr ON pr.id = s.profesor_id
                INNER JOIN personas  pe ON pe.id = pr.persona_id
                ORDER BY p.activo DESC, p.nombre DESC, s.nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los períodos para el selector del modal.
     */
    public function getPeriodos(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre, activo FROM periodos ORDER BY activo DESC, nombre DESC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getPeriodos] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los profesores para el selector del modal.
     */
    public function getProfesores(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pr.id,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS nombre_completo,
                       pr.titulo
                FROM profesores pr
                INNER JOIN personas pe ON pe.id = pr.persona_id
                ORDER BY pe.apellidos ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getProfesores] ' . $e->getMessage());
            return [];
        }
    }
}
