<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo para la vista de Entregas del profesor.
 *
 * Obtiene todos los intentos enviados por los estudiantes
 * que pertenecen a las asignaciones del profesor autenticado.
 */
class EntregasModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Resuelve profesores.id a partir de users.id (session user_id).
     */
    public function getProfesorId(int $userId): ?int
    {
        $sql = "SELECT p.id
                FROM profesores p
                INNER JOIN users u ON u.persona_id = p.persona_id
                WHERE u.id = :uid
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    /**
     * Obtiene todos los intentos enviados de los estudiantes
     * asignados al profesor (filtrado por cfg.profesor_id).
     *
     * Excluye intentos en En_Progreso (borradores, no enviados aún).
     */
    public function getEntregas(int $profesorId): array
    {
        $sql = "
            SELECT
                i.id                 AS intento_id,
                i.numero_intento     AS intento_actual,
                i.estado,
                i.submitted_at       AS fecha_envio,

                /* Estudiante */
                pe.nombres           AS estudiante_nombres,
                pe.apellidos         AS estudiante_apellidos,
                pe.cedula            AS estudiante_cedula,
                pe.nacionalidad      AS estudiante_nacionalidad,

                /* Caso */
                ce.titulo            AS caso_titulo,

                /* Config */
                cfg.max_intentos     AS intento_max,
                cfg.modalidad,

                /* Sección (más reciente del estudiante) */
                (
                    SELECT s.nombre
                    FROM inscripciones ins
                    INNER JOIN secciones s ON s.id = ins.seccion_id
                    WHERE ins.estudiante_id = est.id
                    ORDER BY ins.created_at DESC
                    LIMIT 1
                ) AS seccion

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce      ON ce.id  = cfg.caso_id
            INNER JOIN estudiantes est            ON est.id = a.estudiante_id
            INNER JOIN personas pe                ON pe.id  = est.persona_id

            WHERE cfg.profesor_id = :prof_id
              AND i.estado != 'En_Progreso'
              AND i.estado != 'Cancelado'
            ORDER BY i.submitted_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':prof_id', $profesorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas para las stat-cards del profesor.
     */
    public function getStats(int $profesorId): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN i.estado = 'Enviado'   THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN i.estado = 'En_Progreso' THEN 1 ELSE 0 END) AS en_progreso,
                SUM(CASE WHEN i.estado = 'Aprobado' OR i.estado = 'Rechazado' THEN 1 ELSE 0 END) AS calificadas,
                COUNT(*) AS total
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :prof_id
              AND i.estado != 'Cancelado'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':prof_id', $profesorId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'pendientes'  => (int) ($row['pendientes'] ?? 0),
            'en_progreso' => (int) ($row['en_progreso'] ?? 0),
            'calificadas' => (int) ($row['calificadas'] ?? 0),
            'total'       => (int) ($row['total'] ?? 0),
        ];
    }

    /**
     * Obtiene el detalle completo de un intento para la vista de corrección.
     */
    public function getIntentoDetalle(int $intentoId, ?int $profesorId = null): ?array
    {
        $sql = "
            SELECT
                i.id                 AS intento_id,
                i.numero_intento,
                i.estado,
                i.submitted_at       AS fecha_envio,
                i.nota_numerica,
                i.nota_cualitativa,
                i.observacion,
                i.borrador_json,
                i.rif_sucesoral,

                /* Estudiante */
                pe.nombres           AS estudiante_nombres,
                pe.apellidos         AS estudiante_apellidos,
                pe.cedula            AS estudiante_cedula,
                pe.nacionalidad      AS estudiante_nacionalidad,
                est.id               AS estudiante_id,

                /* Caso */
                ce.id                AS caso_id,
                ce.titulo            AS caso_titulo,

                /* Config */
                cfg.max_intentos,
                cfg.modalidad,
                cfg.tipo_calificacion,

                /* Sección */
                (
                    SELECT s.nombre
                    FROM inscripciones ins
                    INNER JOIN secciones s ON s.id = ins.seccion_id
                    WHERE ins.estudiante_id = est.id
                    ORDER BY ins.created_at DESC
                    LIMIT 1
                ) AS seccion

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce      ON ce.id  = cfg.caso_id
            INNER JOIN estudiantes est            ON est.id = a.estudiante_id
            INNER JOIN personas pe                ON pe.id  = est.persona_id

            WHERE i.id = :intento_id"
            . ($profesorId !== null ? " AND cfg.profesor_id = :prof_id" : "") . "
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':intento_id', $intentoId, PDO::PARAM_INT);
        if ($profesorId !== null) {
            $stmt->bindValue(':prof_id', $profesorId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Califica un intento: guarda nota + observación y actualiza el estado.
     *
     * @param string $tipo  'numerica' | 'aprobado_reprobado'
     */
    public function calificar(int $intentoId, string $tipo, ?float $notaNum, ?string $notaCual, string $observacion): bool
    {
        // Determinar nuevo estado
        if ($tipo === 'numerica') {
            $estado = ($notaNum !== null && $notaNum >= 10) ? 'Aprobado' : 'Rechazado';
        } else {
            $estado = ($notaCual === 'Aprobado') ? 'Aprobado' : 'Rechazado';
        }

        $sql = "
            UPDATE sim_intentos
            SET nota_numerica   = :nota_num,
                nota_cualitativa = :nota_cual,
                observacion     = :obs,
                estado          = :estado
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nota_num', $notaNum, $notaNum !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':nota_cual', $notaCual, $notaCual !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':obs', $observacion, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':id', $intentoId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
