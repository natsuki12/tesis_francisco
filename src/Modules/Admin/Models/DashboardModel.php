<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo del Dashboard Administrativo.
 *
 * Provee consultas de resumen para las tarjetas estadísticas,
 * el feed de actividad reciente y el estado del sistema.
 *
 * SEGURIDAD:
 *  - PDO prepared statements (anti SQL injection).
 *  - Cada método envuelto en try-catch (anti-crash: si falla la DB
 *    devuelve un valor seguro en vez de romper la página).
 *  - Errores se registran en el log del sistema.
 */
class DashboardModel
{
    // ─── Tarjetas de Resumen (Stats Cards) ─────────────────

    /**
     * Total de usuarios activos en el sistema.
     * Anti-crash: devuelve 0 si la consulta falla.
     */
    public function countUsuarios(): int
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] countUsuarios: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Profesores con cuenta activa.
     * Anti-crash: devuelve 0 si la consulta falla.
     */
    public function countProfesoresActivos(): int
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("
                SELECT COUNT(*)
                FROM profesores pr
                INNER JOIN users u ON u.persona_id = pr.persona_id
                WHERE u.status = 'active'
                  AND u.role_id = 2
            ");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] countProfesoresActivos: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Estudiantes inscritos en al menos una sección del período activo.
     * Anti-crash: devuelve 0 si la consulta falla.
     */
    public function countEstudiantesInscritos(): int
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("
                SELECT COUNT(DISTINCT i.estudiante_id)
                FROM inscripciones i
                INNER JOIN secciones s ON s.id = i.seccion_id
                INNER JOIN periodos  p ON p.id = s.periodo_id
                WHERE p.activo = 1
            ");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] countEstudiantesInscritos: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Secciones abiertas en el período activo.
     * Anti-crash: devuelve 0 si la consulta falla.
     */
    public function countSeccionesAbiertas(): int
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("
                SELECT COUNT(*)
                FROM secciones s
                INNER JOIN periodos p ON p.id = s.periodo_id
                WHERE p.activo = 1
            ");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] countSeccionesAbiertas: ' . $e->getMessage());
            return 0;
        }
    }

    // ─── Feed de Actividad Reciente ────────────────────────

    /**
     * Devuelve los últimos N eventos de la bitácora con datos del usuario.
     * Anti-crash: devuelve array vacío si la consulta falla.
     *
     * @return array<int, array{
     *     tipo_codigo: string,
     *     tipo_descripcion: string,
     *     nivel_riesgo: string,
     *     nombres: string|null,
     *     apellidos: string|null,
     *     role_id: int|null,
     *     attempted_email: string|null,
     *     created_at: string,
     *     created_ts: int
     * }>
     */
    public function getActividadReciente(int $limit = 5): array
    {
        try {
            $db = DB::connect();

            // Sanitizar el límite (nunca más de 50, nunca menos de 1)
            $limit = max(1, min($limit, 50));

            $sql = "
                SELECT
                    te.codigo        AS tipo_codigo,
                    te.descripcion   AS tipo_descripcion,
                    te.nivel_riesgo,
                    p.nombres,
                    p.apellidos,
                    u.role_id,
                    b.attempted_email,
                    b.created_at,
                    UNIX_TIMESTAMP(b.created_at) AS created_ts
                FROM bitacora_eventos b
                INNER JOIN tipos_eventos te ON te.id = b.tipo_evento_id
                LEFT  JOIN users    u ON u.id = b.user_id
                LEFT  JOIN personas p ON p.id = u.persona_id
                ORDER BY b.created_at DESC
                LIMIT :lim
            ";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] getActividadReciente: ' . $e->getMessage());
            return [];
        }
    }

    // ─── Estado del Sistema ────────────────────────────────

    /**
     * Devuelve el período académico activo, o null si no hay ninguno.
     * Anti-crash: devuelve null si la consulta falla.
     *
     * @return array{nombre: string, fecha_inicio: string, fecha_fin: string}|null
     */
    public function getPeriodoActivo(): ?array
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("
                SELECT nombre, fecha_inicio, fecha_fin
                FROM periodos
                WHERE activo = 1
                LIMIT 1
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] getPeriodoActivo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica la conectividad real con la base de datos y devuelve su nombre y versión.
     * Anti-crash: devuelve null si la conexión falla.
     *
     * @return array{db_name: string, version: string}|null
     */
    public function getDbStatus(): ?array
    {
        try {
            $db = DB::connect();
            $dbName  = $db->query("SELECT DATABASE()")->fetchColumn();
            $version = $db->query("SELECT VERSION()")->fetchColumn();
            return [
                'db_name' => $dbName ?: 'desconocida',
                'version' => $version ?: 'desconocida',
            ];
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] getDbStatus: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta los tipos de eventos configurados en la bitácora.
     * Anti-crash: devuelve 0 si la consulta falla.
     */
    public function countTiposEventos(): int
    {
        try {
            $db = DB::connect();
            $stmt = $db->query("SELECT COUNT(*) FROM tipos_eventos");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] countTiposEventos: ' . $e->getMessage());
            return 0;
        }
    }
}
