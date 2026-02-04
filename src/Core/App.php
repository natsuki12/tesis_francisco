<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    private string $basePath;
    private Router $router;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');

        // 1) Cargar variables del .env
        $this->loadEnv($this->basePath . '/.env');

        // 2) Configurar errores / logs
        $this->setupErrorHandling();

        // 3) 🛡️ SEGURIDAD: Configurar Sesiones Blindadas
        // (Antes de iniciar el router o cualquier lógica)
        $this->configureSessions();

        // 4) 🛡️ SEGURIDAD: Enviar Cabeceras HTTP
        $this->sendSecurityHeaders();

        // 5) Router
        $this->router = new Router($this);
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }

    /**
     * Lee una variable del entorno.
     */
    public function env(string $key, ?string $default = null): ?string
    {
        $val = getenv($key);
        if ($val === false) {
            return $default;
        }
        return $val;
    }

    public function envBool(string $key, bool $default = false): bool
    {
        $val = $this->env($key);
        if ($val === null) return $default;

        $val = strtolower(trim($val));
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Renderiza una vista ubicada en resources/views/{view}.php
     */
    public function view(string $view, array $data = []): string
    {
        $view = trim($view, '/\\');
        $viewFile = $this->basePath("resources/views/{$view}.php");

        if (!is_file($viewFile)) {
            http_response_code(500);
            return "Vista no encontrada: {$viewFile}";
        }

        // Pasar variables al scope de la vista
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        return (string) ob_get_clean();
    }

    /**
     * Arranca la app: obtiene URL y ejecuta el Router.
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Obtenemos la URI
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // =========================================================
        // 🚀 FIX AUTOMÁTICO PARA XAMPP Y RUTAS RELATIVAS
        // =========================================================
        
        // 1. Obtenemos la carpeta donde está fisicamente el index.php
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // 2. Limpieza inteligente
        // CASO A: Si la URL incluye "/public"
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        } 
        // CASO B: Si la URL está limpia por .htaccess
        else {
            $baseDir = dirname($scriptDir); 
            if ($baseDir !== '/' && $baseDir !== '.' && strpos($uri, $baseDir) === 0) {
                $uri = substr($uri, strlen($baseDir));
            }
        }

        // 3. Asegurar que la ruta siempre empiece con /
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        echo $this->router->dispatch($method, $uri);
    }

    /**
     * Manejo de errores + log.
     */
    private function setupErrorHandling(): void
    {
        // Leemos la configuración del .env
        $debug = $this->envBool('APP_DEBUG', true);

        // Ocultar errores en pantalla si no estamos en debug
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        
        // Siempre registrar errores en el archivo log
        ini_set('log_errors', '1');
        $logFile = $this->basePath('storage/logs/app_errors.log');
        
        // Crear carpeta de logs si no existe
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0775, true);
        }
        ini_set('error_log', $logFile);

        // MANEJADOR DE EXCEPCIONES GLOBAL
        set_exception_handler(function (Throwable $e) use ($debug) {
            // 1. Guardar el error real en el archivo log (siempre)
            error_log("[CRITICAL] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            error_log($e->getTraceAsString());

            http_response_code(500);

            // 2. Decidir qué mostrar al usuario
            if ($debug) {
                // MODO DESARROLLO
                echo "<div style='background:#f8d7da; color:#721c24; padding:20px; border:1px solid #f5c6cb; font-family:monospace;'>";
                echo "<h1>🛑 Error del Sistema</h1>";
                echo "<h3>" . htmlspecialchars($e->getMessage()) . "</h3>";
                echo "<p><strong>Archivo:</strong> " . $e->getFile() . " (Línea " . $e->getLine() . ")</p>";
                echo "<pre style='background:#fff; padding:15px; border:1px solid #ccc; overflow:auto;'>" . $e->getTraceAsString() . "</pre>";
                echo "</div>";
            } else {
                // MODO PRODUCCIÓN
                $errorView = $this->basePath('resources/views/errors/500.php');
                if (file_exists($errorView)) {
                    require $errorView;
                } else {
                    echo "<h1>Error 500</h1><p>Ocurrió un error interno.</p>";
                }
            }
        });
    }

    /**
     * 🛡️ SEGURIDAD: Configuración de Sesiones (Hardening)
     * Evita robo de cookies y ataques de fijación de sesión.
     */
    private function configureSessions(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Nombre personalizado para ocultar que usamos PHP (Security through obscurity)
        session_name('SENIAT_SESSION_ID');

        // Configuraciones de seguridad
        ini_set('session.use_strict_mode', '1');   // Evita que el usuario fije su propio ID
        ini_set('session.cookie_httponly', '1');   // 🛡️ JS no puede leer la cookie (Anti-XSS)
        ini_set('session.cookie_samesite', 'Lax'); // 🛡️ Protección extra CSRF

        // Solo activar 'Secure' si detectamos HTTPS
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        if ($isHttps) {
            ini_set('session.cookie_secure', '1');
        }

        // Tiempo de vida (2 horas de inactividad)
        ini_set('session.gc_maxlifetime', '7200');
        ini_set('session.cookie_lifetime', '7200');

        session_start();
    }

    /**
     * 🛡️ SEGURIDAD: Cabeceras HTTP (Security Headers)
     * Protege contra Clickjacking, MIME Sniffing y XSS básico.
     */
    private function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // 1. Evita que tu web sea incrustada en un <iframe> ajeno (Clickjacking)
        header('X-Frame-Options: SAMEORIGIN');

        // 2. Fuerza al navegador a respetar el tipo de archivo (MIME Sniffing)
        header('X-Content-Type-Options: nosniff');

        // 3. Protección básica contra XSS para navegadores antiguos
        header('X-XSS-Protection: 1; mode=block');

        // 4. Controla cuánta info se envía al salir de tu web
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /**
     * Carga simple de .env
     */
    private function loadEnv(string $envFile): void
    {
        if (!is_file($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key === '') continue;

            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) !== false) {
                continue;
            }

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}