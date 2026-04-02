<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;

class HomeProfessorModel
{
    /**
     * Returns the last 5 distinct students who logged in most recently.
     * Only considers LOGIN_SUCCESS (tipo_evento_id = 1) events for
     * users with role_id = 3 (students).
     *
     * @return array<int, array{nombres: string, apellidos: string, cedula: string, nacionalidad: string, last_login: string}>
     */
    public function getRecentStudents(int $profesorId, int $limit = 5): array
    {
        $db = DB::connect();

        $sql = "
            SELECT
                p.nombres,
                p.apellidos,
                p.cedula,
                p.nacionalidad,
                MAX(b.created_at) AS last_login,
                UNIX_TIMESTAMP(MAX(b.created_at)) AS last_login_ts
            FROM bitacora_eventos b
            INNER JOIN users u ON b.user_id = u.id
            INNER JOIN personas p ON u.persona_id = p.id
            INNER JOIN estudiantes est ON est.persona_id = p.id
            INNER JOIN inscripciones ins ON ins.estudiante_id = est.id
            INNER JOIN secciones s ON s.id = ins.seccion_id
            WHERE b.tipo_evento_id = 1
              AND u.role_id = 3
              AND s.profesor_id = :pid
            GROUP BY b.user_id, p.nombres, p.apellidos, p.cedula, p.nacionalidad
            ORDER BY last_login DESC
            LIMIT :lim
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':pid', $profesorId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Resolves user_id → profesores.id
     */
    public function getProfesorId(int $userId): ?int
    {
        $db = DB::connect();
        $stmt = $db->prepare("
            SELECT pr.id FROM profesores pr
            INNER JOIN personas p ON p.id = pr.persona_id
            INNER JOIN users u ON u.persona_id = p.id
            WHERE u.id = :uid LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    /**
     * Dashboard stats for the professor home page.
     * Returns counts for: students, cases, pending RIF, pending grading.
     */
    public function getDashboardStats(int $profesorId): array
    {
        $db = DB::connect();

        // 1. Estudiantes activos (enrolled in professor's active sections)
        $sql1 = "
            SELECT COUNT(DISTINCT ins.estudiante_id) AS total
            FROM inscripciones ins
            INNER JOIN secciones s ON s.id = ins.seccion_id
            WHERE s.profesor_id = :pid
        ";
        $stmt = $db->prepare($sql1);
        $stmt->execute([':pid' => $profesorId]);
        $estudiantes = (int) ($stmt->fetchColumn() ?: 0);

        // 2. Casos asignados (unique cases in professor's configs)
        $sql2 = "
            SELECT COUNT(DISTINCT cfg.caso_id) AS total
            FROM sim_caso_configs cfg
            WHERE cfg.profesor_id = :pid
        ";
        $stmt = $db->prepare($sql2);
        $stmt->execute([':pid' => $profesorId]);
        $casos = (int) ($stmt->fetchColumn() ?: 0);

        // 3. Solicitudes de RIF pendientes
        $sql3 = "
            SELECT COUNT(*) AS total
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
              AND i.estado = 'Pendiente_RIF'
        ";
        $stmt = $db->prepare($sql3);
        $stmt->execute([':pid' => $profesorId]);
        $rifPendientes = (int) ($stmt->fetchColumn() ?: 0);

        // 4. Declaraciones por calificar (estado Pendiente_Calificacion)
        $sql4 = "
            SELECT COUNT(*) AS total
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
              AND i.estado = 'Pendiente_Calificacion'
        ";
        $stmt = $db->prepare($sql4);
        $stmt->execute([':pid' => $profesorId]);
        $porCalificar = (int) ($stmt->fetchColumn() ?: 0);

        return [
            'estudiantes'    => $estudiantes,
            'casos'          => $casos,
            'rif_pendientes' => $rifPendientes,
            'por_calificar'  => $porCalificar,
        ];
    }
}
