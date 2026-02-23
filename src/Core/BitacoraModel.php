<?php
declare(strict_types=1);

// ARCHIVO: src/Core/bitacora.php

namespace App\Core;

class BitacoraModel
{
    // Constantes de eventos (mapeo a tipos_eventos.id)
    public const LOGIN_SUCCESS      = 1;
    public const LOGOUT             = 2;
    public const LOGIN_FAILED       = 3;
    public const USER_BLOCKED       = 4;
    public const PASSWORD_RESET_REQ = 5;
    public const PASSWORD_RESET_OK  = 6;
    public const SUSPICIOUS_IP      = 7;

    /**
     * Registra un evento en la bitácora de accesos.
     *
     * @param int         $tipoEventoId   ID del tipo de evento (usar constantes de esta clase)
     * @param int|null    $userId          ID del usuario (null si no se conoce)
     * @param string|null $attemptedEmail  Email usado en el intento
     * @param string|null $failReason      Razón del fallo (null si es exitoso)
     */
    public static function log(int $tipoEventoId, ?int $userId = null, ?string $attemptedEmail = null, ?string $failReason = null): void
    {
        try {
            $db = DB::connect();

            // Capturar datos del cliente
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $query = "INSERT INTO bitacora_accesos 
                      (user_id, attempted_email, tipo_evento_id, fail_reason, ip_address, user_agent) 
                      VALUES (:user_id, :attempted_email, :tipo_evento_id, :fail_reason, :ip_address, :user_agent)";

            $stmt = $db->prepare($query);
            $stmt->execute([
                ':user_id'         => $userId,
                ':attempted_email' => $attemptedEmail,
                ':tipo_evento_id'  => $tipoEventoId,
                ':fail_reason'     => $failReason,
                ':ip_address'      => $ipAddress,
                ':user_agent'      => $userAgent,
            ]);
        } catch (\Throwable $e) {
            // Un fallo en la bitácora NO debe romper el flujo de login/logout
            error_log("[BITACORA ERROR] " . $e->getMessage());
        }
    }
}