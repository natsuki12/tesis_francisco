<?php
declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo para obtener las asignaciones del estudiante autenticado.
 *
 * Tablas involucradas:
 *   users → estudiantes (via persona_id)
 *   sim_caso_asignaciones → sim_caso_configs → sim_casos_estudios
 *   sim_intentos (conteo de intentos usados + mejor nota + borrador)
 *   profesores → personas (nombre del profesor)
 *   secciones (nombre de sección del profesor)
 */
class StudentAssignmentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Resuelve el `estudiantes.id` a partir de `users.id` (session user_id).
     */
    public function getEstudianteId(int $userId): ?int
    {
        $sql = "SELECT e.id
                FROM estudiantes e
                INNER JOIN users u ON u.persona_id = e.persona_id
                WHERE u.id = :uid
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    /**
     * Obtiene todas las asignaciones del estudiante con datos enriquecidos.
     *
     * Cada fila devuelve:
     *  - asignacion_id, estado (de la asignación)
     *  - caso_titulo, caso_descripcion
     *  - profesor_nombre (nombres + apellidos)
     *  - modalidad (Practica_Libre / Evaluacion)
     *  - max_intentos (0 = ilimitados)
     *  - fecha_limite
     *  - config_status (Activo / Inactivo)
     *  - intentos_usados (COUNT de intentos creados)
     *  - mejor_nota (MAX nota de intentos calificados, si existe)
     *  - tiene_borrador (1 si hay un intento En_Proceso activo)
     *  - fecha_creacion (cuando se le asignó al estudiante)
     */
    public function getAsignaciones(int $estudianteId): array
    {
        $sql = "
            SELECT
                a.id                AS asignacion_id,
                a.estado            AS asignacion_estado,
                a.created_at        AS fecha_creacion,

                /* ── Caso ───────────────────── */
                ce.id               AS caso_id,
                ce.titulo           AS caso_titulo,
                ce.descripcion      AS caso_descripcion,

                /* ── Config ─────────────────── */
                cfg.id              AS config_id,
                cfg.modalidad,
                cfg.max_intentos,
                cfg.fecha_limite,
                cfg.fecha_apertura,
                cfg.status          AS config_status,

                /* ── Profesor ───────────────── */
                CONCAT(pp.nombres, ' ', pp.apellidos) AS profesor_nombre,

                /* ── Sección vía inscripción ── */
                (
                    SELECT s.nombre
                    FROM inscripciones ins
                    INNER JOIN secciones s ON s.id = ins.seccion_id
                    WHERE ins.estudiante_id = a.estudiante_id
                    ORDER BY ins.created_at DESC
                    LIMIT 1
                ) AS seccion_nombre,

                /* ── Estadísticas de intentos ─ */
                COALESCE(ints.intentos_usados, 0)   AS intentos_usados,
                ints.mejor_nota,
                COALESCE(ints.tiene_borrador, 0)     AS tiene_borrador,
                ints.ultimo_intento_fecha

            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg   ON cfg.id      = a.config_id
            INNER JOIN sim_casos_estudios ce  ON ce.id       = cfg.caso_id
            INNER JOIN profesores prof        ON prof.id     = cfg.profesor_id
            INNER JOIN personas pp            ON pp.id       = prof.persona_id

            /* Sub-select para estadísticas de intentos */
            LEFT JOIN (
                SELECT
                    i.asignacion_id,
                    COUNT(*)                                             AS intentos_usados,
                    MAX(CASE
                        WHEN ie_last.estado = 'Aprobado' THEN NULL
                        ELSE NULL
                    END)                                                 AS mejor_nota,
                    MAX(CASE
                        WHEN ie_last.estado = 'En_Proceso' THEN 1
                        ELSE 0
                    END)                                                 AS tiene_borrador,
                    MAX(i.updated_at)                                    AS ultimo_intento_fecha
                FROM sim_intentos i
                LEFT JOIN (
                    SELECT ie1.intento_id, ie1.estado
                    FROM sim_intento_estados ie1
                    INNER JOIN (
                        SELECT intento_id, MAX(id) AS max_id
                        FROM sim_intento_estados
                        GROUP BY intento_id
                    ) ie2 ON ie1.id = ie2.max_id
                ) ie_last ON ie_last.intento_id = i.id
                GROUP BY i.asignacion_id
            ) ints ON ints.asignacion_id = a.id

            WHERE a.estudiante_id = :est_id
              AND a.estado != 'Inactivo'
              AND cfg.status = 'Activo'
            ORDER BY
                FIELD(a.estado, 'En_Progreso', 'Pendiente', 'Completado', 'Vencido'),
                a.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el detalle de UNA asignación específica del estudiante.
     * Verifica que la asignación pertenezca al estudiante (seguridad).
     */
    public function getDetalleAsignacion(int $asignacionId, int $estudianteId): ?array
    {
        $sql = "
            SELECT
                a.id                AS asignacion_id,
                a.estado            AS asignacion_estado,
                a.created_at        AS fecha_creacion,

                ce.id               AS caso_id,
                ce.titulo           AS caso_titulo,

                cfg.id              AS config_id,
                cfg.modalidad,
                cfg.max_intentos,
                cfg.fecha_limite,
                cfg.fecha_apertura,
                cfg.status          AS config_status,
                cfg.instrucciones,

                CONCAT(pp.nombres, ' ', pp.apellidos) AS profesor_nombre,

                (
                    SELECT s.nombre
                    FROM inscripciones ins
                    INNER JOIN secciones s ON s.id = ins.seccion_id
                    WHERE ins.estudiante_id = a.estudiante_id
                    ORDER BY ins.created_at DESC
                    LIMIT 1
                ) AS seccion_nombre

            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg   ON cfg.id  = a.config_id
            INNER JOIN sim_casos_estudios ce  ON ce.id   = cfg.caso_id
            INNER JOIN profesores prof        ON prof.id = cfg.profesor_id
            INNER JOIN personas pp            ON pp.id   = prof.persona_id

            WHERE a.id = :asig_id
              AND a.estudiante_id = :est_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtiene todos los intentos de una asignación con su estado actual
     * y la observación más reciente del profesor.
     */
    public function getIntentos(int $asignacionId): array
    {
        $sql = "
            SELECT
                i.id               AS intento_id,
                i.numero_intento   AS numero,
                i.estado,
                i.paso_actual,
                i.pasos_completados,
                i.created_at       AS fecha_inicio,
                i.submitted_at     AS fecha_envio,
                i.reviewed_at      AS fecha_revision,

                /* Observación más reciente del profesor */
                (
                    SELECT obs.observacion
                    FROM sim_intento_observaciones obs
                    WHERE obs.intento_id = i.id
                    ORDER BY obs.created_at DESC
                    LIMIT 1
                ) AS observacion

            FROM sim_intentos i
            WHERE i.asignacion_id = :asig_id
            ORDER BY i.numero_intento ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la asignación más reciente del estudiante que tenga
     * un intento activo (En_Progreso). Usada para el card
     * "Continuar donde lo dejaste" en el home.
     *
     * Retorna: caso_titulo, fecha_limite, asignacion_id,
     *          ultima_edicion (updated_at del intento activo).
     * Retorna null si no hay ningún borrador activo.
     */
    public function getUltimaAsignacionAccedida(int $estudianteId): ?array
    {
        $sql = "
            SELECT
                a.id                AS asignacion_id,
                ce.titulo           AS caso_titulo,
                cfg.fecha_limite,
                i.updated_at        AS ultima_edicion

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce      ON ce.id  = cfg.caso_id

            WHERE a.estudiante_id = :est_id
              AND i.estado = 'En_Progreso'
              AND a.estado != 'Inactivo'
              AND cfg.status = 'Activo'
            ORDER BY i.updated_at DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtiene el historial de planillas del estudiante —
     * todos los intentos enviados/calificados con sus datos de caso.
     */
    public function getHistorialPlanillas(int $estudianteId): array
    {
        $sql = "
            SELECT
                i.id                AS intento_id,
                i.numero_intento    AS numero,
                i.estado,
                i.submitted_at      AS fecha_envio,
                i.reviewed_at       AS fecha_revision,

                ce.titulo           AS caso_titulo,
                a.id                AS asignacion_id,
                cfg.modalidad

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce      ON ce.id  = cfg.caso_id

            WHERE a.estudiante_id = :est_id
              AND i.estado != 'En_Progreso'
              AND i.estado != 'Cancelado'
            ORDER BY i.submitted_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

