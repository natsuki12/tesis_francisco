<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models\Casos;

use App\Core\DB;
use PDO;

class CasosModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene los casos de estudio de un profesor.
     */
    public function getCasosByProfesor(int $profesorId): array
    {
        $sql = "
            SELECT 
                c.id,
                c.titulo,
                c.estado,
                c.created_at,
                c.causante_id,
                p.nombres AS causante_nombres,
                p.apellidos AS causante_apellidos,
                p.cedula AS causante_cedula,
                conf.modalidad,
                (
                    SELECT COUNT(*) 
                    FROM sim_caso_participantes cp 
                    WHERE cp.caso_estudio_id = c.id 
                    AND cp.rol_en_caso = 'Heredero'
                ) as herederos_count
            FROM sim_casos_estudios c
            LEFT JOIN sim_personas p ON c.causante_id = p.id
            LEFT JOIN sim_caso_configs conf ON conf.caso_id = c.id
            WHERE c.profesor_id = :profesor_id
            AND c.estado != 'Eliminado'
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['profesor_id' => $profesorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las estadísticas generales de los casos del profesor.
     */
    public function getStatsByProfesor(int $profesorId): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_casos,
                SUM(CASE WHEN c.estado = 'Publicado' THEN 1 ELSE 0 END) as casos_publicados,
                SUM(CASE WHEN c.estado = 'Borrador' THEN 1 ELSE 0 END) as casos_borrador,
                (
                    SELECT COUNT(DISTINCT a.estudiante_id)
                    FROM sim_caso_asignaciones a
                    INNER JOIN sim_caso_configs conf ON a.config_id = conf.id
                    INNER JOIN sim_casos_estudios c2 ON conf.caso_id = c2.id
                    WHERE c2.profesor_id = :profesor_id_2
                ) as total_estudiantes_asignados
            FROM sim_casos_estudios c
            WHERE c.profesor_id = :profesor_id
            AND c.estado != 'Eliminado'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'profesor_id' => $profesorId,
            'profesor_id_2' => $profesorId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_casos' => (int) ($result['total_casos'] ?? 0),
            'casos_publicados' => (int) ($result['casos_publicados'] ?? 0),
            'casos_borrador' => (int) ($result['casos_borrador'] ?? 0),
            'estudiantes_asignados' => (int) ($result['total_estudiantes_asignados'] ?? 0)
        ];
    }

    /**
     * Obtiene el JSON del borrador de un caso específico.
     * Retorna null si no existe o no pertenece al profesor.
     */
    public function getCasoJsonById(int $casoId, int $profesorId): ?array
    {
        $sql = "
            SELECT id, titulo, estado, borrador_json
            FROM sim_casos_estudios
            WHERE id = :id AND profesor_id = :prof AND estado != 'Eliminado'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $casoId, 'prof' => $profesorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row)
            return null;

        return [
            'caso_id' => (int) $row['id'],
            'titulo' => $row['titulo'],
            'estado' => $row['estado'],
            'data' => $row['borrador_json'] ? json_decode($row['borrador_json'], true) : null
        ];
    }
}
