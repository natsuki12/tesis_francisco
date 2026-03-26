<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;
use App\Core\Traits\ValidatesUsuarios;

/**
 * Model para la gestión administrativa de estudiantes.
 */
class EstudiantesModel
{
    use ValidatesUsuarios;

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

    /**
     * Paginación server-side para la DataTable de estudiantes.
     * Soporta búsqueda, filtro por estado, sort y paginación.
     *
     * @return array{rows: array, total: int, page: int, pages: int, conteo: array}
     */
    public function getPaginated(
        int $page = 1,
        int $limit = 15,
        string $search = '',
        string $sortCol = 'created_at',
        string $sortDir = 'DESC',
        string $status = ''
    ): array {
        $conteo = $this->getConteo();
        $default = ['rows' => [], 'total' => 0, 'page' => $page, 'pages' => 1, 'conteo' => $conteo];

        // Whitelist de columnas para ORDER BY (anti SQL injection)
        $allowedCols = [
            'nombres'    => 'p.nombres',
            'apellidos'  => 'p.apellidos',
            'cedula'     => 'p.cedula',
            'created_at' => 'u.created_at',
            'status'     => 'u.status',
        ];
        $orderColumn = $allowedCols[$sortCol] ?? 'u.created_at';
        $sortDir = ($sortDir === 'ASC') ? 'ASC' : 'DESC';

        try {
            $params = [];
            $conditions = ['u.role_id = 3'];

            // Filtro de búsqueda
            if ($search !== '') {
                $conditions[] = "(p.nombres LIKE :s1 OR p.apellidos LIKE :s2 OR p.cedula LIKE :s3 OR u.email LIKE :s4)";
                $like = '%' . $search . '%';
                $params[':s1'] = $like;
                $params[':s2'] = $like;
                $params[':s3'] = $like;
                $params[':s4'] = $like;
            }

            // Filtro de estado
            if ($status === 'active' || $status === 'inactive') {
                $conditions[] = "u.status = :status";
                $params[':status'] = $status;
            }

            $where = 'WHERE ' . implode(' AND ', $conditions);

            // Total con filtros
            $countSql = "SELECT COUNT(DISTINCT u.id) FROM users u
                         INNER JOIN personas p ON p.id = u.persona_id
                         INNER JOIN estudiantes e ON e.persona_id = p.id
                         {$where}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $pages = max(1, (int) ceil($total / $limit));
            if ($page > $pages) $page = $pages;
            $offset = ($page - 1) * $limit;

            // Filas paginadas
            $dataSql = "
                SELECT
                    u.id           AS user_id,
                    p.nombres,
                    p.apellidos,
                    u.email,
                    p.nacionalidad,
                    p.cedula,
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
                {$where}
                GROUP BY u.id
                ORDER BY {$orderColumn} {$sortDir}
                LIMIT {$limit} OFFSET {$offset}
            ";

            $dataStmt = $this->db->prepare($dataSql);
            $dataStmt->execute($params);
            $rows = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

            return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'conteo' => $conteo];
        } catch (\Throwable $e) {
            error_log('[EstudiantesModel::getPaginated] ' . $e->getMessage());
            return $default;
        }
    }

    // =========================================================
    // CREACIÓN
    // =========================================================

    /**
     * Crea un estudiante completo: persona + estudiantes + users (transacción).
     * Opcionalmente crea una inscripción si se provee seccion_id.
     * Carrera hardcoded a id=1.
     *
     * @param array $data { nacionalidad, cedula, nombres, apellidos, email, seccion_id? }
     * @return array { user_id, persona_id, estudiante_id }
     * @throws \RuntimeException
     */
    public function createEstudiante(array $data): array
    {
        $this->db->beginTransaction();

        try {
            // 1. INSERT personas
            $stmt = $this->db->prepare("
                INSERT INTO personas (nacionalidad, cedula, nombres, apellidos)
                VALUES (:nac, :ced, :nom, :ape)
            ");
            $stmt->execute([
                ':nac' => $data['nacionalidad'],
                ':ced' => $data['cedula'],
                ':nom' => $data['nombres'],
                ':ape' => $data['apellidos'],
            ]);
            $personaId = (int) $this->db->lastInsertId();

            // 2. INSERT estudiantes (carrera_id = 1)
            $stmt = $this->db->prepare("
                INSERT INTO estudiantes (persona_id, carrera_id)
                VALUES (:pid, 1)
            ");
            $stmt->execute([':pid' => $personaId]);
            $estudianteId = (int) $this->db->lastInsertId();

            // 3. INSERT users (role_id=3, password=hash(cédula), force_password_change=1)
            $passwordHash = password_hash($data['cedula'], PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO users (persona_id, role_id, email, password, status, force_password_change)
                VALUES (:pid, 3, :email, :pass, 'active', 1)
            ");
            $stmt->execute([
                ':pid'   => $personaId,
                ':email' => $data['email'],
                ':pass'  => $passwordHash,
            ]);
            $userId = (int) $this->db->lastInsertId();

            // 4. INSERT inscripción (opcional)
            $seccionId = (int) ($data['seccion_id'] ?? 0);
            if ($seccionId > 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO inscripciones (estudiante_id, seccion_id)
                    VALUES (:eid, :sid)
                ");
                $stmt->execute([
                    ':eid' => $estudianteId,
                    ':sid' => $seccionId,
                ]);
            }

            $this->db->commit();

            return [
                'user_id'       => $userId,
                'persona_id'    => $personaId,
                'estudiante_id' => $estudianteId,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[EstudiantesModel::createEstudiante] ' . $e->getMessage());
            throw new \RuntimeException('Error al crear el estudiante: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Obtiene las secciones disponibles para el dropdown de asignación.
     * Incluye materia, período y profesor.
     *
     * @return array
     */
    public function getSecciones(): array
    {
        try {
            $sql = "
                SELECT s.id,
                       s.nombre,
                       m.nombre AS materia,
                       p.nombre AS periodo,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS profesor
                FROM secciones s
                INNER JOIN materias m   ON m.id = s.materia_id
                INNER JOIN periodos p   ON p.id = s.periodo_id
                INNER JOIN profesores pr ON pr.id = s.profesor_id
                INNER JOIN personas pe  ON pe.id = pr.persona_id
                ORDER BY p.nombre DESC, s.nombre ASC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[EstudiantesModel::getSecciones] ' . $e->getMessage());
            return [];
        }
    }
}

