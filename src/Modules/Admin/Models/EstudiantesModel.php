<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la gestión administrativa de estudiantes.
 */
class EstudiantesModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los estudiantes con su información personal, carrera y período.
     *
     * @return array<int, array{
     *     user_id: int,
     *     nombres: string,
     *     apellidos: string,
     *     email: string,
     *     nacionalidad: string,
     *     cedula: string,
     *     genero: string|null,
     *     carrera: string,
     *     periodo: string|null,
     *     status: string,
     *     created_at: string
     * }>
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                u.id           AS user_id,
                p.nombres,
                p.apellidos,
                u.email,
                p.nacionalidad,
                p.cedula,
                p.genero,
                c.nombre       AS carrera,
                per.nombre     AS periodo,
                u.status,
                u.created_at
            FROM users u
            INNER JOIN personas p      ON p.id  = u.persona_id
            INNER JOIN estudiantes e   ON e.persona_id = p.id
            LEFT  JOIN carreras c      ON c.id  = e.carrera_id
            LEFT  JOIN inscripciones i ON i.estudiante_id = e.id
            LEFT  JOIN secciones s     ON s.id  = i.seccion_id
            LEFT  JOIN periodos per    ON per.id = s.periodo_id
            WHERE u.role_id = 3
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[EstudiantesModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta estudiantes por estado.
     *
     * @return array{total: int, activos: int, inactivos: int}
     */
    public function getConteo(): array
    {
        try {
            $sql = "
                SELECT
                    COUNT(*)                                  AS total,
                    SUM(CASE WHEN u.status = 'active'   THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN u.status = 'inactive' THEN 1 ELSE 0 END) AS inactivos
                FROM users u
                INNER JOIN personas p    ON p.id = u.persona_id
                INNER JOIN estudiantes e ON e.persona_id = p.id
                WHERE u.role_id = 3
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return [
                'total'     => (int) ($row['total'] ?? 0),
                'activos'   => (int) ($row['activos'] ?? 0),
                'inactivos' => (int) ($row['inactivos'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('[EstudiantesModel::getConteo] ' . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }
}
