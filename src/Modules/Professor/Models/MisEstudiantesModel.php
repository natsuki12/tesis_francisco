<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo de Mis Estudiantes — lista de estudiantes del profesor.
 * Obtiene estudiantes vía secciones, con métricas de asignaciones e intentos.
 */
class MisEstudiantesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los estudiantes de las secciones del profesor
     * con métricas: asignaciones, intentos, completadas, pendientes, último acceso.
     */
    public function getEstudiantes(int $profesorId): array
    {
        $sql = "
            SELECT
                est.id AS id,
                pe.nombres,
                pe.apellidos,
                pe.cedula,
                pe.nacionalidad,
                sec.nombre AS seccion,

                /* N° Asignaciones (total de asignaciones activas) */
                (
                    SELECT COUNT(*)
                    FROM sim_caso_asignaciones a2
                    INNER JOIN sim_caso_configs cfg2 ON cfg2.id = a2.config_id
                    WHERE a2.estudiante_id = est.id
                      AND cfg2.profesor_id = :prof2
                      AND a2.estado != 'Inactivo'
                ) AS asignaciones,



                /* Asignaciones Completadas (tienen al menos un intento Aprobado o Rechazado) */
                (
                    SELECT COUNT(DISTINCT a4.id)
                    FROM sim_caso_asignaciones a4
                    INNER JOIN sim_caso_configs cfg4 ON cfg4.id = a4.config_id
                    INNER JOIN sim_intentos i4       ON i4.asignacion_id = a4.id
                    WHERE a4.estudiante_id = est.id
                      AND cfg4.profesor_id = :prof4
                      AND i4.estado IN ('Aprobado', 'Rechazado')
                ) AS asignaciones_completadas,

                /* Asignaciones Pendientes (asignadas pero sin intento calificado) */
                (
                    SELECT COUNT(*)
                    FROM sim_caso_asignaciones a5
                    INNER JOIN sim_caso_configs cfg5 ON cfg5.id = a5.config_id
                    WHERE a5.estudiante_id = est.id
                      AND cfg5.profesor_id = :prof5
                      AND a5.estado != 'Inactivo'
                      AND a5.id NOT IN (
                          SELECT i5.asignacion_id
                          FROM sim_intentos i5
                          WHERE i5.estado IN ('Aprobado', 'Rechazado')
                      )
                ) AS asignaciones_pendientes,

                /* Último acceso (última actividad en la bitácora) */
                (
                    SELECT MAX(be.created_at)
                    FROM bitacora_eventos be
                    INNER JOIN users u6 ON u6.email = be.attempted_email
                    WHERE u6.persona_id = pe.id
                ) AS ultimo_acceso

            FROM estudiantes est
            INNER JOIN personas pe        ON pe.id = est.persona_id
            INNER JOIN inscripciones ins  ON ins.estudiante_id = est.id
            INNER JOIN secciones sec      ON sec.id = ins.seccion_id
            WHERE sec.profesor_id = :prof1
            ORDER BY pe.apellidos ASC, pe.nombres ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'prof1' => $profesorId,
            'prof2' => $profesorId,
            'prof4' => $profesorId,
            'prof5' => $profesorId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula estadísticas a partir del array de estudiantes.
     */
    public function getStats(array $estudiantes): array
    {
        $total = count($estudiantes);
        $conPendientes = 0;
        $sinActividad = 0;

        foreach ($estudiantes as $est) {
            if ((int) ($est['asignaciones_pendientes'] ?? 0) > 0) $conPendientes++;
            if ((int) ($est['asignaciones'] ?? 0) > 0 && (int) ($est['asignaciones_completadas'] ?? 0) === 0) $sinActividad++;
        }

        return [
            'total'         => $total,
            'pendientes'    => $conPendientes,
            'sin_actividad' => $sinActividad,
        ];
    }

    /**
     * Detalle de un estudiante específico (validado contra profesor).
     */
    public function getEstudianteDetalle(int $estudianteId, int $profesorId): ?array
    {
        $sql = "
            SELECT
                est.id,
                pe.nombres,
                pe.apellidos,
                pe.cedula,
                pe.nacionalidad,
                u.email,
                sec.nombre AS seccion,
                ins.created_at AS fecha_inscripcion
            FROM estudiantes est
            INNER JOIN personas pe       ON pe.id = est.persona_id
            INNER JOIN users u           ON u.persona_id = pe.id
            INNER JOIN inscripciones ins ON ins.estudiante_id = est.id
            INNER JOIN secciones sec     ON sec.id = ins.seccion_id
            WHERE est.id = :est_id
              AND sec.profesor_id = :prof_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['est_id' => $estudianteId, 'prof_id' => $profesorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Entregas/intentos de un estudiante, filtrado por configs del profesor.
     */
    public function getEntregasEstudiante(int $estudianteId, int $profesorId): array
    {
        $sql = "
            SELECT
                i.id AS intento_id,
                i.numero_intento,
                i.estado,
                i.nota_numerica,
                i.nota_cualitativa,
                i.submitted_at,
                i.created_at,

                ce.titulo AS caso_titulo,
                cfg.max_intentos,
                cfg.tipo_calificacion

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce   ON ce.id = cfg.caso_id
            WHERE a.estudiante_id = :est_id
              AND cfg.profesor_id = :prof_id
              AND i.estado NOT IN ('Cancelado')
            ORDER BY i.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['est_id' => $estudianteId, 'prof_id' => $profesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
