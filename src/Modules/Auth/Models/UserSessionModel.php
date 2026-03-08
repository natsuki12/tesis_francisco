<?php
declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Core\DB;

/**
 * Modelo para gestión de sesiones activas.
 * Garantiza que cada usuario solo pueda tener UNA sesión activa.
 * Tabla: user_sessions (UNIQUE KEY en user_id → 1 fila por usuario).
 */
class UserSessionModel
{
    /**
     * Inserta o actualiza la sesión activa de un usuario.
     * Si ya existe un registro para ese user_id, lo sobreescribe (desplaza la sesión anterior).
     *
     * @return bool true si había una sesión previa con diferente session_id (fue desplazada)
     */
    public static function upsert(int $userId, string $sessionId, ?string $ip, ?string $userAgent): bool
    {
        try {
            $db = DB::connect();

            // 1. Verificar si existe sesión previa con diferente session_id
            $displaced = false;
            $stmtCheck = $db->prepare("SELECT session_id FROM user_sessions WHERE user_id = ? LIMIT 1");
            $stmtCheck->execute([$userId]);
            $existing = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if ($existing && $existing['session_id'] !== $sessionId) {
                $displaced = true; // Había otra sesión activa → será desplazada
            }

            // 2. INSERT ... ON DUPLICATE KEY UPDATE (la UNIQUE KEY en user_id hace el truco)
            $sql = "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, last_activity, created_at)
                    VALUES (:user_id, :session_id, :ip, :ua, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        session_id    = VALUES(session_id),
                        ip_address    = VALUES(ip_address),
                        user_agent    = VALUES(user_agent),
                        last_activity = NOW()";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':user_id'    => $userId,
                ':session_id' => $sessionId,
                ':ip'         => $ip,
                ':ua'         => $userAgent,
            ]);

            return $displaced;

        } catch (\Throwable $e) {
            error_log("[USER_SESSION ERROR] upsert: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la sesión activa de un usuario.
     */
    public static function getByUserId(int $userId): ?array
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("SELECT session_id, last_activity FROM user_sessions WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\Throwable $e) {
            error_log("[USER_SESSION ERROR] getByUserId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Elimina la sesión activa de un usuario (al hacer logout).
     */
    public static function deleteByUserId(int $userId): void
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
            $stmt->execute([$userId]);

        } catch (\Throwable $e) {
            error_log("[USER_SESSION ERROR] deleteByUserId: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el timestamp de última actividad.
     */
    public static function updateLastActivity(int $userId): void
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE user_id = ?");
            $stmt->execute([$userId]);

        } catch (\Throwable $e) {
            error_log("[USER_SESSION ERROR] updateLastActivity: " . $e->getMessage());
        }
    }
}
