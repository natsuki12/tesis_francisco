<?php
declare(strict_types=1);

use App\Core\Csrf;

// =============================================================================
// 🛡️ SEGURIDAD Y FORMATO
// =============================================================================

if (!function_exists('e')) {
    /**
     * Escapa texto para prevenir XSS.
     * Acepta string o null (para campos vacíos de la BD).
     */
    function e(string|null $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// =============================================================================
// 🔗 RUTAS Y ASSETS
// =============================================================================

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $appBase = $_ENV['APP_BASE'] ?? getenv('APP_BASE');
        $base = rtrim((string) $appBase, '/');
        return $base . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $appBase = $_ENV['APP_BASE'] ?? getenv('APP_BASE');
        $base = rtrim((string) $appBase, '/');
        $path = ltrim($path, '/');

        if ($base === '') {
            return '/' . $path;
        }

        return $base . ($path !== '' ? '/' . $path : '');
    }
}

// =============================================================================
// 🔐 SEGURIDAD CSRF
// =============================================================================

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        // Asegura que la clase Csrf exista y tenga el método
        if (class_exists(Csrf::class)) {
            $token = Csrf::getToken();
            return '<input type="hidden" name="csrf_token" value="' . $token . '">';
        }
        return '';
    }
}