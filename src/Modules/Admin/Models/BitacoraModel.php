<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

class BitacoraModel
{
    /**
     * Obtiene todos los eventos de la bitácora, ordenados por fecha descendente.
     * Incluye JOIN con tipos_eventos para datos legibles.
     *
     * @return array
     */
    public static function getAll(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT 
                        ba.id,
                        COALESCE(ba.attempted_email, 'Sistema') AS email,
                        te.descripcion AS evento,
                        te.nivel_riesgo,
                        ba.modulo,
                        ba.entidad_tipo,
                        ba.entidad_id,
                        ba.detalle,
                        ba.ip_address,
                        ba.created_at
                    FROM bitacora_eventos ba
                    LEFT JOIN tipos_eventos te ON ba.tipo_evento_id = te.id
                    ORDER BY ba.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la lista de emails únicos que tienen registros en la bitácora.
     *
     * @return string[]
     */
    public static function getUniqueEmails(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT DISTINCT COALESCE(ba.attempted_email, 'Sistema') AS email
                    FROM bitacora_eventos ba
                    ORDER BY email ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getUniqueEmails] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los tipos de eventos disponibles.
     *
     * @return array
     */
    public static function getTiposEventos(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT id, descripcion, nivel_riesgo FROM tipos_eventos ORDER BY id ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getTiposEventos] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene eventos paginados con filtros server-side.
     * Retorna formato compatible con DataTableManager: {rows, total, page, pages}
     */
    public static function getPaginated(
        int $page = 1,
        int $limit = 15,
        string $search = '',
        string $sortCol = 'created_at',
        string $sortDir = 'DESC',
        array $filters = []
    ): array {
        $default = ['rows' => [], 'total' => 0, 'page' => $page, 'pages' => 1];

        // Whitelist de columnas para ORDER BY (anti SQL injection)
        $allowedCols = ['created_at', 'email', 'modulo', 'evento', 'detalle', 'ip_address'];
        if (!in_array($sortCol, $allowedCols, true)) {
            $sortCol = 'created_at';
        }
        $sortDir = ($sortDir === 'ASC') ? 'ASC' : 'DESC';

        // Mapear sort de 'email' a la columna real
        $orderCol = $sortCol === 'email'
            ? "COALESCE(ba.attempted_email, 'Sistema')"
            : ($sortCol === 'evento' ? 'te.descripcion' : "ba.{$sortCol}");

        try {
            $db = DB::connect();
            $params = [];
            $conditions = [];

            // Filtro: módulo
            if (!empty($filters['modulo']) && $filters['modulo'] !== 'Todos') {
                $conditions[] = 'ba.modulo = :modulo';
                $params[':modulo'] = $filters['modulo'];
            }

            // Filtro: evento (descripción del tipo)
            if (!empty($filters['evento']) && $filters['evento'] !== 'Todos') {
                $conditions[] = 'te.descripcion = :evento';
                $params[':evento'] = $filters['evento'];
            }

            // Filtro: usuario (email)
            if (!empty($filters['usuario']) && $filters['usuario'] !== 'Todos') {
                $conditions[] = "COALESCE(ba.attempted_email, 'Sistema') = :usuario";
                $params[':usuario'] = $filters['usuario'];
            }

            // Filtro: rango de fechas
            if (!empty($filters['date_from'])) {
                $conditions[] = 'DATE(ba.created_at) >= :date_from';
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $conditions[] = 'DATE(ba.created_at) <= :date_to';
                $params[':date_to'] = $filters['date_to'];
            }

            // Búsqueda libre
            if ($search !== '') {
                $like = '%' . $search . '%';
                $conditions[] = "(ba.attempted_email LIKE :s1 OR te.descripcion LIKE :s2 OR ba.detalle LIKE :s3 OR ba.ip_address LIKE :s4)";
                $params[':s1'] = $like;
                $params[':s2'] = $like;
                $params[':s3'] = $like;
                $params[':s4'] = $like;
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $join = 'LEFT JOIN tipos_eventos te ON ba.tipo_evento_id = te.id';

            // Total de registros (con filtros)
            $countSql = "SELECT COUNT(*) FROM bitacora_eventos ba {$join} {$where}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $pages = max(1, (int) ceil($total / $limit));
            if ($page > $pages) $page = $pages;
            $offset = ($page - 1) * $limit;

            // Filas paginadas
            $dataSql = "SELECT
                            ba.id,
                            COALESCE(ba.attempted_email, 'Sistema') AS email,
                            te.descripcion AS evento,
                            te.nivel_riesgo,
                            ba.modulo,
                            ba.entidad_tipo,
                            ba.entidad_id,
                            ba.detalle,
                            ba.ip_address,
                            ba.created_at
                        FROM bitacora_eventos ba
                        {$join}
                        {$where}
                        ORDER BY {$orderCol} {$sortDir}
                        LIMIT {$limit} OFFSET {$offset}";

            $dataStmt = $db->prepare($dataSql);
            $dataStmt->execute($params);
            $rows = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

            return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getPaginated] ' . $e->getMessage());
            return $default;
        }
    }
}
