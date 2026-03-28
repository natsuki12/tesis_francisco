<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;
use PDO;

/**
 * Historial de actividad del profesor.
 * Consolida eventos de distintas tablas en una vista paginada server-side.
 */
class HistorialModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene los tipos de eventos disponibles para el filtro.
     */
    public function getTiposEvento(): array
    {
        return [
            'intento_iniciado'   => 'Intento iniciado',
            'intento_calificado' => 'Intento calificado',
            'intento_enviado'    => 'Intento enviado',
            'caso_creado'        => 'Caso creado',
            'asignacion_creada'  => 'Asignación creada',
        ];
    }

    /**
     * Eventos paginados del profesor.
     * Retorna {rows, total, page, pages} compatible con DataTableManager server-side.
     */
    public function getPaginated(
        int $profesorId,
        int $page = 1,
        int $limit = 15,
        string $search = '',
        string $sortCol = 'fecha',
        string $sortDir = 'DESC',
        array $filters = []
    ): array {
        $default = ['rows' => [], 'total' => 0, 'page' => $page, 'pages' => 1];

        $allowedCols = ['fecha', 'tipo', 'estudiante', 'caso', 'detalle'];
        if (!in_array($sortCol, $allowedCols, true)) $sortCol = 'fecha';
        $sortDir = ($sortDir === 'ASC') ? 'ASC' : 'DESC';

        try {
            // ── Sub-queries as UNION ALL ──

            // 1) Intentos calificados (estado Aprobado/Rechazado)
            $q1 = "
                SELECT
                    i.updated_at AS fecha,
                    'intento_calificado' AS tipo,
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                    ce.titulo AS caso,
                    CONCAT(
                        'Intento #', i.numero_intento,
                        CASE
                            WHEN i.nota_numerica IS NOT NULL THEN CONCAT(' — Nota: ', i.nota_numerica, '/20')
                            WHEN i.nota_cualitativa IS NOT NULL THEN CONCAT(' — ', i.nota_cualitativa)
                            ELSE ''
                        END
                    ) AS detalle,
                    i.id AS ref_id
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a   ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce      ON ce.id = cfg.caso_id
                INNER JOIN estudiantes est            ON est.id = a.estudiante_id
                INNER JOIN personas pe                ON pe.id = est.persona_id
                WHERE cfg.profesor_id = :prof1
                  AND i.estado IN ('Aprobado', 'Rechazado')
            ";

            // 2) Intentos enviados (todos los que fueron enviados)
            $q2 = "
                SELECT
                    i.submitted_at AS fecha,
                    'intento_enviado' AS tipo,
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                    ce.titulo AS caso,
                    CONCAT('Intento #', i.numero_intento, ' enviado para revisión') AS detalle,
                    i.id AS ref_id
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a   ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce      ON ce.id = cfg.caso_id
                INNER JOIN estudiantes est            ON est.id = a.estudiante_id
                INNER JOIN personas pe                ON pe.id = est.persona_id
                WHERE cfg.profesor_id = :prof2
                  AND i.submitted_at IS NOT NULL
            ";

            // 3) Casos creados
            $q3 = "
                SELECT
                    ce.created_at AS fecha,
                    'caso_creado' AS tipo,
                    '' AS estudiante,
                    ce.titulo AS caso,
                    CONCAT('Caso \"', ce.titulo, '\" creado como ', ce.estado) AS detalle,
                    ce.id AS ref_id
                FROM sim_casos_estudios ce
                WHERE ce.profesor_id = :prof3
                  AND ce.estado != 'Eliminado'
            ";

            // 4) Asignaciones creadas
            $q4 = "
                SELECT
                    a.created_at AS fecha,
                    'asignacion_creada' AS tipo,
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                    ce.titulo AS caso,
                    CONCAT('Asignación creada para ', pe.nombres, ' ', pe.apellidos) AS detalle,
                    a.id AS ref_id
                FROM sim_caso_asignaciones a
                INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce      ON ce.id = cfg.caso_id
                INNER JOIN estudiantes est            ON est.id = a.estudiante_id
                INNER JOIN personas pe                ON pe.id = est.persona_id
                WHERE cfg.profesor_id = :prof4
            ";

            // 5) Intentos iniciados (estudiante empieza un intento)
            $q5 = "
                SELECT
                    i.created_at AS fecha,
                    'intento_iniciado' AS tipo,
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                    ce.titulo AS caso,
                    CONCAT(
                        pe.nombres, ' ', pe.apellidos,
                        ' inició el intento #', i.numero_intento,
                        ' de ', cfg.max_intentos,
                        ' en ', ce.titulo
                    ) AS detalle,
                    i.id AS ref_id
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a   ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce      ON ce.id = cfg.caso_id
                INNER JOIN estudiantes est            ON est.id = a.estudiante_id
                INNER JOIN personas pe                ON pe.id = est.persona_id
                WHERE cfg.profesor_id = :prof5
            ";

            $unionBase = "({$q1}) UNION ALL ({$q2}) UNION ALL ({$q3}) UNION ALL ({$q4}) UNION ALL ({$q5})";
            $params = [
                ':prof1' => $profesorId,
                ':prof2' => $profesorId,
                ':prof3' => $profesorId,
                ':prof4' => $profesorId,
                ':prof5' => $profesorId,
            ];

            // ── Outer WHERE conditions ──
            $outerConditions = [];

            // Filtro: tipo de evento
            if (!empty($filters['tipo']) && $filters['tipo'] !== 'Todos') {
                $outerConditions[] = "tipo = :filt_tipo";
                $params[':filt_tipo'] = $filters['tipo'];
            }

            // Filtro: rango de fechas
            if (!empty($filters['date_from'])) {
                $outerConditions[] = "DATE(fecha) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $outerConditions[] = "DATE(fecha) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            // Búsqueda libre
            if ($search !== '') {
                $like = '%' . $search . '%';
                $outerConditions[] = "(estudiante LIKE :s1 OR caso LIKE :s2 OR detalle LIKE :s3)";
                $params[':s1'] = $like;
                $params[':s2'] = $like;
                $params[':s3'] = $like;
            }

            $outerWhere = $outerConditions ? 'WHERE ' . implode(' AND ', $outerConditions) : '';

            // ── COUNT ──
            $countSql = "SELECT COUNT(*) FROM ({$unionBase}) AS eventos {$outerWhere}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $pages = max(1, (int) ceil($total / $limit));
            if ($page > $pages) $page = $pages;
            $offset = ($page - 1) * $limit;

            // ── DATA ──
            $dataSql = "SELECT * FROM ({$unionBase}) AS eventos {$outerWhere} ORDER BY {$sortCol} {$sortDir} LIMIT {$limit} OFFSET {$offset}";
            $dataStmt = $this->db->prepare($dataSql);
            $dataStmt->execute($params);
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
        } catch (\Throwable $e) {
            error_log('[HistorialModel::getPaginated] ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Últimos N eventos para el card de "Actividad reciente" del home.
     */
    public function getRecent(int $profesorId, int $limit = 5): array
    {
        try {
            $sql = "
                SELECT * FROM (
                    (SELECT i.updated_at AS fecha, 'intento_calificado' AS tipo,
                        CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                        ce.titulo AS caso,
                        CONCAT('Intento #', i.numero_intento,
                            CASE WHEN i.nota_numerica IS NOT NULL THEN CONCAT(' — Nota: ', i.nota_numerica, '/20')
                                 WHEN i.nota_cualitativa IS NOT NULL THEN CONCAT(' — ', i.nota_cualitativa)
                                 ELSE '' END
                        ) AS detalle, i.id AS ref_id
                    FROM sim_intentos i
                    INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
                    INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
                    INNER JOIN sim_casos_estudios ce ON ce.id = cfg.caso_id
                    INNER JOIN estudiantes est ON est.id = a.estudiante_id
                    INNER JOIN personas pe ON pe.id = est.persona_id
                    WHERE cfg.profesor_id = :p1 AND i.estado IN ('Aprobado','Rechazado'))

                    UNION ALL

                    (SELECT i.submitted_at AS fecha, 'intento_enviado' AS tipo,
                        CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                        ce.titulo AS caso,
                        CONCAT('Intento #', i.numero_intento, ' enviado para revisión') AS detalle,
                        i.id AS ref_id
                    FROM sim_intentos i
                    INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
                    INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
                    INNER JOIN sim_casos_estudios ce ON ce.id = cfg.caso_id
                    INNER JOIN estudiantes est ON est.id = a.estudiante_id
                    INNER JOIN personas pe ON pe.id = est.persona_id
                    WHERE cfg.profesor_id = :p2 AND i.submitted_at IS NOT NULL)

                    UNION ALL

                    (SELECT a.created_at AS fecha, 'asignacion_creada' AS tipo,
                        CONCAT(pe.nombres, ' ', pe.apellidos) AS estudiante,
                        ce.titulo AS caso,
                        CONCAT('Asignación creada para ', pe.nombres, ' ', pe.apellidos) AS detalle,
                        a.id AS ref_id
                    FROM sim_caso_asignaciones a
                    INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
                    INNER JOIN sim_casos_estudios ce ON ce.id = cfg.caso_id
                    INNER JOIN estudiantes est ON est.id = a.estudiante_id
                    INNER JOIN personas pe ON pe.id = est.persona_id
                    WHERE cfg.profesor_id = :p3)
                ) AS eventos
                ORDER BY fecha DESC
                LIMIT :lim
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':p1', $profesorId, PDO::PARAM_INT);
            $stmt->bindValue(':p2', $profesorId, PDO::PARAM_INT);
            $stmt->bindValue(':p3', $profesorId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[HistorialModel::getRecent] ' . $e->getMessage());
            return [];
        }
    }
}
