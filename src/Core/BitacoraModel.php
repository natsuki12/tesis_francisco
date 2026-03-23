<?php
declare(strict_types=1);

namespace App\Core;

class BitacoraModel
{
    // ── Autenticación (1-9) ──
    public const LOGIN_SUCCESS      = 1;
    public const LOGOUT             = 2;
    public const LOGIN_FAILED       = 3;
    public const USER_BLOCKED       = 4;
    public const PASSWORD_RESET_REQ = 5;
    public const PASSWORD_RESET_OK  = 6;
    public const SUSPICIOUS_IP      = 7;
    public const USER_REGISTERED    = 8;
    public const SESSION_DISPLACED  = 9;

    // ── Usuarios (10-13) ──
    public const PROFESSOR_CREATED   = 10;
    public const PROFESSOR_UPDATED   = 11;
    public const PROFESSOR_DELETED   = 12;
    public const USER_STATUS_CHANGED = 13;

    // ── Casos (14-18) ──
    public const CASE_CREATED        = 14;
    public const CASE_PUBLISHED      = 15;
    public const CASE_DELETED        = 16;
    public const CASE_STATUS_CHANGED = 17;
    public const CASE_DUPLICATED     = 18;

    // ── Configs / Asignaciones (19-23) ──
    public const CONFIG_CREATED      = 19;
    public const CONFIG_UPDATED      = 20;
    public const CONFIG_DELETED      = 21;
    public const ASSIGNMENT_CREATED  = 22;
    public const ASSIGNMENT_REMOVED  = 23;

    // ── Simulador (24-28) ──
    public const ATTEMPT_STARTED      = 24;
    public const ATTEMPT_SUBMITTED    = 25;
    public const ATTEMPT_GRADED       = 26;
    public const ATTEMPT_CANCELLED    = 27;
    public const ATTEMPT_RIF_REVIEWED = 28;

    // ── Sistema (29-33) ──
    public const LOG_EXPORTED          = 29;
    public const SYSTEM_BACKUP         = 30;
    public const BACKUP_CONFIG_CHANGED = 31;
    public const BACKUP_DELETED        = 32;
    public const BACKUP_DOWNLOADED     = 33;

    // ── Módulos (para dropdowns y validación) ──
    public const MODULOS = [
        'autenticacion' => 'Autenticación',
        'usuarios'      => 'Usuarios',
        'casos'         => 'Casos de Estudio',
        'asignaciones'  => 'Asignaciones',
        'simulador'     => 'Simulador',
        'sistema'       => 'Sistema',
    ];

    /**
     * Registra un evento en la bitácora unificada.
     *
     * @param int         $tipoEventoId   ID del tipo de evento (usar constantes de esta clase)
     * @param string      $modulo          Módulo del sistema (autenticacion, usuarios, casos, etc.)
     * @param int|null    $userId          ID del usuario (null → intenta tomar de sesión)
     * @param string|null $attemptedEmail  Email del intento (null → intenta tomar de sesión)
     * @param string|null $entidadTipo     Tabla afectada (users, sim_casos_estudios, etc.)
     * @param int|null    $entidadId       PK del registro afectado
     * @param string|null $detalle         Info adicional (razón de fallo, nota contextual, etc.)
     */
    public static function registrar(
        int     $tipoEventoId,
        string  $modulo         = 'autenticacion',
        ?int    $userId         = null,
        ?string $attemptedEmail = null,
        ?string $entidadTipo    = null,
        ?int    $entidadId      = null,
        ?string $detalle        = null
    ): void {
        try {
            $db = DB::connect();

            // Fallback a datos de sesión si no se proveen
            if ($userId === null && isset($_SESSION['user_id'])) {
                $userId = (int) $_SESSION['user_id'];
            }
            if ($attemptedEmail === null && isset($_SESSION['email'])) {
                $attemptedEmail = $_SESSION['email'];
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $sql = "INSERT INTO bitacora_eventos 
                    (user_id, attempted_email, tipo_evento_id, modulo, entidad_tipo, entidad_id, detalle, ip_address, user_agent) 
                    VALUES (:user_id, :attempted_email, :tipo_evento_id, :modulo, :entidad_tipo, :entidad_id, :detalle, :ip_address, :user_agent)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':user_id'         => $userId,
                ':attempted_email' => $attemptedEmail,
                ':tipo_evento_id'  => $tipoEventoId,
                ':modulo'          => $modulo,
                ':entidad_tipo'    => $entidadTipo,
                ':entidad_id'      => $entidadId,
                ':detalle'         => $detalle,
                ':ip_address'      => $ipAddress,
                ':user_agent'      => $userAgent,
            ]);
        } catch (\Throwable $e) {
            // Un fallo en la bitácora NO debe romper el flujo del sistema
            error_log("[BITACORA ERROR] " . $e->getMessage());
        }
    }
}