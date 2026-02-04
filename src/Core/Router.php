<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Clase Router
 * * Se encarga de mapear las URLs entrantes (Request) a funciones o Controladores específicos.
 * Actúa como el "semáforo" de la aplicación.
 */
final class Router
{
    /** @var App Instancia principal de la aplicación para inyección de dependencias */
    private App $app;

    /** * @var array Almacena las rutas registradas separadas por verbo HTTP.
     * Estructura: ['GET' => [...], 'POST' => [...]]
     */
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Constructor
     * Recibe la instancia de App para poder pasarla a los controladores que se instancien.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Registra una ruta para el método GET.
     * * @param string $path La URL (ej: /usuarios)
     * @param callable|array $handler La función o [Controlador, método] a ejecutar
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][] = [$path, $handler];
    }

    /**
     * Registra una ruta para el método POST.
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][] = [$path, $handler];
    }

    /**
     * El Corazón del Router: DISPATCH (Despachador).
     * * Toma el método HTTP y la URL actual, busca una coincidencia y ejecuta la lógica.
     * * @param string $method El verbo HTTP (GET, POST, etc.)
     * @param string $path La URL solicitada (ej: /registro)
     * @return mixed El resultado de la ejecución del controlador/función
     */
    public function dispatch(string $method, string $path): mixed
    {
        $method = strtoupper($method);

        // 1. Normalización de la URL:
        // Elimina barras al inicio y final para evitar errores (ej: "/home/" es igual a "/home")
        // Si la ruta queda vacía, asumimos que es la raíz "/"
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        // 2. Verificación de Método:
        // Si el método (ej: PATCH) no está soportado en nuestro array $routes, error 405.
        if (!isset($this->routes[$method])) {
            http_response_code(405);
            return 'Método no permitido';
        }

        // 3. Búsqueda de Coincidencia (Loop):
        // Recorremos todas las rutas registradas para ese método.
        foreach ($this->routes[$method] as [$routePath, $handler]) {
            
            // Llamamos a match() para ver si la URL actual coincide con el patrón registrado
            $params = $this->match($routePath, $path);
            
            // Si $params es null, no hubo coincidencia, seguimos a la siguiente ruta.
            if ($params === null) {
                continue;
            }

            // --- ¡COINCIDENCIA ENCONTRADA! ---

            // CASO A: El handler es un array [Controlador::class, 'metodo']
            if (is_array($handler)) {
                [$class, $action] = $handler;

                // Verificamos que la clase del controlador exista físicamente
                if (!class_exists($class)) {
                    http_response_code(500);
                    return "Controller no existe: {$class}";
                }

                // Instanciamos el Controlador dinámicamente inyectándole la App
                $controller = new $class($this->app);

                // Verificamos que el método dentro del controlador exista
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    return "Método no existe: {$class}::{$action}";
                }

                // Ejecutamos el método pasando los parámetros dinámicos (ej: el ID)
                return $controller->$action(...array_values($params));
            }

            // CASO B: El handler es una función anónima (Closure)
            return $handler(...array_values($params));
        }

        // 4. Si terminó el ciclo y nadie respondió: 404 Not Found
        http_response_code(404);
        return '404 - Página no encontrada';
    }

    /**
     * Lógica de Expresiones Regulares (Regex) para URLs dinámicas.
     * * Compara una ruta registrada (ej: /user/{id}) con la ruta real (ej: /user/42).
     * * @return array|null Retorna los parámetros capturados si hay coincidencia, o null si falla.
     */
    private function match(string $routePath, string $path): ?array
    {
        // Normalizamos la ruta registrada igual que hicimos con la entrante
        $routePath = '/' . ltrim($routePath, '/');
        $routePath = rtrim($routePath, '/');
        $routePath = $routePath === '' ? '/' : $routePath;

        // Coincidencia exacta (Optimización rápida)
        if ($routePath === $path) {
            return [];
        }

        // ---REGEX ---
        // Convertimos los parámetros tipo {id} en expresiones regulares.
        // {nombre} se convierte en (?P<nombre>[^/]+) que significa:
        // "Captura cualquier cosa que no sea una barra / y llámalo 'nombre'"
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        
        // Agregamos delimitadores de inicio (^) y fin ($) para que la coincidencia sea total
        $pattern = '#^' . $pattern . '$#';

        // Ejecutamos la comparación
        if (!preg_match($pattern, $path, $matches)) {
            return null; // No coincide
        }

        // Limpiamos los resultados (preg_match devuelve índices numéricos que no necesitamos)
        $params = [];
        foreach ($matches as $k => $v) {
            if (!is_string($k)) continue; // Ignoramos índices numéricos (0, 1...)
            $params[$k] = $v;
        }

        return $params;
    }
}