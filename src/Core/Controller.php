<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    protected function view(string $view, array $data = []): string
    {
        return $this->app->view($view, $data);
    }

    /**
     * Redirección Inteligente 🧠
     * Detecta si la ruta necesita el prefijo del proyecto (/SISTEMA)
     */
    protected function redirect(string $path, int $status = 302): never
    {
        // 1. 🛡️ SEGURIDAD PRIMERO: Limpieza de CRLF Injection
        // Quitamos saltos de línea maliciosos del path original
        $path = str_replace(["\r", "\n"], '', $path);

        // 2. 🧭 RUTAS RELATIVAS
        // Si no empieza con http/https, usamos el helper global para completar la URL
        if (!preg_match('/^https?:\/\//', $path)) {
         // Aquí combinamos: Pasamos el path limpio a base_url
         // helper.php se encargará de ponerle el prefijo "/SISTEMA"
         $path = base_url($path); 
        }

        header('Location: ' . $path, true, $status);
        exit;
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) return $_POST[$key];
        if (array_key_exists($key, $_GET))  return $_GET[$key];
        return $default;
    }

    protected function inputString(string $key, string $default = '', int $maxLen = 10000): string
    {
        $val = $this->input($key, $default);

        if (is_array($val) || is_object($val)) {
            return $default;
        }

        $val = trim((string)$val);

        if (mb_strlen($val) > $maxLen) {
            $val = mb_substr($val, 0, $maxLen);
        }

        return $val;
    }
}