<?php
namespace App\Core;

class Csrf
{
    /**
     * Genera un token si no existe y lo guarda en sesión.
     * Retorna el token actual.
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            // Generamos 32 bytes aleatorios y los convertimos a Hex (muy seguro)
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica si el token enviado coincide con el de la sesión.
     */
    public static function verify(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        // hash_equals es resistente a ataques de tiempo (Timing Attacks)
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}