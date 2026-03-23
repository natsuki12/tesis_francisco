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
    public function getRecentStudents(int $limit = 5): array
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
            WHERE b.tipo_evento_id = 1
              AND u.role_id = 3
            GROUP BY b.user_id, p.nombres, p.apellidos, p.cedula, p.nacionalidad
            ORDER BY last_login DESC
            LIMIT :lim
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
